<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\PharmacyBranch;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchMedicinesController extends Controller
{
    private function branchOrRedirect(Request $request)
    {
        $user = $request->user();
        if (! $user || (string) ($user->role ?? '') !== 'pharmacy_branch') {
            return redirect()->route('pharmacy.painel')->with('error', 'Acesso não autorizado.');
        }

        $branch = PharmacyBranch::query()
            ->with(['matrix'])
            ->where('user_id', $user->id)
            ->first();

        if (! $branch) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma filial.');
        }

        return $branch;
    }

    public function index(Request $request)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        $query = MedicineInventory::query()
            ->with(['medicine'])
            ->where('owner_type', 'pharmacy_branch')
            ->where('owner_id', $branch->id);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->whereHas('medicine', function ($m) use ($q) {
                $m->where('name', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $cat = trim((string) $request->input('category'));
            $query->whereHas('medicine', function ($m) use ($cat) {
                $m->where('category', $cat);
            });
        }

        if ($request->filled('availability')) {
            $availability = trim((string) $request->input('availability'));
            if ($availability === 'available') {
                $query->where('is_available', true);
            }
            if ($availability === 'unavailable') {
                $query->where('is_available', false);
            }
        }

        if ($request->boolean('low_stock')) {
            $query->where('stock', '<=', 5);
        }

        $inventories = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $categories = MedicineInventory::query()
            ->where('owner_type', 'pharmacy_branch')
            ->where('owner_id', $branch->id)
            ->join('medicines', 'medicines.id', '=', 'medicine_inventories.medicine_id')
            ->whereNotNull('medicines.category')
            ->where('medicines.category', '!=', '')
            ->select('medicines.category')
            ->distinct()
            ->orderBy('medicines.category')
            ->pluck('medicines.category');

        return view('pharmacy.branch_medicines.index', [
            'branch' => $branch,
            'inventories' => $inventories,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        return view('pharmacy.branch_medicines.create', [
            'branch' => $branch,
        ]);
    }

    public function store(Request $request)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        if ($request->filled('stock')) {
            $request->merge([
                'stock' => preg_replace('/\D+/', '', (string) $request->input('stock')),
            ]);
        }

        if ($request->filled('price')) {
            $request->merge([
                'price' => str_replace(',', '.', (string) $request->input('price')),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'requires_prescription' => ['nullable', 'boolean'],

            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $branch) {
            $medicine = Medicine::query()->create([
                'pharmacy_id' => (int) $branch->matrix_id,
                'name' => trim((string) $data['name']),
                'barcode' => isset($data['barcode']) ? trim((string) $data['barcode']) : null,
                'category' => isset($data['category']) ? trim((string) $data['category']) : null,
                'description' => isset($data['description']) ? trim((string) $data['description']) : null,
                'price' => (float) $data['price'],
                'stock' => 0,
                'requires_prescription' => (bool) ($data['requires_prescription'] ?? false),
                'image_path' => null,
                'is_available' => (bool) ($data['is_available'] ?? true),
            ]);

            MedicineInventory::query()->create([
                'medicine_id' => $medicine->id,
                'owner_type' => 'pharmacy_branch',
                'owner_id' => $branch->id,
                'price' => (float) $data['price'],
                'stock' => (int) $data['stock'],
                'is_available' => (bool) ($data['is_available'] ?? true),
            ]);
        });

        return redirect()->route('pharmacy.branch_medicines.index')->with('success', 'Medicamento criado com sucesso.');
    }

    public function edit(Request $request, MedicineInventory $inventory)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        if ((string) ($inventory->owner_type ?? '') !== 'pharmacy_branch' || (int) $inventory->owner_id !== (int) $branch->id) {
            return redirect()->route('pharmacy.branch_medicines.index')->with('error', 'Acesso não autorizado.');
        }

        $inventory->loadMissing('medicine');

        return view('pharmacy.branch_medicines.edit', [
            'branch' => $branch,
            'inventory' => $inventory,
        ]);
    }

    public function update(Request $request, MedicineInventory $inventory)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        if ((string) ($inventory->owner_type ?? '') !== 'pharmacy_branch' || (int) $inventory->owner_id !== (int) $branch->id) {
            return redirect()->route('pharmacy.branch_medicines.index')->with('error', 'Acesso não autorizado.');
        }

        if ($request->filled('stock')) {
            $request->merge([
                'stock' => preg_replace('/\D+/', '', (string) $request->input('stock')),
            ]);
        }

        if ($request->filled('price')) {
            $request->merge([
                'price' => str_replace(',', '.', (string) $request->input('price')),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'requires_prescription' => ['nullable', 'boolean'],

            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $beforeStock = (int) $inventory->stock;

        DB::transaction(function () use ($data, $inventory) {
            $inventory->loadMissing('medicine');

            $medicine = $inventory->medicine;
            if ($medicine) {
                $medicine->name = trim((string) $data['name']);
                $medicine->barcode = isset($data['barcode']) ? trim((string) $data['barcode']) : null;
                $medicine->category = isset($data['category']) ? trim((string) $data['category']) : null;
                $medicine->description = isset($data['description']) ? trim((string) $data['description']) : null;
                $medicine->requires_prescription = (bool) ($data['requires_prescription'] ?? false);
                $medicine->is_available = (bool) ($data['is_available'] ?? true);
                $medicine->save();
            }

            $inventory->price = (float) $data['price'];
            $inventory->stock = (int) $data['stock'];
            $inventory->is_available = (bool) ($data['is_available'] ?? true);
            $inventory->save();
        });

        $afterStock = (int) $inventory->fresh()->stock;
        if ($beforeStock > 5 && $afterStock <= 5) {
            $medicineName = (string) optional($inventory->medicine)->name;
            NotificationService::notifyUser(
                (int) ($branch->user_id ?? 0),
                'Stock baixo (Filial)',
                'O medicamento '.($medicineName !== '' ? $medicineName : '—').' ficou com stock baixo ('.$afterStock.').',
                route('pharmacy.branch_medicines.index', ['low_stock' => 1])
            );
        }

        return redirect()->route('pharmacy.branch_medicines.index')->with('success', 'Medicamento actualizado com sucesso.');
    }

    public function destroy(Request $request, MedicineInventory $inventory)
    {
        $branch = $this->branchOrRedirect($request);
        if ($branch instanceof \Illuminate\Http\RedirectResponse) {
            return $branch;
        }

        if ((string) ($inventory->owner_type ?? '') !== 'pharmacy_branch' || (int) $inventory->owner_id !== (int) $branch->id) {
            return redirect()->route('pharmacy.branch_medicines.index')->with('error', 'Acesso não autorizado.');
        }

        $inventory->delete();

        return redirect()->route('pharmacy.branch_medicines.index')->with('success', 'Medicamento eliminado com sucesso.');
    }
}
