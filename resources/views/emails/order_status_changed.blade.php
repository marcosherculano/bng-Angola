@php
    $statusLabels = [
        'schedule_confirmed' => 'Agendamento confirmado',
        'ready_for_pickup' => 'Pronto para levantamento',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado',
    ];

    $label = $statusLabels[$event] ?? ($order->status ?? 'Atualização');
@endphp

<div style="font-family: Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2 style="margin: 0 0 12px;">{{ $label }}</h2>

    <p style="margin: 0 0 10px;">
        O seu pedido <strong>#{{ $order->id }}</strong> teve uma atualização.
    </p>

    <div style="padding: 12px; background: #f7f7f7; border: 1px solid #e5e5e5; border-radius: 6px;">
        <div><strong>Farmácia:</strong> {{ optional($order->pharmacy)->business_name ?: '—' }}</div>
        <div><strong>Estado:</strong> {{ $order->status }}</div>
        <div><strong>Método:</strong> {{ $order->pickup_method ?: '—' }}</div>

        @if ($order->pickup_method === 'external_transport')
            <div><strong>Transporte:</strong> {{ $order->external_transport_name ?: '—' }}</div>
        @endif

        <div><strong>Agendado para:</strong> {{ optional($order->scheduled_pickup_at)->format('Y-m-d H:i') ?: '—' }}</div>

        @if (! empty($order->schedule_notes))
            <div><strong>Notas:</strong> <span style="white-space: pre-wrap;">{{ $order->schedule_notes }}</span></div>
        @endif

        <div><strong>Total:</strong> {{ number_format((float) $order->total_price, 0, ',', '.') }} Kz</div>
    </div>

    <p style="margin: 12px 0 0; color: #444;">
        Obrigado por usar a nossa plataforma.
    </p>
</div>
