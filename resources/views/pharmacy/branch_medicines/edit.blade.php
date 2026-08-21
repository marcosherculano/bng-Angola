@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="h4 mb-0">Editar medicamento (Filial)</div>
            <div class="text-muted">
                {{ $branch->branch_name }}
                <span class="mx-2 text-muted">•</span>
                Matriz: {{ optional($branch->matrix)->business_name ?: '—' }}
            </div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('pharmacy.branch_medicines.update', $inventory) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-12 col-lg-6">
                    <label class="form-label">Nome</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name', optional($inventory->medicine)->name) }}" required>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Código de barras</label>
                    <input class="form-control" type="text" name="barcode" value="{{ old('barcode', optional($inventory->medicine)->barcode) }}">
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Categoria</label>
                    <input class="form-control" type="text" name="category" value="{{ old('category', optional($inventory->medicine)->category) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description', optional($inventory->medicine)->description) }}</textarea>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Preço (Kz)</label>
                    <input class="form-control" type="number" name="price" value="{{ old('price', (float) $inventory->price) }}" step="0.01" min="0" required>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Stock</label>
                    <input class="form-control" type="number" name="stock" value="{{ old('stock', (int) $inventory->stock) }}" step="1" min="0" required>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Disponível</label>
                    <select class="form-select" name="is_available">
                        <option value="1" @selected(old('is_available', $inventory->is_available ? '1' : '0') === '1')>Sim</option>
                        <option value="0" @selected(old('is_available', $inventory->is_available ? '1' : '0') === '0')>Não</option>
                    </select>
                </div>

                <div class="col-12 col-lg-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requires_prescription" name="requires_prescription" value="1" @checked((bool) old('requires_prescription', (bool) optional($inventory->medicine)->requires_prescription))>
                        <label class="form-check-label" for="requires_prescription">Requer receita</label>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span class="ms-1">Guardar alterações</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
