@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Pedido #{{ $order->id }}</div>
            <div class="text-muted">{{ optional($order->created_at)->format('Y-m-d H:i') }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('cliente.pedidos.index') }}">Voltar</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Itens</div>
                    <span class="badge bg-secondary" id="orderStatusLabel">{{ $order->status_label }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicamento</th>
                                    <th class="text-end">Qtd</th>
                                    <th class="text-end">Preço</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $it)
                                    <tr>
                                        <td>{{ optional($it->medicine)->name ?: '—' }}</td>
                                        <td class="text-end">{{ $it->quantity }}</td>
                                        <td class="text-end">{{ number_format((float) $it->unit_price, 0, ',', '.') }} Kz</td>
                                        <td class="text-end">{{ number_format((float) $it->subtotal, 0, ',', '.') }} Kz</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end">{{ number_format((float) $order->total_price, 0, ',', '.') }} Kz</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">Pagamento do medicamento</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="fw-semibold">Medicamento pago à farmácia:</div>
                        @if ($order->status === 'paid')
                            <div class="text-success">✅</div>
                        @else
                            <div class="text-muted">Pendente</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="fw-semibold">Entrega até casa</div>
                        @if ($order->pickup_method === 'external_transport')
                            <div class="text-muted">A entrega é feita por um parceiro externo (ex.: Yango/Bolt/Heetch). O pagamento da entrega é tratado diretamente com o parceiro.</div>
                            <div class="small text-muted mt-1">Depois do pagamento do medicamento ser confirmado, a farmácia solicita a entrega e você poderá acompanhar aqui o estado, o motorista e o contacto (quando disponível).</div>
                        @else
                            <div class="text-muted">Levantamento na farmácia.</div>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="mb-2 fw-semibold">Dados para pagamento</div>
                    @if ($paymentSettings && $paymentSettings->is_active)
                        <div class="small">
                            @if (!empty($paymentSettings->iban))
                                <div><span class="text-muted">IBAN/IBAM:</span> <span class="fw-semibold">{{ $paymentSettings->iban }}</span></div>
                            @endif
                            @if (!empty($paymentSettings->express_number))
                                <div><span class="text-muted">Express:</span> <span class="fw-semibold">{{ $paymentSettings->express_number }}</span></div>
                            @endif
                            @if (!empty($paymentSettings->bank_name))
                                <div><span class="text-muted">Banco:</span> {{ $paymentSettings->bank_name }}</div>
                            @endif
                            @if (!empty($paymentSettings->account_holder))
                                <div><span class="text-muted">Titular:</span> {{ $paymentSettings->account_holder }}</div>
                            @endif
                            @if (!empty($paymentSettings->account_number))
                                <div><span class="text-muted">Nº de conta:</span> {{ $paymentSettings->account_number }}</div>
                            @endif
                            @if (!empty($paymentSettings->instructions))
                                <div class="mt-2"><span class="text-muted">Instruções:</span> {{ $paymentSettings->instructions }}</div>
                            @endif
                            <div class="mt-2"><span class="text-muted">Referência sugerida:</span> <span class="fw-semibold">Pedido #{{ $order->id }}</span></div>
                        </div>
                    @else
                        <div class="text-muted">A farmácia ainda não configurou dados de pagamento.</div>
                    @endif

                    <hr class="my-3">

                    <div class="mb-2 fw-semibold">Enviar comprovativo / referência</div>

                    @if ($order->payment && $order->payment->status === 'confirmed')
                        <div class="alert alert-success mb-0">Pagamento confirmado pela farmácia.</div>
                    @elseif ($order->payment && $order->payment->status === 'rejected')
                        <div class="alert alert-danger">
                            Pagamento recusado.
                            @if (!empty($order->payment->rejection_reason))
                                <div class="small mt-1">Motivo: {{ $order->payment->rejection_reason }}</div>
                            @endif
                        </div>
                    @elseif ($order->payment && $order->payment->status === 'submitted')
                        <div class="alert alert-warning mb-0">Comprovativo enviado. A aguardar confirmação da farmácia.</div>
                    @else
                        <form method="POST" action="{{ route('cliente.pedidos.payment.submit', $order) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Método</label>
                                    <select class="form-select" name="method" required>
                                        <option value="express" @selected(old('method')==='express')>Express</option>
                                        <option value="iban" @selected(old('method')==='iban')>IBAN/IBAM</option>
                                        <option value="other" @selected(old('method')==='other')>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Referência (opcional)</label>
                                    <input class="form-control" name="reference" maxlength="120" value="{{ old('reference', 'Pedido #'.$order->id) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Comprovativo (PDF/JPG/PNG)</label>
                                    <input class="form-control" type="file" name="proof" accept="application/pdf,image/png,image/jpeg" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa-solid fa-upload"></i>
                                    <span class="ms-1">Enviar</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Farmácia</div>
                <div class="card-body">
                    <div class="fw-semibold">{{ optional($order->pharmacy)->business_name ?: '—' }}</div>
                    <div class="text-muted">{{ optional($order->pharmacy)->province ?: '—' }}</div>
                    @if ($order->branch)
                        <div class="text-muted">Filial: {{ $order->branch->branch_name }}</div>
                    @endif
                </div>
            </div>

            @if (in_array((string) $order->status, ['paid','ready_for_pickup','delivery_requested','delivery_in_progress','delivered'], true))
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-white fw-semibold">Fatura</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary" href="{{ route('cliente.pedidos.invoice', $order) }}">
                                <i class="fa-solid fa-receipt"></i>
                                <span class="ms-1">Ver fatura</span>
                            </a>
                            <a class="btn btn-primary" href="{{ route('cliente.pedidos.invoice.download', $order) }}">
                                <i class="fa-solid fa-file-arrow-down"></i>
                                <span class="ms-1">Baixar</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">Entrega / Levantamento</div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="text-muted small">Método</div>
                        <div class="fw-semibold">{{ $order->pickup_method ?: '—' }}</div>
                    </div>

                    @if ($order->pickup_method === 'external_transport')
                        <div class="mb-2">
                            <div class="text-muted small">Transporte (informado)</div>
                            <div class="fw-semibold">{{ $order->external_transport_name ?: '—' }}</div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#deliverySummaryClientBox" aria-expanded="false" aria-controls="deliverySummaryClientBox">
                                <i class="fa-solid fa-clipboard"></i>
                                <span class="ms-1">Resumo para transporte</span>
                            </button>
                        </div>

                        <div class="collapse mb-3" id="deliverySummaryClientBox">
                            @php
                                $originName = $order->branch ? (optional($order->branch)->branch_name ?: 'Filial') : (optional($order->pharmacy)->business_name ?: 'Farmácia');
                                $originPhone = $order->branch ? (optional($order->branch)->phone ?: optional($order->pharmacy)->phone) : (optional($order->pharmacy)->phone);
                                $originParts = [];
                                $originParts[] = (string) (optional($order->pharmacy)->province ?: '');
                                $originParts[] = (string) (optional($order->pharmacy)->city ?: '');
                                $originParts[] = (string) (optional($order->pharmacy)->neighborhood ?: '');
                                $originParts[] = (string) (optional($order->pharmacy)->street ?: '');
                                $originAddress = trim(implode(', ', array_values(array_filter($originParts, fn($v) => trim((string) $v) !== ''))));
                                $originLat = $order->branch ? (optional($order->branch)->latitude ?: optional($order->pharmacy)->latitude) : optional($order->pharmacy)->latitude;
                                $originLng = $order->branch ? (optional($order->branch)->longitude ?: optional($order->pharmacy)->longitude) : optional($order->pharmacy)->longitude;

                                $clientName = (string) (optional($order->client)->name ?: '');
                                $clientPhone = (string) (optional($order->client)->phone ?: '');
                                $clientProvince = (string) (optional($order->client)->province ?: '');
                                $clientLat = optional($order->client)->location_lat;
                                $clientLng = optional($order->client)->location_lng;

                                $summaryLines = [];
                                $summaryLines[] = 'Pedido #'.$order->id;
                                $summaryLines[] = 'Parceiro: '.(optional($order->delivery)->partner ?: ($order->external_transport_name ?: 'Yango'));
                                $summaryLines[] = 'ORIGEM: '.$originName;
                                if (! empty($originPhone)) {
                                    $summaryLines[] = 'Contacto origem: '.$originPhone;
                                }
                                if ($originAddress !== '') {
                                    $summaryLines[] = 'Morada origem: '.$originAddress;
                                }
                                if (! is_null($originLat) && ! is_null($originLng)) {
                                    $summaryLines[] = 'GPS origem: '.$originLat.', '.$originLng;
                                }
                                $summaryLines[] = 'DESTINO: '.$clientName;
                                if ($clientPhone !== '') {
                                    $summaryLines[] = 'Contacto destino: '.$clientPhone;
                                }
                                if ($clientProvince !== '') {
                                    $summaryLines[] = 'Província destino: '.$clientProvince;
                                }
                                if (! is_null($clientLat) && ! is_null($clientLng)) {
                                    $summaryLines[] = 'GPS destino: '.$clientLat.', '.$clientLng;
                                }
                                if (! empty($order->customer_notes)) {
                                    $summaryLines[] = 'Observações: '.trim((string) $order->customer_notes);
                                }
                                $summaryText = trim(implode("\n", $summaryLines));
                            @endphp

                            <textarea id="deliverySummaryClientText" class="form-control" rows="7" readonly style="white-space: pre-wrap;">{{ $summaryText }}</textarea>

                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="(function(){var el=document.getElementById('deliverySummaryClientText'); if(!el){return;} el.focus(); el.select(); try{document.execCommand('copy');}catch(e){} })();">
                                    <i class="fa-solid fa-copy"></i>
                                    <span class="ms-1">Copiar resumo</span>
                                </button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="text-muted small">Estado da entrega</div>
                            <div class="fw-semibold" id="deliveryStatusLabel">{{ $order->status_label }}</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Parceiro</div>
                                <div class="fw-semibold" id="deliveryPartner">{{ optional($order->delivery)->partner ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">ID externo</div>
                                <div class="fw-semibold" id="deliveryExternalId">{{ optional($order->delivery)->external_id ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Solicitado em</div>
                                <div class="fw-semibold" id="deliveryRequestedAt">{{ optional(optional($order->delivery)->requested_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Iniciado em</div>
                                <div class="fw-semibold" id="deliveryStartedAt">{{ optional(optional($order->delivery)->started_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Entregue em</div>
                                <div class="fw-semibold" id="deliveryDeliveredAt">{{ optional(optional($order->delivery)->delivered_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Motorista</div>
                                <div class="fw-semibold" id="deliveryDriverName">{{ optional($order->delivery)->driver_name ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Telefone</div>
                                <div class="fw-semibold" id="deliveryDriverPhone">{{ optional($order->delivery)->driver_phone ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Preço estimado</div>
                                <div class="fw-semibold" id="deliveryEstimatedPrice">
                                    @if (!is_null(optional($order->delivery)->estimated_price))
                                        {{ number_format((float) $order->delivery->estimated_price, 0, ',', '.') }} {{ $order->delivery->currency ?: 'Kz' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($order->status === 'pending')
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <form method="POST" action="{{ route('cliente.pedidos.cancel', $order) }}">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-outline-danger w-100" type="submit" data-confirm="Cancelar este pedido?">
                                <i class="fa-solid fa-xmark"></i>
                                <span class="ms-1">Cancelar pedido</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var url = @json(route('cliente.pedidos.status', $order));

        var statusEl = document.getElementById('orderStatusLabel');
        var deliveryStatusEl = document.getElementById('deliveryStatusLabel');
        var partnerEl = document.getElementById('deliveryPartner');
        var externalIdEl = document.getElementById('deliveryExternalId');
        var requestedAtEl = document.getElementById('deliveryRequestedAt');
        var startedAtEl = document.getElementById('deliveryStartedAt');
        var deliveredAtEl = document.getElementById('deliveryDeliveredAt');
        var driverNameEl = document.getElementById('deliveryDriverName');
        var driverPhoneEl = document.getElementById('deliveryDriverPhone');
        var priceEl = document.getElementById('deliveryEstimatedPrice');

        function setText(el, value) {
            if (!el) return;
            el.textContent = (value && String(value).trim() !== '') ? value : '—';
        }

        function formatKz(amount, currency) {
            if (amount === null || typeof amount === 'undefined') return '—';
            var n = Number(amount);
            if (Number.isNaN(n)) return '—';

            try {
                return n.toLocaleString('pt-PT', { maximumFractionDigits: 0 }) + ' ' + (currency || 'Kz');
            } catch (e) {
                return String(n) + ' ' + (currency || 'Kz');
            }
        }

        function formatDateTimePt(value) {
            if (!value) return '—';
            try {
                var d = new Date(value);
                if (Number.isNaN(d.getTime())) return '—';
                return d.toLocaleString('pt-PT', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return '—';
            }
        }

        var stopped = false;
        var terminal = { delivered: true, cancelled: true };

        async function poll() {
            if (stopped) return;

            try {
                var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                var data = await res.json();

                if (statusEl && data.status_label) {
                    statusEl.textContent = data.status_label;
                }
                if (deliveryStatusEl && data.status_label) {
                    deliveryStatusEl.textContent = data.status_label;
                }

                if (data.delivery) {
                    setText(partnerEl, data.delivery.partner);
                    setText(externalIdEl, data.delivery.external_id);
                    if (requestedAtEl) requestedAtEl.textContent = formatDateTimePt(data.delivery.requested_at);
                    if (startedAtEl) startedAtEl.textContent = formatDateTimePt(data.delivery.started_at);
                    if (deliveredAtEl) deliveredAtEl.textContent = formatDateTimePt(data.delivery.delivered_at);
                    setText(driverNameEl, data.delivery.driver_name);
                    setText(driverPhoneEl, data.delivery.driver_phone);

                    if (priceEl) {
                        priceEl.textContent = formatKz(data.delivery.estimated_price, data.delivery.currency);
                    }
                }

                if (terminal[String(data.status || '')]) {
                    stopped = true;
                }
            } catch (e) {
                // noop
            }
        }

        setInterval(poll, 15000);
        poll();
    })();
</script>
@endpush
