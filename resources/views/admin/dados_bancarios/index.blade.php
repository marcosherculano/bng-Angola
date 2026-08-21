@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-2">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Admin</div>
                <div class="card-body p-0">
                    @include('admin.partials.nav')
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Dados bancários do sistema</div>
                </div>

                <div class="card-body">
                    @if (!empty($missingTable) && $missingTable)
                        <div class="alert alert-warning">
                            <div class="fw-semibold">Tabela não encontrada</div>
                            <div class="small">A tabela <code>dados_bancarios</code> ainda não existe. Execute as migrations para activar esta funcionalidade.</div>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-xl-5">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white fw-semibold">Dados actuais</div>
                                <div class="card-body">
                                    @if ($current)
                                        <div class="mb-2"><span class="text-muted small">Banco</span><div class="fw-semibold">{{ $current->banco }}</div></div>
                                        <div class="mb-2"><span class="text-muted small">Titular</span><div class="fw-semibold">{{ $current->titular }}</div></div>
                                        <div class="mb-2"><span class="text-muted small">Nº da conta</span><div class="fw-semibold">{{ $current->numero_conta ?: '—' }}</div></div>
                                        <div class="mb-2"><span class="text-muted small">IBAN</span><div class="fw-semibold">{{ $current->iban ?: '—' }}</div></div>
                                        <div class="mb-2"><span class="text-muted small">Última alteração</span><div class="fw-semibold">{{ optional($current->data_alteracao)->format('Y-m-d H:i') }}</div></div>
                                    @else
                                        <div class="text-muted">Ainda não existem dados bancários cadastrados.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mt-3">
                                <div class="card-header bg-white fw-semibold">Actualizar dados</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.dados_bancarios.store') }}" class="row g-3">
                                        @csrf

                                        <div class="col-12">
                                            <label class="form-label">Nome do banco</label>
                                            <input class="form-control @error('banco') is-invalid @enderror" name="banco" value="{{ old('banco', optional($current)->banco) }}" required>
                                            @error('banco')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Titular</label>
                                            <input class="form-control @error('titular') is-invalid @enderror" name="titular" value="{{ old('titular', optional($current)->titular) }}" required>
                                            @error('titular')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Número da conta (opcional)</label>
                                            <input class="form-control @error('numero_conta') is-invalid @enderror" name="numero_conta" value="{{ old('numero_conta', optional($current)->numero_conta) }}">
                                            @error('numero_conta')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">IBAN</label>
                                            <input class="form-control @error('iban') is-invalid @enderror" name="iban" value="{{ old('iban', optional($current)->iban) }}" required>
                                            @error('iban')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                                <span class="ms-1">Guardar</span>
                                            </button>
                                        </div>
                                    </form>

                                    <div class="form-text mt-2">Cada alteração fica guardada no histórico.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white fw-semibold">Histórico de alterações</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Banco</th>
                                                    <th>Titular</th>
                                                    <th>Nº conta</th>
                                                    <th>IBAN</th>
                                                    <th class="text-end">Acções</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($history as $h)
                                                    <tr>
                                                        <td class="text-muted small">{{ optional($h->data_alteracao)->format('Y-m-d H:i') }}</td>
                                                        <td>{{ $h->banco }}</td>
                                                        <td>{{ $h->titular }}</td>
                                                        <td>{{ $h->numero_conta ?: '—' }}</td>
                                                        <td>{{ $h->iban ?: '—' }}</td>
                                                        <td class="text-end">
                                                            @if ($current && (int) $current->id === (int) $h->id)
                                                                <span class="badge bg-success">Actual</span>
                                                            @else
                                                                <form method="POST" action="{{ route('admin.dados_bancarios.makeCurrent', $h) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button class="btn btn-sm btn-outline-primary" type="submit" data-confirm="Tornar este registo o actual?">
                                                                        <i class="fa-solid fa-rotate"></i>
                                                                        <span class="ms-1">Tornar actual</span>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="6" class="text-muted">Sem registos.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{ $history->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
