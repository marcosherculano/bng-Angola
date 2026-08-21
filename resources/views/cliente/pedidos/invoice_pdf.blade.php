<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Fatura - Pedido #{{ $order->id }}</title>
    <style>
        :root { --muted: #6c757d; --border: #e9ecef; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; margin: 24px; color: #111; font-size: 12px; }
        .row { width: 100%; }
        .col { width: 48%; display: inline-block; vertical-align: top; }
        .muted { color: var(--muted); }
        .box { border: 1px solid var(--border); border-radius: 6px; padding: 12px; }
        h1 { margin: 0; font-size: 16px; }
        .header { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid var(--border); padding: 6px; text-align: left; }
        th { background: #f8f9fa; }
        .right { text-align: right; }
        .total { font-weight: 700; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fatura / Recibo</h1>
        <div class="muted">Pedido #{{ $order->id }} | Emitido em: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <div class="row">
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 6px;">Farmácia</div>
            <div>{{ optional($order->pharmacy)->business_name ?: '—' }}</div>
            <div class="muted">{{ optional($order->pharmacy)->province ?: '—' }}{{ optional($order->pharmacy)->city ? ', '.optional($order->pharmacy)->city : '' }}</div>
            @if ($order->branch)
                <div class="muted">Filial: {{ $order->branch->branch_name }}</div>
            @endif
        </div>

        <div class="col box" style="margin-left: 4%;">
            <div style="font-weight: 700; margin-bottom: 6px;">Cliente</div>
            <div>{{ optional($order->client)->name ?: '—' }}</div>
            <div class="muted">{{ optional($order->client)->email ?: '—' }}</div>
        </div>
    </div>

    <div class="box" style="margin-top: 12px;">
        <div style="font-weight: 700; margin-bottom: 6px;">Itens</div>
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

    <div class="row" style="margin-top: 12px;">
        <div class="col box">
            <div style="font-weight: 700; margin-bottom: 6px;">Pagamento</div>
            <div><span class="muted">Estado:</span> {{ $order->status_label }}</div>
            @if ($order->payment)
                <div><span class="muted">Método:</span> {{ $order->payment->method }}</div>
                <div><span class="muted">Referência:</span> {{ $order->payment->reference ?: '—' }}</div>
                @if ($order->payment->confirmed_at)
                    <div><span class="muted">Confirmado em:</span> {{ optional($order->payment->confirmed_at)->format('Y-m-d H:i') }}</div>
                @endif
            @endif
        </div>

        <div class="col box" style="margin-left: 4%;">
            <div style="font-weight: 700; margin-bottom: 6px;">Entrega / Levantamento</div>
            <div><span class="muted">Método:</span> {{ $order->pickup_method ?: '—' }}</div>
            @if ($order->pickup_method === 'external_transport')
                <div><span class="muted">Transporte:</span> {{ $order->external_transport_name ?: '—' }}</div>
            @endif
        </div>
    </div>
</body>
</html>
