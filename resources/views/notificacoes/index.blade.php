@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Notificações</h1>

        <form method="POST" action="{{ route('notificacoes.read_all') }}">
            @csrf
            @method('PUT')
            <button class="btn btn-outline-primary btn-sm" type="submit">Marcar todas como lidas</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Estado</th>
                            <th class="text-end">Acções</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $n)
                            <tr>
                                <td class="fw-semibold">{{ $n->title }}</td>
                                <td>{{ $n->message }}</td>
                                <td class="small text-muted">{{ optional($n->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if ($n->read_at)
                                        <span class="badge bg-success">Lida</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Não lida</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        @if (! $n->read_at)
                                            <form method="POST" action="{{ route('notificacoes.read', $n) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-sm btn-primary" type="submit">Abrir</button>
                                            </form>
                                        @else
                                            @if ($n->resolved_url)
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ $n->resolved_url }}">Ver</a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted p-3">Sem notificações.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
