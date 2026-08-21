@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Meus pedidos</div>
            <div class="text-muted">Acompanhe o estado dos seus pedidos</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('cliente.busca') }}">Buscar medicamentos</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Farmácia</th>
                            <th>Itens</th>
                            <th>Método</th>
                            <th>Agendamento</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Data</th>
                            <th class="text-end" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr>
                                <td class="fw-semibold">{{ $o->id }}</td>
                                <td>{{ optional($o->pharmacy)->business_name ?: '—' }}</td>
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
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary">{{ $o->status_label }}</span></td>
                                <td class="text-end">{{ number_format((float) $o->total_price, 0, ',', '.') }} Kz</td>
                                <td class="text-end">{{ optional($o->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('cliente.pedidos.show', $o) }}" title="Detalhes" aria-label="Detalhes" data-bs-toggle="tooltip" data-bs-title="Detalhes">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="visually-hidden">Detalhes</span>
                                    </a>
                                    @if ($o->status === 'pending')
                                        <form method="POST" action="{{ route('cliente.pedidos.cancel', $o) }}" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Cancelar" aria-label="Cancelar" data-bs-toggle="tooltip" data-bs-title="Cancelar" data-confirm="Cancelar este pedido?">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span class="visually-hidden">Cancelar</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted p-3">Sem pedidos.</td>
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
