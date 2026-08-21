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
                    <div class="fw-semibold">Logs</div>
                </div>

                <div class="card-body">
                    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.logs.index') }}">
                        <div class="col-12 col-md-8">
                            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar em acção/descrição/modelo">
                        </div>
                        <div class="col-12 col-md-4">
                            <button class="btn btn-primary">Filtrar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.logs.index') }}">Limpar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Utilizador</th>
                                    <th>Acção</th>
                                    <th>Entidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $l)
                                    <tr>
                                        <td>{{ optional($l->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ optional($l->user)->email }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $l->action_type }}</div>
                                            <div class="text-muted small">{{ $l->description }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $l->model_type }}</span>
                                            <span class="badge bg-secondary">#{{ $l->model_id }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">Sem registos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
