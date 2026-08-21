<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\MedicineTransfer;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicineTransfersController extends Controller
{
    private function pharmacyOrRedirect(Request $request)
    {
        $pharmacy = optional($request->user())->pharmacy;

        if (! $pharmacy) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma farmácia.');
        }

        if ((string) ($pharmacy->type ?? 'normal') !== 'matrix') {
            return redirect()->route('pharmacy.painel')->with('error', 'Apenas farmácias matriz podem transferir medicamentos.');
        }

        return $pharmacy;
    }

    public function create(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $existingCount = MedicineInventory::query()
            ->where('owner_type', 'pharmacy')
            ->where('owner_id', $pharmacy->id)
            ->count();

        if ($existingCount === 0) {
            DB::transaction(function () use ($pharmacy) {
                Medicine::query()
                    ->where('pharmacy_id', $pharmacy->id)
                    ->orderBy('id')
                    ->chunk(200, function ($meds) use ($pharmacy) {
                        foreach ($meds as $m) {
                            MedicineInventory::query()->firstOrCreate(
                                [
                                    'medicine_id' => $m->id,
                                    'owner_type' => 'pharmacy',
                                    'owner_id' => (int) $pharmacy->id,
                                ],
                                [
                                    'price' => (float) $m->price,
                                    'stock' => (int) $m->stock,
                                    'is_available' => (bool) $m->is_available,
                                ]
                            );
                        }
                    });
            });
        }

        $branchesQuery = PharmacyBranch::query()
            ->where('matrix_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('branch_name');

        if (Schema::hasColumn('pharmacy_branches', 'status')) {
            $branchesQuery->where('status', 'approved');
        }

        $branches = $branchesQuery->get();

        $matrixInventories = MedicineInventory::query()
            ->with(['medicine'])
            ->where('owner_type', 'pharmacy')
            ->where('owner_id', $pharmacy->id)
            ->orderByDesc('id')
            ->get();

        return view('pharmacy.transfers.create', [
            'pharmacy' => $pharmacy,
            'branches' => $branches,
            'matrixInventories' => $matrixInventories,
        ]);
    }

    public function store(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:pharmacy_branches,id'],
            'matrix_inventory_id' => ['required', 'integer', 'exists:medicine_inventories,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $branch = PharmacyBranch::query()->where('id', (int) $data['branch_id'])->firstOrFail();
        if ((int) $branch->matrix_id !== (int) $pharmacy->id) {
            return redirect()->route('pharmacy.transfers.create')->with('error', 'Filial inválida.');
        }

        $qty = (int) $data['quantity'];

        try {
            $result = DB::transaction(function () use ($request, $pharmacy, $branch, $data, $qty) {
                $matrixInv = MedicineInventory::query()
                    ->lockForUpdate()
                    ->where('id', (int) $data['matrix_inventory_id'])
                    ->where('owner_type', 'pharmacy')
                    ->where('owner_id', $pharmacy->id)
                    ->firstOrFail();

                $matrixBefore = (int) $matrixInv->stock;
                if ($matrixBefore < $qty) {
                    throw new \RuntimeException('Stock insuficiente no inventário da matriz.');
                }

                $matrixInv->stock = $matrixBefore - $qty;
                $matrixInv->save();

                $destInv = MedicineInventory::query()
                    ->lockForUpdate()
                    ->where('owner_type', 'pharmacy_branch')
                    ->where('owner_id', $branch->id)
                    ->where('medicine_id', $matrixInv->medicine_id)
                    ->first();

                if (! $destInv) {
                    $destInv = MedicineInventory::query()->create([
                        'medicine_id' => $matrixInv->medicine_id,
                        'owner_type' => 'pharmacy_branch',
                        'owner_id' => $branch->id,
                        'price' => (float) $matrixInv->price,
                        'stock' => 0,
                        'is_available' => (bool) $matrixInv->is_available,
                    ]);
                }

                $destBefore = (int) $destInv->stock;
                $destInv->stock = $destBefore + $qty;
                $destInv->save();

                MedicineTransfer::query()->create([
                    'medicine_id' => $matrixInv->medicine_id,
                    'from_type' => 'pharmacy',
                    'from_id' => $pharmacy->id,
                    'to_type' => 'pharmacy_branch',
                    'to_id' => $branch->id,
                    'quantity' => $qty,
                    'notes' => isset($data['notes']) ? trim((string) $data['notes']) : null,
                    'created_by' => optional($request->user())->id,
                ]);

                $medicine = Medicine::query()
                    ->where('id', $matrixInv->medicine_id)
                    ->where('pharmacy_id', $pharmacy->id)
                    ->first();

                if ($medicine) {
                    $medicine->stock = (int) $matrixInv->stock;
                    $medicine->price = (float) $matrixInv->price;
                    $medicine->is_available = (bool) $matrixInv->is_available;
                    $medicine->save();
                }

                return [
                    'matrix_before' => $matrixBefore,
                    'matrix_after' => (int) $matrixInv->stock,
                    'dest_before' => $destBefore,
                    'dest_after' => (int) $destInv->stock,
                    'medicine_id' => (int) $matrixInv->medicine_id,
                ];
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy.transfers.create')->with('error', $e->getMessage());
        }

        $medicineName = (string) optional(Medicine::query()->find($result['medicine_id']))->name;

        $matrixUserId = (int) ($pharmacy->user_id ?? 0);
        if ($matrixUserId > 0 && (int) $result['matrix_before'] > 5 && (int) $result['matrix_after'] <= 5) {
            NotificationService::notifyUser(
                $matrixUserId,
                'Stock baixo (Matriz)',
                'O medicamento '.($medicineName !== '' ? $medicineName : '—').' ficou com stock baixo ('.$result['matrix_after'].').',
                route('pharmacy.medicines.index', ['low_stock' => 1])
            );
        }

        $branchUserId = (int) ($branch->user_id ?? 0);
        if ($branchUserId > 0 && (int) $result['dest_before'] > 5 && (int) $result['dest_after'] <= 5) {
            NotificationService::notifyUser(
                $branchUserId,
                'Stock baixo (Filial)',
                'O medicamento '.($medicineName !== '' ? $medicineName : '—').' ficou com stock baixo ('.$result['dest_after'].').',
                route('pharmacy.branch_medicines.index', ['low_stock' => 1])
            );
        }

        return redirect()->route('pharmacy.transfers.create')->with('success', 'Transferência realizada com sucesso.');
    }
}
