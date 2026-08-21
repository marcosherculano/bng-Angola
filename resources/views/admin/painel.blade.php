@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mb-3" style="background: linear-gradient(135deg, rgba(111,66,193,.14), rgba(13,110,253,.10));">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="h4 mb-1">Painel do Administrador</div>
                    <div class="text-muted">Visão geral do sistema</div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark border">{{ optional(auth()->user())->email }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary" href="{{ url('/') }}">Página pública</a>
                    <a class="btn btn-primary" href="{{ route('admin.usuarios.index', ['status' => 'pending']) }}">
                        <i class="fa-solid fa-user-check"></i>
                        <span class="ms-1">Aprovações</span>
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.mensalidades.index', ['status' => 'submitted']) }}">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span class="ms-1">Comprovativos</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.farmacias.index', ['is_active' => 1]) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Farmácias activas</div>
                                <div class="h4 mb-0">{{ $kpis['pharmacies_active'] ?? '—' }}</div>
                                <div class="small text-muted">Em operação</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Farmácias">
                                <i class="fa-solid fa-store"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.farmacias.index') }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Farmácias em trial</div>
                                <div class="h4 mb-0">{{ $kpis['pharmacies_trial'] ?? '—' }}</div>
                                <div class="small text-muted">Teste activo</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Trial">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.usuarios.index', ['status' => 'pending']) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Utilizadores pendentes</div>
                                <div class="h4 mb-0">{{ $kpis['users_pending'] ?? '—' }}</div>
                                <div class="small text-muted">Aguardam aprovação</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Utilizadores">
                                <i class="fa-solid fa-users"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.mensalidades.index', ['status' => 'submitted']) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Comprovativos submetidos</div>
                                <div class="h4 mb-0">{{ $kpis['monthlyfees_submitted'] ?? '—' }}</div>
                                <div class="small text-muted">Para validar</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Mensalidades">
                                <i class="fa-solid fa-file-signature"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.filiais.index', ['is_active' => 0]) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Filiais pendentes</div>
                                <div class="h4 mb-0">{{ $kpis['branches_pending'] ?? '—' }}</div>
                                <div class="small text-muted">Aguardam aprovação</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Filiais">
                                <i class="fa-solid fa-code-branch"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.mensalidades.index', ['status' => 'pending']) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Mensalidades pendentes</div>
                                <div class="h4 mb-0">{{ $kpis['monthlyfees_pending'] ?? '—' }}</div>
                                <div class="small text-muted">A gerar/por pagar</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Pendentes">
                                <i class="fa-solid fa-clock"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a class="text-decoration-none" href="{{ route('admin.usuarios.index', ['status' => 'suspended']) }}">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Utilizadores suspensos</div>
                                <div class="h4 mb-0">{{ $kpis['users_suspended'] ?? '—' }}</div>
                                <div class="small text-muted">Restritos</div>
                            </div>
                            <span class="btn btn-sm btn-outline-primary" role="button" aria-label="Suspensos">
                                <i class="fa-solid fa-user-slash"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Atividade recente</div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.logs.clear') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Limpar todas as atividades recentes?">
                        Limpar atividades
                    </button>
                </form>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.logs.index') }}">Ver logs</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Utilizador</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLogs as $log)
                            <tr>
                                <td class="text-muted small">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($log->user)->email }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->action_type }}</div>
                                    <div class="text-muted small">{{ $log->description }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted">Sem registos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Backup da Base de Dados</div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.backups.index') }}">
                <i class="fa-solid fa-database"></i>
                <span class="ms-1">Gerenciar</span>
            </a>
        </div>
        <div class="card-body">
            <div class="text-muted">Gere, baixe e restaure backups completos da base de dados.</div>
        </div>
    </div>
</div>
@endsection
