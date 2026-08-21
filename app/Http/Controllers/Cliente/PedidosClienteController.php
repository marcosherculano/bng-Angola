<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use App\Models\PharmacyBranchPaymentSetting;
use App\Models\PharmacyPaymentSetting;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class PedidosClienteController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['pharmacy', 'items.medicine'])
            ->where('client_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('cliente.pedidos.index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request, Medicine $medicine)
    {
        $medicine->load('pharmacy');

        $inventory = null;
        if ($request->filled('inventory_id')) {
            $inventory = MedicineInventory::query()
                ->with(['owner'])
                ->where('id', (int) $request->input('inventory_id'))
                ->where('medicine_id', $medicine->id)
                ->first();
        }

        if (! $inventory) {
            $inventory = MedicineInventory::query()
                ->with(['owner'])
                ->where('owner_type', 'pharmacy')
                ->where('owner_id', $medicine->pharmacy_id)
                ->where('medicine_id', $medicine->id)
                ->first();
        }

        if (! $inventory) {
            return redirect()->route('cliente.busca')->with('error', 'Inventário não encontrado para este medicamento.');
        }

        if (! $inventory->is_available) {
            return redirect()->route('cliente.busca')->with('error', 'Este medicamento não está disponível.');
        }

        $branch = null;
        $pharmacy = null;
        if ((string) $inventory->owner_type === 'pharmacy') {
            $pharmacy = Pharmacy::query()->where('id', (int) $inventory->owner_id)->first();
        } elseif ((string) $inventory->owner_type === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->with(['matrix'])->where('id', (int) $inventory->owner_id)->first();
            $pharmacy = optional($branch)->matrix;
        }

        if (! $pharmacy || ! $pharmacy->is_active) {
            return redirect()->route('cliente.busca')->with('error', 'Farmácia indisponível no momento.');
        }

        if ($branch) {
            if (! $branch->is_active) {
                return redirect()->route('cliente.busca')->with('error', 'Filial indisponível no momento.');
            }
            if (Schema::hasColumn('pharmacy_branches', 'status') && (string) ($branch->status ?? 'pending') !== 'approved') {
                return redirect()->route('cliente.busca')->with('error', 'Filial indisponível no momento.');
            }
        }

        return view('cliente.pedidos.create', [
            'medicine' => $medicine,
            'inventory' => $inventory,
            'pharmacy' => $pharmacy,
            'branch' => $branch,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Acesso não autorizado.');
        }

        $order->load(['pharmacy', 'branch', 'items.medicine', 'payment', 'delivery']);

        $paymentSettings = null;
        if ($order->branch) {
            $paymentSettings = PharmacyBranchPaymentSetting::query()->where('pharmacy_branch_id', (int) $order->branch->id)->first();
        }
        if (! $paymentSettings) {
            $paymentSettings = PharmacyPaymentSetting::query()->where('pharmacy_id', (int) $order->pharmacy_id)->first();
        }

        return view('cliente.pedidos.show', [
            'order' => $order,
            'paymentSettings' => $paymentSettings,
        ]);
    }

    public function status(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.',
            ], Response::HTTP_FORBIDDEN);
        }

        $order->loadMissing(['payment', 'delivery']);

        return response()->json([
            'id' => (int) $order->id,
            'status' => (string) $order->status,
            'status_label' => (string) $order->status_label,
            'payment' => $order->payment ? [
                'status' => (string) $order->payment->status,
            ] : null,
            'delivery' => $order->delivery ? [
                'partner' => (string) ($order->delivery->partner ?? ''),
                'external_id' => (string) ($order->delivery->external_id ?? ''),
                'driver_name' => (string) ($order->delivery->driver_name ?? ''),
                'driver_phone' => (string) ($order->delivery->driver_phone ?? ''),
                'estimated_price' => is_null($order->delivery->estimated_price) ? null : (float) $order->delivery->estimated_price,
                'currency' => (string) ($order->delivery->currency ?? 'Kz'),
                'requested_at' => optional($order->delivery->requested_at)->toISOString(),
                'started_at' => optional($order->delivery->started_at)->toISOString(),
                'delivered_at' => optional($order->delivery->delivered_at)->toISOString(),
            ] : null,
        ]);
    }

    public function submitPayment(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Acesso não autorizado.');
        }

        if (! in_array((string) $order->status, ['pending', 'schedule_requested', 'schedule_confirmed'], true)) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'Não é possível enviar pagamento neste estado.');
        }

        $data = $request->validate([
            'method' => ['required', 'in:iban,express,other'],
            'reference' => ['nullable', 'string', 'max:120'],
            'proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:5120'],
        ]);

        $proof = $request->file('proof');
        if (! $proof || ! $proof->isValid()) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'Falha ao carregar o comprovativo. Tente novamente.');
        }

        try {
            $ext = (string) ($proof->guessExtension() ?: $proof->getClientOriginalExtension() ?: 'bin');
            $ext = mb_strtolower(trim($ext));
            $ext = preg_replace('/[^a-z0-9]+/', '', $ext) ?: 'bin';

            $filename = (string) Str::uuid().'.'.$ext;
            $newPath = $proof->storeAs('orders/payment_proofs', $filename, 'local');
        } catch (\Throwable $e) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'Erro ao guardar o comprovativo. Tente novamente.');
        }

        if (empty($newPath) || ! Storage::disk('local')->exists($newPath)) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'Erro ao guardar o comprovativo. Tente novamente.');
        }

        $oldPath = (string) optional($order->payment)->proof_path;

        $payment = OrderPayment::query()->firstOrNew([
            'order_id' => $order->id,
        ]);

        $payment->fill([
            'method' => (string) $data['method'],
            'reference' => isset($data['reference']) && trim((string) $data['reference']) !== '' ? trim((string) $data['reference']) : null,
            'proof_path' => $newPath,
            'status' => 'submitted',
            'confirmed_by' => null,
            'confirmed_at' => null,
            'rejection_reason' => null,
        ]);
        $payment->save();

        if ($oldPath !== '' && $oldPath !== $newPath && str_starts_with($oldPath, 'orders/payment_proofs/')) {
            try {
                if (Storage::disk('local')->exists($oldPath)) {
                    Storage::disk('local')->delete($oldPath);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $order->loadMissing(['pharmacy.user', 'branch.user']);
        $notifyUserId = 0;
        if (! empty($order->pharmacy_branch_id)) {
            $notifyUserId = (int) optional(optional($order->branch)->user)->id;
        }
        if ($notifyUserId <= 0) {
            $notifyUserId = (int) optional(optional($order->pharmacy)->user)->id;
        }
        if ($notifyUserId > 0) {
            NotificationService::notifyUser(
                $notifyUserId,
                'Comprovativo de pagamento enviado',
                'O cliente enviou um comprovativo de pagamento para o pedido #'.$order->id.'.',
                route('pharmacy.orders.show', $order)
            );
        }

        ActivityLogger::log(
            $request,
            'client_payment_proof_submitted',
            'Comprovativo de pagamento enviado (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('cliente.pedidos.show', $order)->with('success', 'Comprovativo enviado. A aguardar confirmação da farmácia.');
    }

    public function invoice(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Acesso não autorizado.');
        }

        if (! in_array((string) $order->status, ['paid', 'ready_for_pickup', 'delivery_requested', 'delivery_in_progress', 'delivered'], true)) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'A fatura só fica disponível após confirmação do pagamento.');
        }

        $order->load(['pharmacy', 'branch', 'items.medicine', 'payment']);

        return response()->view('cliente.pedidos.invoice', [
            'order' => $order,
        ]);
    }

    public function invoiceDownload(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Acesso não autorizado.');
        }

        if (! in_array((string) $order->status, ['paid', 'ready_for_pickup', 'delivery_requested', 'delivery_in_progress', 'delivered'], true)) {
            return redirect()->route('cliente.pedidos.show', $order)->with('error', 'A fatura só fica disponível após confirmação do pagamento.');
        }

        $order->load(['pharmacy', 'branch', 'items.medicine', 'payment']);

        $filename = 'Fatura-Pedido-'.$order->id.'.pdf';

        return Pdf::loadView('cliente.pedidos.invoice_pdf', [
            'order' => $order,
        ])->download($filename);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'inventory_id' => ['required', 'integer', 'exists:medicine_inventories,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'pickup_method' => ['nullable', 'in:pickup,external_transport'],
            'external_transport_name' => ['nullable', 'string', 'max:255'],
            'scheduled_pickup_at' => ['nullable', 'date'],
            'schedule_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $qty = (int) $data['quantity'];

        try {
            $result = DB::transaction(function () use ($request, $data, $qty) {
                $medicine = Medicine::query()->with('pharmacy')->findOrFail((int) $data['medicine_id']);

                $inventory = MedicineInventory::query()
                    ->with(['owner'])
                    ->lockForUpdate()
                    ->where('id', (int) $data['inventory_id'])
                    ->where('medicine_id', $medicine->id)
                    ->firstOrFail();

                if (! $inventory->is_available) {
                    throw new \RuntimeException('Este medicamento não está disponível.');
                }

                $branch = null;
                $pharmacy = null;
                if ((string) $inventory->owner_type === 'pharmacy') {
                    $pharmacy = Pharmacy::query()->where('id', (int) $inventory->owner_id)->first();
                } elseif ((string) $inventory->owner_type === 'pharmacy_branch') {
                    $branch = PharmacyBranch::query()->with(['matrix'])->where('id', (int) $inventory->owner_id)->first();
                    $pharmacy = optional($branch)->matrix;
                }

                if (! $pharmacy || ! $pharmacy->is_active) {
                    throw new \RuntimeException('Farmácia indisponível no momento.');
                }

                if ($branch) {
                    if (! $branch->is_active) {
                        throw new \RuntimeException('Filial indisponível no momento.');
                    }
                    if (Schema::hasColumn('pharmacy_branches', 'status') && (string) ($branch->status ?? 'pending') !== 'approved') {
                        throw new \RuntimeException('Filial indisponível no momento.');
                    }
                }

                if ((int) $inventory->stock < $qty) {
                    throw new \RuntimeException('Stock insuficiente para a quantidade solicitada.');
                }

                $unitPrice = (float) $inventory->price;
                $subtotal = $unitPrice * $qty;

                $pickupMethod = $data['pickup_method'] ?? 'pickup';
                $externalTransportName = null;
                if ($pickupMethod === 'external_transport') {
                    $externalTransportName = trim((string) ($data['external_transport_name'] ?? ''));
                    if ($externalTransportName === '') {
                        throw new \RuntimeException('Indique o nome do transporte externo.');
                    }
                }

                $scheduledPickupAt = null;
                if (! empty($data['scheduled_pickup_at'])) {
                    $scheduledPickupAt = Carbon::parse($data['scheduled_pickup_at']);
                }

                $status = $scheduledPickupAt ? 'schedule_requested' : 'pending';

                $order = Order::query()->create([
                    'client_id' => $request->user()->id,
                    'pharmacy_id' => (int) $pharmacy->id,
                    'medicine_inventory_id' => (int) $inventory->id,
                    'pharmacy_branch_id' => $branch ? (int) $branch->id : null,
                    'status' => $status,
                    'pickup_method' => $pickupMethod,
                    'external_transport_name' => $externalTransportName,
                    'total_price' => $subtotal,
                    'customer_notes' => $data['customer_notes'] ?? null,
                    'scheduled_pickup_at' => $scheduledPickupAt,
                    'schedule_notes' => $data['schedule_notes'] ?? null,
                    'schedule_confirmed_at' => null,
                    'delivered_at' => null,
                ]);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $pharmacyUserId = (int) optional(optional($pharmacy)->user)->id;
                $branchUserId = (int) optional($branch)->user_id;

                return [
                    'order' => $order,
                    'pharmacy_user_id' => $branch ? $branchUserId : $pharmacyUserId,
                    'status' => $status,
                ];
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cliente.busca')->with('error', $e->getMessage());
        }

        $order = $result['order'];
        $notifyUserId = (int) ($result['pharmacy_user_id'] ?? 0);
        if ($notifyUserId > 0) {
            $title = 'Novo pedido recebido';
            $message = 'Recebeu um novo pedido #'.$order->id.' de '.($request->user()->name ?? 'Cliente').'.';

            if (($result['status'] ?? '') === 'schedule_requested') {
                $title = 'Pedido com agendamento solicitado';
                $message = 'O cliente solicitou agendamento no pedido #'.$order->id.'.';
            }

            NotificationService::notifyUser(
                $notifyUserId,
                $title,
                $message,
                route('pharmacy.orders.show', $order)
            );
        }

        return redirect()->route('cliente.pedidos.index')->with('success', 'Pedido criado com sucesso.');
    }

    public function cancel(Request $request, Order $order)
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Acesso não autorizado.');
        }

        if (! in_array((string) $order->status, ['pending', 'schedule_requested'], true)) {
            return redirect()->route('cliente.pedidos.index')->with('error', 'Só é possível cancelar pedidos pendentes ou com agendamento solicitado.');
        }

        $order->loadMissing(['pharmacy.user', 'branch.user']);

        $order->status = 'cancelled';
        $order->save();

        $notifyUserId = 0;
        if (! empty($order->pharmacy_branch_id)) {
            $notifyUserId = (int) optional(optional($order->branch)->user)->id;
        }
        if ($notifyUserId <= 0) {
            $notifyUserId = (int) optional(optional($order->pharmacy)->user)->id;
        }

        if ($notifyUserId > 0) {
            NotificationService::notifyUser(
                $notifyUserId,
                'Pedido cancelado pelo cliente',
                'O cliente cancelou o pedido #'.$order->id.'.',
                route('pharmacy.orders.show', $order)
            );
        }

        return redirect()->route('cliente.pedidos.index')->with('success', 'Pedido cancelado.');
    }
}
