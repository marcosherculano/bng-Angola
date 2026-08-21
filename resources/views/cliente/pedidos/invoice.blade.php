<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fatura - Pedido #{{ $order->id }}</title>
    <style>
        :root { --muted: #6c757d; --border: #e9ecef; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; color: #111; }
        .row { display: flex; gap: 24px; }
        .col { flex: 1; }
        .muted { color: var(--muted); }
        .box { border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
        .title { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
        .title h1 { margin: 0; font-size: 18px; }
        .title .id { font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid var(--border); padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f8f9fa; }
        .right { text-align: right; }
        .total { font-size: 14px; font-weight: 700; }
        .actions { margin-top: 16px; display: flex; gap: 8px; }
        .btn { display: inline-block; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; text-decoration: none; color: #111; font-size: 13px; }
        .btn-primary { border-color: #0d6efd; color: #0d6efd; }
        @media print { .actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="title">
        <h1>Fatura / Recibo</h1>
        <div class="id">Pedido #{{ $order->id }}</div>
    </div>
    <div class="muted" style="margin-top: 6px;">Emitido em: {{ now()->format('Y-m-d H:i') }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('cliente.pedidos.show', $order) }}">Voltar</a>
        <a class="btn btn-primary" href="{{ route('cliente.pedidos.invoice.download', $order) }}">Baixar PDF</a>
        <a class="btn" href="#" onclick="window.print(); return false;">Imprimir</a>
    </div>

    <div class="row" style="margin-top: 16px;">
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 8px;">Farmácia</div>
            <div>{{ optional($order->pharmacy)->business_name ?: '—' }}</div>
            <div class="muted">{{ optional($order->pharmacy)->province ?: '—' }}{{ optional($order->pharmacy)->city ? ', '.optional($order->pharmacy)->city : '' }}</div>
            @if ($order->branch)
                <div class="muted">Filial: {{ $order->branch->branch_name }}</div>
            @endif
        </div>
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 8px;">Cliente</div>
            <div>{{ optional($order->client)->name ?: '—' }}</div>
            <div class="muted">{{ optional($order->client)->email ?: '—' }}</div>
        </div>
    </div>

    <div class="box" style="margin-top: 16px;">
        <div style="font-weight: 700;">Itens</div>
        <table>
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th class="right">Qtd</th>
                    <th class="right">Preço</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $it)
                    <tr>
                        <td>{{ optional($it->medicine)->name ?: '—' }}</td>
                        <td class="right">{{ $it->quantity }}</td>
                        <td class="right">{{ number_format((float) $it->unit_price, 0, ',', '.') }} Kz</td>
                        <td class="right">{{ number_format((float) $it->subtotal, 0, ',', '.') }} Kz</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right total">Total</td>
                    <td class="right total">{{ number_format((float) $order->total_price, 0, ',', '.') }} Kz</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row" style="margin-top: 16px;">
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 8px;">Pagamento</div>
            <div><span class="muted">Estado:</span> {{ $order->status_label }}</div>
            @if ($order->payment)
                <div><span class="muted">Método:</span> {{ $order->payment->method }}</div>
                <div><span class="muted">Referência:</span> {{ $order->payment->reference ?: '—' }}</div>
                @if ($order->payment->confirmed_at)
                    <div><span class="muted">Confirmado em:</span> {{ optional($order->payment->confirmed_at)->format('Y-m-d H:i') }}</div>
                @endif
            @endif
        </div>
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 8px;">Entrega / Levantamento</div>
            <div><span class="muted">Método:</span> {{ $order->pickup_method ?: '—' }}</div>
            @if ($order->pickup_method === 'external_transport')
                <div><span class="muted">Transporte:</span> {{ $order->external_transport_name ?: '—' }}</div>
            @endif
        </div>
    </div>
</body>
</html>
