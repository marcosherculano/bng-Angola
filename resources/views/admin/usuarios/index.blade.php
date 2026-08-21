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
                    <div class="fw-semibold">Utilizadores</div>
                </div>

                <div class="card-body">
                    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.usuarios.index') }}">
                        <div class="col-12 col-md-6">
                            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou email">
                        </div>
                        <div class="col-6 col-md-3">
                            <select class="form-select" name="role">
                                <option value="">Perfil</option>
                                @foreach (['admin','client','pharmacy_normal','pharmacy_matrix','pharmacy_branch'] as $r)
                                    <option value="{{ $r }}" @selected(request('role')===$r)>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Estado</option>
                                @foreach (['pending','approved','suspended','blocked'] as $s)
                                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Filtrar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.usuarios.index') }}">Limpar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Perfil</th>
                                    <th>Estado</th>
                                    <th>Criado</th>
                                    <th class="text-end">Acções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $u)
                                    <tr>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>{{ $u->phone }}</td>
                                        <td><span class="badge bg-secondary">{{ $u->role }}</span></td>
                                        <td><span class="badge bg-light text-dark">{{ $u->status }}</span></td>
                                        <td>{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                                @if ($u->status !== 'approved')
                                                    <form method="POST" action="{{ route('admin.usuarios.approve', $u) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-success" type="submit" title="Aprovar" aria-label="Aprovar" data-bs-toggle="tooltip" data-bs-title="Aprovar">
                                                            <i class="fa-solid fa-check"></i>
                                                            <span class="visually-hidden">Aprovar</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($u->role !== 'admin' && $u->status !== 'suspended')
                                                    <form method="POST" action="{{ route('admin.usuarios.suspend', $u) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-warning" type="submit" title="Suspender" aria-label="Suspender" data-bs-toggle="tooltip" data-bs-title="Suspender" data-confirm="Suspender este utilizador?">
                                                            <i class="fa-solid fa-pause"></i>
                                                            <span class="visually-hidden">Suspender</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($u->role !== 'admin' && $u->status !== 'blocked')
                                                    <form method="POST" action="{{ route('admin.usuarios.block', $u) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-danger" type="submit" title="Bloquear" aria-label="Bloquear" data-bs-toggle="tooltip" data-bs-title="Bloquear" data-confirm="Bloquear este utilizador?">
                                                            <i class="fa-solid fa-ban"></i>
                                                            <span class="visually-hidden">Bloquear</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if (in_array($u->status, ['suspended', 'blocked'], true))
                                                    <form method="POST" action="{{ route('admin.usuarios.unrestrict', $u) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-outline-secondary" type="submit" title="Reativar" aria-label="Reativar" data-bs-toggle="tooltip" data-bs-title="Reativar">
                                                            <i class="fa-solid fa-rotate-left"></i>
                                                            <span class="visually-hidden">Reativar</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($u->role !== 'admin')
                                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Eliminar definitivamente" aria-label="Eliminar definitivamente" data-bs-toggle="tooltip" data-bs-title="Eliminar definitivamente" data-confirm="Eliminar definitivamente este utilizador? Esta acção não pode ser desfeita.">
                                                            <i class="fa-solid fa-trash"></i>
                                                            <span class="visually-hidden">Eliminar definitivamente</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-muted">Sem registos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
