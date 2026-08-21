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
                    <div class="fw-semibold">Farmácias</div>
                </div>

                <div class="card-body">
                    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.farmacias.index') }}">
                        <div class="col-12 col-md-6">
                            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou NIF">
                        </div>
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="type">
                                <option value="">Tipo</option>
                                @foreach (['normal','matrix'] as $t)
                                    <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <input class="form-control" name="province" value="{{ request('province') }}" placeholder="Província">
                        </div>
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="is_active">
                                <option value="">Activo?</option>
                                <option value="1" @selected(request('is_active')==='1')>Sim</option>
                                <option value="0" @selected(request('is_active')==='0')>Não</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Filtrar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.farmacias.index') }}">Limpar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>NIF</th>
                                    <th>Província</th>
                                    <th>Tipo</th>
                                    <th>Plano</th>
                                    <th>Mensalidade base</th>
                                    <th>Activa</th>
                                    <th>Utilizador</th>
                                    <th class="text-end">Acções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pharmacies as $p)
                                    <tr>
                                        <td>{{ $p->business_name }}</td>
                                        <td>{{ $p->nif }}</td>
                                        <td>{{ $p->province }}</td>
                                        <td><span class="badge bg-secondary">{{ $p->type }}</span></td>
                                        <td><span class="badge bg-light text-dark">{{ $p->subscription_plan }}</span></td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.farmacias.updateMonthlyFee', $p) }}" class="d-flex gap-2 align-items-center justify-content-start" style="min-width: 220px;">
                                                @csrf
                                                @method('PUT')
                                                <input class="form-control form-control-sm" name="monthly_fee" type="number" min="0" step="0.01" value="{{ old('monthly_fee', $p->monthly_fee) }}" style="max-width: 140px;" required>
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Guardar</button>
                                            </form>
                                        </td>
                                        <td>
                                            @if ($p->is_active)
                                                <span class="badge bg-success">ACTIVA</span>
                                            @else
                                                <span class="badge bg-secondary">INACTIVA</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($p->user)->email }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                                @if (!empty($p->alvara_document_path))
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.farmacias.alvara_document', $p) }}" title="Documento do Alvará" aria-label="Documento do Alvará" data-bs-toggle="tooltip" data-bs-title="Documento do Alvará">
                                                        <i class="fa-solid fa-file-arrow-down"></i>
                                                        <span class="visually-hidden">Documento do Alvará</span>
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" disabled title="Sem documento" aria-label="Sem documento" data-bs-toggle="tooltip" data-bs-title="Sem documento">
                                                        <i class="fa-solid fa-file-circle-xmark"></i>
                                                        <span class="visually-hidden">Sem documento</span>
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalAdminUploadAlvara{{ $p->id }}" title="Enviar/Actualizar Alvará" aria-label="Enviar/Actualizar Alvará" data-bs-toggle="tooltip" data-bs-title="Enviar/Actualizar Alvará">
                                                    <i class="fa-solid fa-upload"></i>
                                                </button>

                                                <form method="POST" action="{{ route('admin.farmacias.toggleActive', $p) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    @if ($p->is_active)
                                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Desactivar" aria-label="Desactivar" data-bs-toggle="tooltip" data-bs-title="Desactivar" data-confirm="Desactivar esta farmácia?">
                                                            <i class="fa-solid fa-power-off"></i>
                                                            <span class="visually-hidden">Desactivar</span>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-outline-success" type="submit" title="Activar" aria-label="Activar" data-bs-toggle="tooltip" data-bs-title="Activar">
                                                            <i class="fa-solid fa-power-off"></i>
                                                            <span class="visually-hidden">Activar</span>
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalAdminUploadAlvara{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.farmacias.updateAlvaraDocument', $p) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <div class="h5 mb-0">Enviar/Actualizar Alvará</div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label">Alvará (PDF/JPG/PNG)</label>
                                                            <input class="form-control" type="file" name="alvara_document" accept="application/pdf,image/png,image/jpeg" required>
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
                                    <tr><td colspan="9" class="text-muted">Sem registos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $pharmacies->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
