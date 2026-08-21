@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="h4 mb-0">Backup da Base de Dados</div>
            <div class="text-muted">Gerar, baixar e restaurar backups completos</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.painel') }}">Voltar ao painel</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <div class="fw-semibold">Gerenciar Backup da Base de Dados</div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <form method="POST" action="{{ route('admin.backups.generate') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span class="ms-1">Gerar Backup</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.backups.generate_full') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fa-solid fa-database"></i>
                        <span class="ms-1">Gerar Backup Completo (.sql)</span>
                    </button>
                    <div class="form-text">Inclui CREATE DATABASE/USE, views, triggers, routines e events (ideal para migração no Workbench).</div>
                </form>

                <form method="POST" action="{{ route('admin.backups.restore') }}" class="d-inline" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="backup_file" class="form-control @error('backup_file') is-invalid @enderror" accept=".sql,.zip" required>
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fa-solid fa-recycle"></i>
                            <span class="ms-1">Restaurar Backup</span>
                        </button>
                    </div>
                    @error('backup_file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Atenção: o restauro substitui os dados atuais. Permite apenas .sql ou .zip (máx. 50MB).</div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ficheiro</th>
                            <th>Data</th>
                            <th>Tamanho</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $b)
                            <tr>
                                <td class="text-muted">#{{ $b->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $b->filename }}</div>
                                    @if ($b->error_message)
                                        <div class="small text-danger">{{ $b->error_message }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ optional($b->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-muted small">
                                    @if ($b->size_bytes)
                                        {{ number_format($b->size_bytes / 1024 / 1024, 2, ',', '.') }} MB
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $st = (string) $b->status;
                                        $badge = 'secondary';
                                        if ($st === 'ready') $badge = 'success';
                                        elseif ($st === 'failed') $badge = 'danger';
                                        elseif ($st === 'running' || $st === 'pending') $badge = 'warning';
                                        elseif ($st === 'restoring') $badge = 'info';
                                        elseif ($st === 'restored') $badge = 'primary';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $st }}</span>
                                </td>
                                <td class="text-end">
                                    @if ($b->status === 'ready')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.backups.download', $b) }}">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                                            <i class="fa-solid fa-download"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Sem backups ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($backups->hasPages())
            <div class="card-footer bg-white">
                {{ $backups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
