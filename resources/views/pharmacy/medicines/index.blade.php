@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Medicamentos</div>
            <div class="text-muted">Gestão de catálogo e stock</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar ao painel</a>
            @if ((string) (optional(auth()->user()->pharmacy)->type ?? 'normal') === 'matrix')
                <a class="btn btn-outline-primary" href="{{ route('pharmacy.transfers.create') }}" data-bs-toggle="tooltip" data-bs-title="Transferir para filiais" aria-label="Transferir para filiais">
                    <i class="fa-solid fa-right-left"></i>
                    <span class="ms-1">Transferências</span>
                </a>
            @endif
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalCreateMedicine">
                <i class="fa-solid fa-plus"></i>
                <span class="ms-1">Novo</span>
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pharmacy.medicines.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Pesquisar</label>
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Nome ou código de barras">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" name="category">
                        <option value="">Todas</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Disponibilidade</label>
                    <select class="form-select" name="availability">
                        <option value="">Todas</option>
                        <option value="available" @selected(request('availability') === 'available')>Disponível</option>
                        <option value="unavailable" @selected(request('availability') === 'unavailable')>Indisponível</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1" @checked((bool) request('low_stock'))>
                        <label class="form-check-label" for="low_stock">Stock baixo</label>
                    </div>

                    <button class="btn btn-outline-primary w-100 mt-2" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        <span class="ms-1">Filtrar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th class="text-end">Preço</th>
                            <th class="text-end">Stock</th>
                            <th class="text-center">Receita</th>
                            <th class="text-center">Disponível</th>
                            <th class="text-end" style="width: 160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $m)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $m->name }}</div>
                                    <div class="text-muted small">{{ $m->barcode ?: '—' }}</div>
                                </td>
                                <td>{{ $m->category ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $m->price, 0, ',', '.') }} Kz</td>
                                <td class="text-end">
                                    @if ($m->stock <= 5)
                                        <span class="badge bg-warning text-dark">{{ $m->stock }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ $m->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($m->requires_prescription)
                                        <span class="badge bg-danger">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($m->is_available)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.medicines.edit', $m) }}" title="Editar" aria-label="Editar" data-bs-toggle="tooltip" data-bs-title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                            <span class="visually-hidden">Editar</span>
                                        </a>

                                        <form method="POST" action="{{ route('pharmacy.medicines.destroy', $m) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Remover" aria-label="Remover" data-bs-toggle="tooltip" data-bs-title="Remover" data-confirm="Remover este medicamento?">
                                                <i class="fa-solid fa-trash"></i>
                                                <span class="visually-hidden">Remover</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted p-3">Sem registos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($medicines->hasPages())
            <div class="card-footer bg-white">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalCreateMedicine" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-sm-down modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('pharmacy.medicines.store') }}">
                @csrf
                <div class="modal-header">
                    <div class="h5 mb-0">Novo medicamento</div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('pharmacy.medicines.partials.form', ['medicine' => null])
                </div>
                <div class="modal-footer d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span class="ms-1">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            function tryOpenDatalist(input) {
                if (!input) return;
                var listId = input.getAttribute('list');
                if (!listId) return;
                var list = document.getElementById(listId);
                if (!list || !list.options || list.options.length === 0) return;
                if (typeof input.value !== 'string') return;

                var original = input.value;
                if (original.length === 0) {
                    input.value = ' ';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.value = '';
                } else {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
                try {
                    input.setSelectionRange(0, input.value.length);
                } catch (e) {}
            }

            document.addEventListener('shown.bs.modal', function (e) {
                if (!e.target || !e.target.matches('#modalCreateMedicine')) return;
                var first = e.target.querySelector('input[name="name"]');
                if (first) {
                    setTimeout(function () { first.focus(); }, 50);
                }
            });

            document.addEventListener('focusin', function (e) {
                var input = e.target;
                if (!input || !input.matches('[data-bng-suggest-input]')) return;
                tryOpenDatalist(input);
            });

            document.addEventListener('click', function (e) {
                var input = e.target;
                if (!input || !input.matches('[data-bng-suggest-input]')) return;
                tryOpenDatalist(input);
            });
        })();
    </script>
@endpush
