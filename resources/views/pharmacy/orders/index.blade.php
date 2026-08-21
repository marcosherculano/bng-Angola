@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Pedidos recebidos</div>
            <div class="text-muted">Gestão de pedidos da farmácia</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar ao painel</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pharmacy.orders.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        @foreach (['pending','paid','schedule_requested','schedule_confirmed','ready_for_pickup','delivery_requested','delivery_in_progress','delivered','cancelled'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ \App\Models\Order::STATUS_LABELS[$st] ?? $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">
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
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th>Método</th>
                            <th>Agendamento</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Data</th>
                            <th class="text-end" style="width: 220px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr>
                                <td class="fw-semibold">{{ $o->id }}</td>
                                <td>{{ optional($o->client)->name ?: optional($o->client)->email }}</td>
                                <td>
                                    <div class="small">
                                        @foreach ($o->items as $it)
                                            <div>
                                                <span class="fw-semibold">{{ optional($it->medicine)->name ?: '—' }}</span>
                                                <span class="text-muted">x{{ $it->quantity }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="fw-semibold">{{ $o->pickup_method ?: '—' }}</div>
                                        @if ($o->pickup_method === 'external_transport')
                                            <div class="text-muted">{{ $o->external_transport_name ?: '—' }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($o->scheduled_pickup_at)
                                        <div class="small fw-semibold">{{ optional($o->scheduled_pickup_at)->format('Y-m-d H:i') }}</div>
                                        @if ($o->status === 'schedule_confirmed')
                                            <span class="badge bg-success">Confirmado</span>
                                        @elseif ($o->status === 'schedule_requested')
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary">{{ $o->status_label }}</span></td>
                                <td class="text-end">{{ number_format((float) $o->total_price, 0, ',', '.') }} Kz</td>
                                <td class="text-end">{{ optional($o->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('pharmacy.orders.show', $o) }}" title="Detalhes" aria-label="Detalhes" data-bs-toggle="tooltip" data-bs-title="Detalhes">
                                            <i class="fa-solid fa-eye"></i>
                                            <span class="visually-hidden">Detalhes</span>
                                        </a>

                                        @if (in_array($o->status, ['pending','schedule_confirmed'], true))
                                            <form method="POST" action="{{ route('pharmacy.orders.ready', $o) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-sm btn-success" type="submit" title="Pronto" aria-label="Pronto" data-bs-toggle="tooltip" data-bs-title="Marcar pronto" onclick="event.preventDefault(); event.stopPropagation(); this.closest('form').submit();">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span class="visually-hidden">Pronto</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($o->status === 'ready_for_pickup')
                                            <form method="POST" action="{{ route('pharmacy.orders.delivered', $o) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-sm btn-primary" type="submit" title="Entregue" aria-label="Entregue" data-bs-toggle="tooltip" data-bs-title="Marcar entregue" data-confirm="Marcar este pedido como entregue?">
                                                    <i class="fa-solid fa-box"></i>
                                                    <span class="visually-hidden">Entregue</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if (! in_array($o->status, ['delivered','cancelled'], true))
                                            <form method="POST" action="{{ route('pharmacy.orders.cancel', $o) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Cancelar" aria-label="Cancelar" data-bs-toggle="tooltip" data-bs-title="Cancelar" data-confirm="Cancelar este pedido?">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span class="visually-hidden">Cancelar</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted p-3">Sem pedidos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="card-footer bg-white">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
