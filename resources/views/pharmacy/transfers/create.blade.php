@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="h4 mb-0">Transferir medicamentos</div>
            <div class="text-muted">{{ $pharmacy->business_name }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.medicines.index') }}">Medicamentos</a>
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar ao painel</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('pharmacy.transfers.store') }}" class="row g-3">
                @csrf

                <div class="col-12 col-lg-6">
                    <label class="form-label">Filial</label>
                    <select class="form-select" name="branch_id" required>
                        <option value="">Selecionar filial</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected((string) old('branch_id') === (string) $b->id)>
                                {{ $b->branch_name }} ({{ $b->province }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Medicamento (inventário da matriz)</label>
                    <select class="form-select" name="matrix_inventory_id" required>
                        <option value="">Selecionar medicamento</option>
                        @foreach ($matrixInventories as $inv)
                            @php $m = $inv->medicine; @endphp
                            <option value="{{ $inv->id }}" @selected((string) old('matrix_inventory_id') === (string) $inv->id)>
                                {{ optional($m)->name ?: '—' }} — Stock: {{ (int) $inv->stock }} — Preço: {{ number_format((float) $inv->price, 0, ',', '.') }} Kz
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Regra: só é possível transferir medicamentos existentes no inventário da matriz.</div>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label">Quantidade</label>
                    <input class="form-control" type="number" name="quantity" min="1" step="1" value="{{ old('quantity', 1) }}" required>
                </div>

                <div class="col-12 col-lg-9">
                    <label class="form-label">Observações</label>
                    <input class="form-control" type="text" name="notes" value="{{ old('notes') }}" placeholder="Ex.: reposição semanal">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-right-left"></i>
                        <span class="ms-1">Transferir</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
