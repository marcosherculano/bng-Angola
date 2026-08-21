@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="h4 mb-0">Medicamentos da Filial</div>
            <div class="text-muted">
                {{ $branch->branch_name }}
                <span class="mx-2 text-muted">•</span>
                Matriz: {{ optional($branch->matrix)->business_name ?: '—' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar</a>
            <a class="btn btn-primary" href="{{ route('pharmacy.branch_medicines.create') }}">
                <i class="fa-solid fa-plus"></i>
                <span class="ms-1">Novo</span>
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pharmacy.branch_medicines.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-lg-5">
                    <label class="form-label">Pesquisar</label>
                    <input class="form-control" type="text" name="q" value="{{ request('q') }}" placeholder="Nome, categoria ou código de barras">
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" name="category">
                        <option value="">Todas</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label">Disponibilidade</label>
                    <select class="form-select" name="availability">
                        <option value="">Todas</option>
                        <option value="available" @selected(request('availability') === 'available')>Disponível</option>
                        <option value="unavailable" @selected(request('availability') === 'unavailable')>Indisponível</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-1">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="low_stock" name="low_stock" value="1" @checked((bool) request('low_stock'))>
                        <label class="form-check-label" for="low_stock">Mostrar só stock baixo (≤ 5)</label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Lista</div>
            <div class="text-muted small">{{ $inventories->total() }} item(ns)</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicamento</th>
                            <th>Categoria</th>
                            <th class="text-end">Preço</th>
                            <th class="text-end">Stock</th>
                            <th class="text-center">Disponível</th>
                            <th class="text-end" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventories as $inv)
                            @php
                                $m = $inv->medicine;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ optional($m)->name ?: '—' }}</td>
                                <td class="text-muted small">{{ optional($m)->category ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $inv->price, 0, ',', '.') }} Kz</td>
                                <td class="text-end">
                                    @if ((int) $inv->stock <= 5)
                                        <span class="badge bg-warning text-dark">{{ (int) $inv->stock }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ (int) $inv->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($inv->is_available)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.branch_medicines.edit', $inv) }}" data-bs-toggle="tooltip" data-bs-title="Editar" aria-label="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pharmacy.branch_medicines.destroy', $inv) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Eliminar este medicamento da filial?" data-bs-toggle="tooltip" data-bs-title="Eliminar" aria-label="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted p-3">Sem medicamentos cadastrados para esta filial.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($inventories->hasPages())
            <div class="card-footer bg-white">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
