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
                    <div class="fw-semibold">Filiais</div>
                </div>

                <div class="card-body">
                    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.filiais.index') }}">
                        <div class="col-12 col-md-6">
                            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar por nome, província, email">
                        </div>
                        <div class="col-6 col-md-3">
                            <input class="form-control" name="matrix_id" value="{{ request('matrix_id') }}" placeholder="ID da matriz">
                        </div>
                        <div class="col-6 col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Estado</option>
                                <option value="approved" @selected(request('status')==='approved')>Aprovada</option>
                                <option value="pending" @selected(request('status')==='pending')>Pendente</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Filtrar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.filiais.index') }}">Limpar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Filial</th>
                                    <th>Matriz</th>
                                    <th>Província</th>
                                    <th>Mensalidade</th>
                                    <th>Login</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $b)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $b->branch_name }}</div>
                                            <div class="small text-muted">{{ $b->email ?: '—' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ optional($b->matrix)->business_name ?: '—' }}</div>
                                            <div class="small text-muted">ID: {{ $b->matrix_id }}</div>
                                        </td>
                                        <td>{{ $b->province }}</td>
                                        <td>{{ number_format((float) ($b->monthly_fee ?? 0), 2, ',', '.') }}</td>
                                        <td>
                                            <div class="small text-muted">{{ optional($b->user)->email ?: '—' }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $bStatus = (string) ($b->status ?? 'pending');
                                            @endphp
                                            @if ($bStatus === 'pending')
                                                <span class="badge bg-warning text-dark">PENDENTE</span>
                                            @elseif ($bStatus === 'approved' && (bool) $b->is_active)
                                                <span class="badge bg-success">ACTIVA</span>
                                            @else
                                                <span class="badge bg-secondary">DESACTIVADA</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 justify-content-end flex-wrap">
                                                @if ($bStatus === 'pending')
                                                    <form method="POST" action="{{ route('admin.filiais.approve', $b) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-outline-success" type="submit">Aprovar</button>
                                                    </form>
                                                @endif

                                                @if (!empty($b->document_path))
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.filiais.alvara_document', $b) }}">Alvará</a>
                                                @endif

                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalAdminEditBranch{{ $b->id }}">
                                                    Editar
                                                </button>

                                                <form method="POST" action="{{ route('admin.filiais.destroy', $b) }}" onsubmit="return confirm('Eliminar esta filial?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalAdminEditBranch{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-fullscreen-sm-down modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.filiais.update', $b) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <div class="h5 mb-0">Editar filial</div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-8">
                                                                <label class="form-label">Nome da filial</label>
                                                                <input class="form-control" name="branch_name" value="{{ old('branch_name', $b->branch_name) }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Província</label>
                                                                <input class="form-control" name="province" value="{{ old('province', $b->province) }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Mensalidade da filial</label>
                                                                <input class="form-control" name="monthly_fee" type="number" min="0" step="0.01" value="{{ old('monthly_fee', $b->monthly_fee) }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Horário de funcionamento</label>
                                                                <input class="form-control" name="opening_hours" value="{{ old('opening_hours', $b->opening_hours) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Município</label>
                                                                <input class="form-control" name="city" value="{{ old('city', $b->city) }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bairro</label>
                                                                <input class="form-control" name="neighborhood" value="{{ old('neighborhood', $b->neighborhood) }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Rua</label>
                                                                <input class="form-control" name="street" value="{{ old('street', $b->street) }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Latitude</label>
                                                                <input class="form-control" name="latitude" value="{{ old('latitude', $b->latitude) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Longitude</label>
                                                                <input class="form-control" name="longitude" value="{{ old('longitude', $b->longitude) }}" required>
                                                            </div>

                                                            <div class="col-12"><hr></div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Nome do responsável</label>
                                                                <input class="form-control" name="user_name" value="{{ old('user_name', optional($b->user)->name) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Telefone</label>
                                                                <input class="form-control" name="user_phone" value="{{ old('user_phone', optional($b->user)->phone) }}">
                                                            </div>
                                                            <div class="col-md-7">
                                                                <label class="form-label">Email (login)</label>
                                                                <input class="form-control" name="user_email" value="{{ old('user_email', optional($b->user)->email) }}" required>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="form-label">Nova senha (opcional)</label>
                                                                <input class="form-control" type="password" name="user_password">
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="form-label">Confirmar nova senha</label>
                                                                <input class="form-control" type="password" name="user_password_confirmation">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-grid gap-2 d-md-flex justify-content-md-end">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button class="btn btn-primary" type="submit">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr><td colspan="6" class="text-muted">Sem registos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $branches->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
