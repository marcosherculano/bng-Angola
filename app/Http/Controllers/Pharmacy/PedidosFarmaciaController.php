<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PharmacyBranch;
use App\Services\Delivery\DeliveryPartnerFactory;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PedidosFarmaciaController extends Controller
{
    private function tryNotifyClient(Order $order, string $event): void
    {
        try {
            $order->loadMissing(['client', 'pharmacy']);

            $email = optional($order->client)->email;
            if (empty($email)) {
                return;
            }
            Mail::to($email)->send(new OrderStatusChanged($order, $event));
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar email de notificação do pedido.', [
                'order_id' => $order->id ?? null,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function requestDelivery(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((string) $order->pickup_method !== 'external_transport') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não é de transporte externo.');
        }

        if ((string) $order->status !== 'paid') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Só é possível solicitar entrega após confirmação do pagamento.');
        }

        $data = $request->validate([
            'partner' => ['nullable', 'string', 'max:60'],
            'external_id' => ['nullable', 'string', 'max:120'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'driver_phone' => ['nullable', 'string', 'max:60'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $driver = DeliveryPartnerFactory::make($data['partner'] ?? null);
        $driver->requestDelivery($order, $data);

        $order->status = 'delivery_requested';
        $order->save();

        $this->tryNotifyClient($order, 'delivery_requested');

        ActivityLogger::log(
            $request,
            'pharmacy_delivery_requested',
            'Entrega externa solicitada (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Entrega solicitada.');
    }

    public function startDelivery(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((string) $order->pickup_method !== 'external_transport') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não é de transporte externo.');
        }

        if ((string) $order->status !== 'delivery_requested') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não está com entrega solicitada.');
        }

        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:120'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'driver_phone' => ['nullable', 'string', 'max:60'],
        ]);

        $order->loadMissing(['delivery']);
        $driver = DeliveryPartnerFactory::make(optional($order->delivery)->partner);
        $driver->startDelivery($order, $data);

        $order->status = 'delivery_in_progress';
        $order->save();

        $this->tryNotifyClient($order, 'delivery_in_progress');

        ActivityLogger::log(
            $request,
            'pharmacy_delivery_started',
            'Entrega externa iniciada (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Entrega iniciada.');
    }

    public function updateDeliveryDetails(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((string) $order->pickup_method !== 'external_transport') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não é de transporte externo.');
        }

        if ((string) $order->status === 'delivered') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido já foi entregue. Não é possível alterar os dados da entrega.');
        }

        $data = $request->validate([
            'partner' => ['nullable', 'string', 'max:60'],
            'external_id' => ['nullable', 'string', 'max:120'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'driver_phone' => ['nullable', 'string', 'max:60'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->loadMissing(['delivery']);
        $driver = DeliveryPartnerFactory::make($data['partner'] ?? optional($order->delivery)->partner);
        $driver->updateDetails($order, $data);

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Dados da entrega atualizados.');
    }

    public function cancelDelivery(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((string) $order->pickup_method !== 'external_transport') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não é de transporte externo.');
        }

        $status = (string) $order->status;
        if ($status === 'delivered') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido já foi entregue.');
        }

        if (! in_array($status, ['delivery_requested', 'delivery_in_progress'], true)) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Não é possível cancelar a entrega neste estado.');
        }

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->loadMissing(['delivery']);
        $driver = DeliveryPartnerFactory::make(optional($order->delivery)->partner);
        $driver->cancelDelivery($order, [
            'cancel_reason' => trim((string) ($data['cancel_reason'] ?? '')) !== '' ? trim((string) $data['cancel_reason']) : null,
        ]);

        $order->status = 'paid';
        $order->save();

        $this->tryNotifyClient($order, 'delivery_cancelled');

        ActivityLogger::log(
            $request,
            'pharmacy_delivery_cancelled',
            'Entrega externa cancelada (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Entrega externa cancelada.');
    }

    private function pharmacyOrRedirect(Request $request)
    {
        $user = $request->user();
        $pharmacy = optional($user)->pharmacy;

        if (! $pharmacy && $user && (string) ($user->role ?? '') === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
            $pharmacy = optional($branch)->matrix;
        }

        if (! $pharmacy) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma farmácia.');
        }

        return $pharmacy;
    }

    private function branchOrNull(Request $request): ?PharmacyBranch
    {
        $user = $request->user();
        if (! $user || (string) ($user->role ?? '') !== 'pharmacy_branch') {
            return null;
        }

        return PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
    }

    public function index(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $branch = $this->branchOrNull($request);

        $query = Order::query()
            ->with(['client', 'items.medicine'])
            ->where('pharmacy_id', $pharmacy->id);

        if ($branch) {
            $query->where('pharmacy_branch_id', $branch->id);
        }

        if ($request->filled('status')) {
            $query->where('status', trim((string) $request->input('status')));
        }

        $orders = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('pharmacy.orders.index', [
            'pharmacy' => $pharmacy,
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $order->load(['client', 'items.medicine', 'payment', 'delivery']);

        return view('pharmacy.orders.show', [
            'pharmacy' => $pharmacy,
            'order' => $order,
        ]);
    }

    public function paymentProof(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $order->loadMissing(['payment']);

        $path = (string) optional($order->payment)->proof_path;
        if ($path === '') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Sem comprovativo de pagamento neste pedido.');
        }

        $disk = 'local';
        if (! Storage::disk($disk)->exists($path)) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Comprovativo não encontrado no servidor.');
        }

        return Storage::disk($disk)->download($path);
    }

    public function confirmPayment(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (! in_array((string) $order->status, ['pending', 'schedule_requested', 'schedule_confirmed'], true)) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Não é possível confirmar pagamento neste estado do pedido.');
        }

        $order->loadMissing(['payment', 'client']);
        if (! $order->payment || (string) $order->payment->status !== 'submitted') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Não há comprovativo submetido para confirmar.');
        }

        try {
            DB::transaction(function () use ($request, $order) {
                $payment = OrderPayment::query()->where('order_id', $order->id)->lockForUpdate()->first();
                if (! $payment || (string) $payment->status !== 'submitted') {
                    throw new \RuntimeException('Não há comprovativo submetido para confirmar.');
                }

                $payment->status = 'confirmed';
                $payment->confirmed_by = (int) optional($request->user())->id;
                $payment->confirmed_at = Carbon::now();
                $payment->rejection_reason = null;
                $payment->save();

                if (in_array((string) $order->status, ['pending', 'schedule_requested', 'schedule_confirmed'], true)) {
                    $order->status = 'paid';
                    $order->save();
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', $e->getMessage());
        }

        $clientId = (int) optional($order->client)->id;
        if ($clientId > 0) {
            NotificationService::notifyUser(
                $clientId,
                'Pagamento confirmado',
                'A farmácia confirmou o pagamento do pedido #'.$order->id.'.',
                route('cliente.pedidos.show', $order)
            );
        }

        ActivityLogger::log(
            $request,
            'pharmacy_payment_confirmed',
            'Pagamento confirmado (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Pagamento confirmado.');
    }

    public function rejectPayment(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->loadMissing(['payment', 'client']);
        if (! $order->payment || ! in_array((string) $order->payment->status, ['submitted', 'confirmed'], true)) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Não há comprovativo submetido para recusar.');
        }

        if (! in_array((string) $order->status, ['pending', 'schedule_requested', 'schedule_confirmed', 'paid'], true)) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Não é possível recusar pagamento neste estado do pedido.');
        }

        try {
            DB::transaction(function () use ($request, $order, $data) {
                $payment = OrderPayment::query()->where('order_id', $order->id)->lockForUpdate()->first();
                if (! $payment || ! in_array((string) $payment->status, ['submitted', 'confirmed'], true)) {
                    throw new \RuntimeException('Não há comprovativo submetido para recusar.');
                }

                $payment->status = 'rejected';
                $payment->confirmed_by = (int) optional($request->user())->id;
                $payment->confirmed_at = Carbon::now();
                $payment->rejection_reason = isset($data['rejection_reason']) && trim((string) $data['rejection_reason']) !== ''
                    ? trim((string) $data['rejection_reason'])
                    : null;
                $payment->save();

                if ((string) $order->status === 'paid') {
                    $order->status = 'pending';
                    $order->save();
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', $e->getMessage());
        }

        $clientId = (int) optional($order->client)->id;
        if ($clientId > 0) {
            NotificationService::notifyUser(
                $clientId,
                'Pagamento recusado',
                'A farmácia recusou o pagamento do pedido #'.$order->id.'.',
                route('cliente.pedidos.show', $order)
            );
        }

        ActivityLogger::log(
            $request,
            'pharmacy_payment_rejected',
            'Pagamento recusado (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Pagamento recusado.');
    }

    public function confirmSchedule(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $data = $request->validate([
            'schedule_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->status !== 'schedule_requested') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido não está com agendamento solicitado.');
        }

        $order->status = 'schedule_confirmed';
        $order->schedule_confirmed_at = Carbon::now();
        if (! empty($data['schedule_notes'])) {
            $order->schedule_notes = $data['schedule_notes'];
        }
        $order->save();

        $this->tryNotifyClient($order, 'schedule_confirmed');

        $order->loadMissing(['client']);
        $clientId = (int) optional($order->client)->id;
        if ($clientId > 0) {
            NotificationService::notifyUser(
                $clientId,
                'Agendamento confirmado',
                'A farmácia confirmou o agendamento do pedido #'.$order->id.'.',
                route('cliente.pedidos.index')
            );
        }

        return redirect()->route('pharmacy.orders.show', $order)->with('success', 'Agendamento confirmado.');
    }

    private function assertOrderBelongsToPharmacy(Request $request, Order $order)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $branch = $this->branchOrNull($request);

        if ((int) $order->pharmacy_id !== (int) $pharmacy->id) {
            return redirect()->route('pharmacy.orders.index')->with('error', 'Acesso não autorizado.');
        }

        if ($branch && (int) $order->pharmacy_branch_id !== (int) $branch->id) {
            return redirect()->route('pharmacy.orders.index')->with('error', 'Acesso não autorizado.');
        }

        return $pharmacy;
    }

    public function markReady(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $current = (string) $order->status;
        if ($current !== 'paid') {
            return redirect()->route('pharmacy.orders.index')->with('error', 'Só é possível marcar como pronto após confirmação do pagamento.');
        }

        if ((string) $order->pickup_method === 'external_transport') {
            return redirect()->route('pharmacy.orders.show', $order)->with('error', 'Este pedido é de transporte externo. Use as ações de entrega.');
        }

        $order->status = 'ready_for_pickup';
        $order->save();

        $this->tryNotifyClient($order, 'ready_for_pickup');

        $order->loadMissing(['client']);
        $clientId = (int) optional($order->client)->id;
        if ($clientId > 0) {
            NotificationService::notifyUser(
                $clientId,
                'Pedido pronto para levantamento',
                'O pedido #'.$order->id.' está pronto para levantamento.',
                route('cliente.pedidos.index')
            );
        }

        return redirect()->route('pharmacy.orders.index')->with('success', 'Pedido marcado como pronto para levantamento.');
    }

    public function markDelivered(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $status = (string) $order->status;
        if ((string) $order->pickup_method === 'external_transport') {
            if ($status !== 'delivery_in_progress') {
                return redirect()->route('pharmacy.orders.index')->with('error', 'Só é possível entregar quando a entrega estiver em curso.');
            }
        } else {
            if ($status !== 'ready_for_pickup') {
                return redirect()->route('pharmacy.orders.index')->with('error', 'Só é possível entregar quando estiver pronto para levantamento.');
            }
        }

        try {
            DB::transaction(function () use ($order) {
                $order->load(['items', 'medicineInventory.owner']);

                $inventory = null;
                if (! empty($order->medicine_inventory_id)) {
                    $inventory = MedicineInventory::query()
                        ->with(['owner'])
                        ->lockForUpdate()
                        ->where('id', (int) $order->medicine_inventory_id)
                        ->first();
                }

                if ($inventory) {
                    $totalQty = 0;
                    foreach ($order->items as $it) {
                        $totalQty += (int) $it->quantity;
                    }

                    if ((int) $inventory->stock < (int) $totalQty) {
                        throw new \RuntimeException('Stock insuficiente para concluir a entrega.');
                    }

                    $inventory->stock = (int) $inventory->stock - (int) $totalQty;
                    $inventory->save();

                    if ((string) ($inventory->owner_type ?? '') === 'pharmacy') {
                        $medicine = Medicine::query()
                            ->where('id', (int) $inventory->medicine_id)
                            ->lockForUpdate()
                            ->first();

                        if ($medicine) {
                            $medicine->stock = (int) $inventory->stock;
                            $medicine->save();
                        }
                    }
                } else {
                    foreach ($order->items as $it) {
                        $medicine = Medicine::query()
                            ->where('id', $it->medicine_id)
                            ->lockForUpdate()
                            ->first();

                        if (! $medicine) {
                            throw new \RuntimeException('Medicamento não encontrado.');
                        }

                        if ($medicine->stock < (int) $it->quantity) {
                            throw new \RuntimeException('Stock insuficiente para concluir a entrega.');
                        }
                    }

                    foreach ($order->items as $it) {
                        Medicine::query()
                            ->where('id', $it->medicine_id)
                            ->decrement('stock', (int) $it->quantity);
                    }
                }

                $order->status = 'delivered';
                $order->delivered_at = Carbon::now();
                $order->save();

                if ((string) $order->pickup_method === 'external_transport') {
                    $driver = DeliveryPartnerFactory::make(optional($order->delivery)->partner);
                    $driver->updateDetails($order, [
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now(),
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy.orders.index')->with('error', $e->getMessage());
        }

        $this->tryNotifyClient($order, 'delivered');

        ActivityLogger::log(
            $request,
            'pharmacy_order_delivered',
            'Pedido marcado como entregue (Pedido #'.$order->id.')',
            Order::class,
            (int) $order->id
        );

        return redirect()->route('pharmacy.orders.index')->with('success', 'Pedido marcado como entregue.');
    }

    public function cancel(Request $request, Order $order)
    {
        $pharmacy = $this->assertOrderBelongsToPharmacy($request, $order);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (! in_array((string) $order->status, ['pending', 'schedule_requested', 'schedule_confirmed', 'paid', 'ready_for_pickup', 'delivery_requested'], true)) {
            return redirect()->route('pharmacy.orders.index')->with('error', 'Não é possível cancelar neste estado.');
        }

        $order->status = 'cancelled';
        $order->save();

        $this->tryNotifyClient($order, 'cancelled');

        return redirect()->route('pharmacy.orders.index')->with('success', 'Pedido cancelado.');
    }
}
