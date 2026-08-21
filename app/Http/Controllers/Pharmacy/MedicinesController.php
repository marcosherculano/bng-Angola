<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MedicinesController extends Controller
{
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

    public function index(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('viewAny', Medicine::class)) {
            return redirect()->route('pharmacy.painel')->with('error', 'Acesso não autorizado.');
        }

        $query = Medicine::query()->where('pharmacy_id', $pharmacy->id);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', trim((string) $request->input('category')));
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

        $medicines = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $categories = Medicine::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $suggestNames = Medicine::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(200)
            ->pluck('name');

        $suggestBarcodes = Medicine::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->select('barcode')
            ->distinct()
            ->orderBy('barcode')
            ->limit(200)
            ->pluck('barcode');

        return view('pharmacy.medicines.index', [
            'pharmacy' => $pharmacy,
            'medicines' => $medicines,
            'categories' => $categories,
            'suggestNames' => $suggestNames,
            'suggestBarcodes' => $suggestBarcodes,
        ]);
    }

    public function create(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('create', Medicine::class)) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Acesso não autorizado.');
        }

        return view('pharmacy.medicines.create', [
            'pharmacy' => $pharmacy,
        ]);
    }

    public function store(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('create', Medicine::class)) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Acesso não autorizado.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $data['pharmacy_id'] = $pharmacy->id;
        $data['requires_prescription'] = (bool) ($data['requires_prescription'] ?? false);
        $data['is_available'] = (bool) ($data['is_available'] ?? false);

        DB::transaction(function () use ($data, $pharmacy) {
            $medicine = Medicine::query()->create($data);

            MedicineInventory::query()->firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'owner_type' => 'pharmacy',
                    'owner_id' => (int) $pharmacy->id,
                ],
                [
                    'price' => (float) $medicine->price,
                    'stock' => (int) $medicine->stock,
                    'is_available' => (bool) $medicine->is_available,
                ]
            );
        });

        return redirect()->route('pharmacy.medicines.index')->with('success', 'Medicamento criado com sucesso.');
    }

    public function edit(Request $request, Medicine $medicine)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('update', $medicine)) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Não tem permissão para editar este medicamento.');
        }

        return view('pharmacy.medicines.edit', [
            'pharmacy' => $pharmacy,
            'medicine' => $medicine,
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('update', $medicine)) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Não tem permissão para actualizar este medicamento.');
        }

        $beforeStock = (int) $medicine->stock;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $data['requires_prescription'] = (bool) ($data['requires_prescription'] ?? false);
        $data['is_available'] = (bool) ($data['is_available'] ?? false);

        DB::transaction(function () use ($medicine, $data, $pharmacy) {
            $medicine->update($data);

            MedicineInventory::query()->updateOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'owner_type' => 'pharmacy',
                    'owner_id' => (int) $pharmacy->id,
                ],
                [
                    'price' => (float) $medicine->price,
                    'stock' => (int) $medicine->stock,
                    'is_available' => (bool) $medicine->is_available,
                ]
            );
        });

        $afterStock = (int) $medicine->fresh()->stock;
        if ($beforeStock > 5 && $afterStock <= 5) {
            NotificationService::notifyUser(
                (int) ($pharmacy->user_id ?? 0),
                'Stock baixo (Matriz)',
                'O medicamento '.($medicine->name ?? '—').' ficou com stock baixo ('.$afterStock.').',
                route('pharmacy.medicines.index', ['low_stock' => 1])
            );
        }

        return redirect()->route('pharmacy.medicines.index')->with('success', 'Medicamento actualizado com sucesso.');
    }

    public function destroy(Request $request, Medicine $medicine)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if (Gate::denies('delete', $medicine)) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Não tem permissão para remover este medicamento.');
        }

        if ($medicine->orderItems()->exists()) {
            return redirect()->route('pharmacy.medicines.index')->with('error', 'Não é possível remover: este medicamento já tem pedidos associados.');
        }

        $medicine->delete();

        return redirect()->route('pharmacy.medicines.index')->with('success', 'Medicamento removido.');
    }
}
