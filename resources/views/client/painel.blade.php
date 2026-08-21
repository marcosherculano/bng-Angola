@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mb-3" style="background: linear-gradient(135deg, rgba(13,110,253,.12), rgba(25,135,84,.10));">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="h4 mb-1">Painel do Cliente</div>
                    <div class="text-muted">Bem-vindo(a), {{ auth()->user()->name }}</div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark border">{{ auth()->user()->email }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary" href="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() }}">
                        <i class="fa-solid fa-house me-1"></i>
                        Página Principal
                    </a>
                    <a class="btn btn-primary shadow-sm fw-semibold px-4 py-2 rounded-pill w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('cliente.busca') }}" style="background: linear-gradient(135deg, #0d6efd, #198754); border: 0;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="d-inline-block text-start">
                            <span class="d-block">Buscar medicamentos</span>
                            <span class="d-block small" style="opacity: .9;">Ver estoque e rotas</span>
                        </span>
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('cliente.pedidos.index') }}">
                        <i class="fa-solid fa-bag-shopping me-1"></i>
                        Meus pedidos
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('notificacoes.index') }}">
                        <i class="fa-regular fa-bell me-1"></i>
                        Notificações
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Total de pedidos</div>
                            <div class="h4 mb-0">{{ $totalOrders ?? 0 }}</div>
                            <div class="small text-muted">Histórico completo</div>
                        </div>
                        <div class="text-primary" style="font-size: 1.6rem;">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Pedidos em curso</div>
                            <div class="h4 mb-0">{{ $inProgressOrders ?? 0 }}</div>
                            <div class="small text-muted">Pendentes e agendamentos</div>
                        </div>
                        <div class="text-warning" style="font-size: 1.6rem;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Último pedido</div>
                    @if (!empty($lastOrder))
                        <div class="h6 mb-1">
                            #{{ $lastOrder->id }}
                            <span class="text-muted">({{ $lastOrder->status_label }})</span>
                        </div>
                        <div class="small text-muted">
                            {{ optional($lastOrder->created_at)->format('d/m/Y H:i') }}
                            @if (optional($lastOrder->pharmacy)->business_name)
                                · {{ optional($lastOrder->pharmacy)->business_name }}
                            @endif
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('cliente.pedidos.index') }}" class="btn btn-outline-primary btn-sm">Ver pedidos</a>
                        </div>
                    @else
                        <div class="h5 mb-0">—</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total gasto (aprox.)</div>
                    <div class="h4 mb-0">{{ number_format((float) ($totalSpent ?? 0), 0, ',', '.') }} Kz</div>
                    <div class="small text-muted mt-1">*Não inclui pedidos cancelados</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Resumo por estado</div>
                    <div class="text-muted small">Atualizado em tempo real</div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded" style="background: rgba(0,0,0,0.03);">
                                <div class="small text-muted">Pendentes</div>
                                <div class="fw-semibold">{{ (int) (($countsByStatus['pending'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded" style="background: rgba(0,0,0,0.03);">
                                <div class="small text-muted">Agendamento</div>
                                <div class="fw-semibold">{{ (int) (($countsByStatus['schedule_requested'] ?? 0) + ($countsByStatus['schedule_confirmed'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded" style="background: rgba(0,0,0,0.03);">
                                <div class="small text-muted">Prontos</div>
                                <div class="fw-semibold">{{ (int) (($countsByStatus['ready_for_pickup'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded" style="background: rgba(0,0,0,0.03);">
                                <div class="small text-muted">Entregues</div>
                                <div class="fw-semibold">{{ (int) (($countsByStatus['delivered'] ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="fw-semibold">Últimos pedidos</div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('client.atividades.clear') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Limpar as suas atividades?">
                        <i class="fa-solid fa-broom me-1"></i>
                        Limpar atividades
                    </button>
                </form>
                <a href="{{ route('cliente.pedidos.index') }}" class="btn btn-outline-primary btn-sm">Ver todos</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Farmácia</th>
                            <th>Estado</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusMap2 = [
                                'pending' => 'Pendente',
                                'schedule_requested' => 'Agendamento solicitado',
                                'schedule_confirmed' => 'Agendamento confirmado',
                                'ready_for_pickup' => 'Pronto',
                                'delivered' => 'Entregue',
                                'cancelled' => 'Cancelado',
                            ];
                        @endphp
                        @forelse (($recentOrders ?? []) as $o)
                            @php
                                $label = $statusMap2[$o->status] ?? $o->status;
                                $badge = 'bg-secondary';
                                if ($o->status === 'delivered') $badge = 'bg-success';
                                elseif ($o->status === 'ready_for_pickup') $badge = 'bg-primary';
                                elseif ($o->status === 'pending' || $o->status === 'schedule_requested' || $o->status === 'schedule_confirmed') $badge = 'bg-warning text-dark';
                                elseif ($o->status === 'cancelled') $badge = 'bg-danger';
                            @endphp
                            <tr>
                                <td class="fw-semibold">#{{ $o->id }}</td>
                                <td>{{ optional($o->pharmacy)->business_name ?: '—' }}</td>
                                <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                                <td class="text-end">{{ number_format((float) $o->total_price, 0, ',', '.') }} Kz</td>
                                <td class="text-end small text-muted">{{ optional($o->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted p-3">Ainda não existem pedidos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
