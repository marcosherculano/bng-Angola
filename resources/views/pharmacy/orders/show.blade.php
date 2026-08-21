@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Detalhes do pedido #{{ $order->id }}</div>
            <div class="text-muted">{{ optional($order->created_at)->format('Y-m-d H:i') }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar ao painel</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Itens</div>
                    <span class="badge bg-secondary">{{ $order->status_label }}</span>
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

                    @if ($order->pickup_method === 'external_transport' && (string) $order->status === 'paid')
                        <div class="collapse mt-3" id="requestDeliveryBox">
                            <form method="POST" action="{{ route('pharmacy.orders.requestDelivery', $order) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Parceiro</label>
                                        <select class="form-select" name="partner">
                                            <option value="">—</option>
                                            @foreach (['Kubinga','T’Leva','UGO','Heetch','Yango','Bolt'] as $p)
                                                <option value="{{ $p }}" @selected(optional($order->delivery)->partner === $p)>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">ID externo (opcional)</label>
                                        <input class="form-control" name="external_id" maxlength="120" value="{{ old('external_id', optional($order->delivery)->external_id) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Telefone do motorista (opcional)</label>
                                        <input class="form-control" name="driver_phone" maxlength="60" value="{{ old('driver_phone', optional($order->delivery)->driver_phone) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Motorista (opcional)</label>
                                        <input class="form-control" name="driver_name" maxlength="120" value="{{ old('driver_name', optional($order->delivery)->driver_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Preço estimado (opcional)</label>
                                        <input class="form-control" type="number" step="0.01" min="0" name="estimated_price" value="{{ old('estimated_price', optional($order->delivery)->estimated_price) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Moeda</label>
                                        <input class="form-control" name="currency" maxlength="10" value="{{ old('currency', optional($order->delivery)->currency ?: 'Kz') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notas (opcional)</label>
                                        <textarea class="form-control" name="notes" rows="2" maxlength="2000">{{ old('notes', optional($order->delivery)->notes) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button class="btn btn-sm btn-outline-primary" type="submit" data-confirm="Solicitar entrega externa para este pedido?">
                                        <i class="fa-solid fa-truck"></i>
                                        <span class="ms-1">Confirmar solicitação</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        @if (in_array((string) $order->status, ['delivery_requested', 'delivery_in_progress'], true))
                            <div class="collapse mb-3" id="cancelDeliveryBox">
                                <form method="POST" action="{{ route('pharmacy.orders.cancelDelivery', $order) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-2">
                                        <label class="form-label">Motivo (opcional)</label>
                                        <textarea class="form-control" name="cancel_reason" rows="2" maxlength="2000" placeholder="Ex.: cliente indisponível, morada incorreta, sem motorista, etc."></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Cancelar a entrega externa deste pedido?">
                                            <i class="fa-solid fa-ban"></i>
                                            <span class="ms-1">Confirmar cancelamento</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endif

                    @if ($order->pickup_method === 'external_transport' && (string) $order->status === 'delivery_requested')
                        <div class="collapse mt-3" id="startDeliveryBox">
                            <form method="POST" action="{{ route('pharmacy.orders.startDelivery', $order) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">ID externo (opcional)</label>
                                        <input class="form-control" name="external_id" maxlength="120" value="{{ old('external_id', optional($order->delivery)->external_id) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Motorista (opcional)</label>
                                        <input class="form-control" name="driver_name" maxlength="120" value="{{ old('driver_name', optional($order->delivery)->driver_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Telefone do motorista (opcional)</label>
                                        <input class="form-control" name="driver_phone" maxlength="60" value="{{ old('driver_phone', optional($order->delivery)->driver_phone) }}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button class="btn btn-sm btn-primary" type="submit" data-confirm="Marcar entrega como em curso?">
                                        <i class="fa-solid fa-truck-fast"></i>
                                        <span class="ms-1">Confirmar início</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Pagamento do cliente</div>
                <div class="card-body">
                    @if (! $order->payment)
                        <div class="text-muted">Ainda não foi enviado comprovativo.</div>
                    @else
                        <div class="mb-2">
                            <div class="text-muted small">Estado</div>
                            <div class="fw-semibold">{{ $order->payment->status }}</div>
                        </div>

                        <div class="mb-2">
                            <div class="text-muted small">Método</div>
                            <div class="fw-semibold">{{ $order->payment->method }}</div>
                        </div>

                        <div class="mb-2">
                            <div class="text-muted small">Referência</div>
                            <div class="fw-semibold">{{ $order->payment->reference ?: '—' }}</div>
                        </div>

                        <div>
                            <div class="text-muted small">Comprovativo</div>
                            @if (! empty($order->payment->proof_path))
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.orders.paymentProof', $order) }}" title="Baixar comprovativo" aria-label="Baixar comprovativo" data-bs-toggle="tooltip" data-bs-title="Baixar comprovativo">
                                    <i class="fa-solid fa-file-arrow-down"></i>
                                    <span class="ms-1">Baixar</span>
                                </a>
                            @else
                                <div class="text-muted">—</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if ($order->pickup_method === 'external_transport')
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">Entrega externa</div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            <div class="fw-semibold mb-1">Passo-a-passo (manual)</div>
                            <div class="small text-muted">
                                1) Confirmar pagamento do medicamento.
                                <br>
                                2) Solicitar entrega (muda para entrega solicitada).
                                <br>
                                3) Abrir “Resumo para transporte” e criar a corrida no app do parceiro.
                                <br>
                                4) Preencher ID externo / motorista / telefone (opcional) em “Atualizar dados”.
                                <br>
                                5) Iniciar entrega quando o parceiro confirmar a recolha.
                                <br>
                                6) Marcar como entregue quando o cliente receber.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#deliverySummaryBox" aria-expanded="false" aria-controls="deliverySummaryBox">
                                <i class="fa-solid fa-clipboard"></i>
                                <span class="ms-1">Resumo para transporte</span>
                            </button>

                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#updateDeliveryBox" aria-expanded="false" aria-controls="updateDeliveryBox">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span class="ms-1">Atualizar dados</span>
                            </button>

                            @if (in_array((string) $order->status, ['delivery_requested', 'delivery_in_progress'], true))
                                <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#cancelDeliveryBox" aria-expanded="false" aria-controls="cancelDeliveryBox">
                                    <i class="fa-solid fa-ban"></i>
                                    <span class="ms-1">Cancelar entrega</span>
                                </button>
                            @endif
                        </div>

                        <div class="collapse mb-3" id="deliverySummaryBox">
                            @php
                                $originName = $order->pharmacy_branch_id ? (optional($order->branch)->branch_name ?: 'Filial') : (optional($pharmacy)->business_name ?: 'Farmácia');
                                $originPhone = $order->pharmacy_branch_id ? (optional($order->branch)->phone ?: optional($pharmacy)->phone) : (optional($pharmacy)->phone);
                                $originParts = [];
                                $originParts[] = (string) (optional($pharmacy)->province ?: '');
                                $originParts[] = (string) (optional($pharmacy)->city ?: '');
                                $originParts[] = (string) (optional($pharmacy)->neighborhood ?: '');
                                $originParts[] = (string) (optional($pharmacy)->street ?: '');
                                $originAddress = trim(implode(', ', array_values(array_filter($originParts, fn($v) => trim((string) $v) !== ''))));
                                $originLat = $order->pharmacy_branch_id ? (optional($order->branch)->latitude ?: optional($pharmacy)->latitude) : optional($pharmacy)->latitude;
                                $originLng = $order->pharmacy_branch_id ? (optional($order->branch)->longitude ?: optional($pharmacy)->longitude) : optional($pharmacy)->longitude;

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

                            <div class="mb-2">
                                <div class="text-muted small">Copie e cole no app do parceiro ao criar a corrida</div>
                            </div>

                            <textarea id="deliverySummaryText" class="form-control" rows="7" readonly style="white-space: pre-wrap;">{{ $summaryText }}</textarea>

                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="(function(){var el=document.getElementById('deliverySummaryText'); if(!el){return;} el.focus(); el.select(); try{document.execCommand('copy');}catch(e){} })();">
                                    <i class="fa-solid fa-copy"></i>
                                    <span class="ms-1">Copiar resumo</span>
                                </button>
                            </div>
                        </div>

                        <div class="collapse mb-3" id="updateDeliveryBox">
                            <form method="POST" action="{{ route('pharmacy.orders.delivery.update', $order) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Parceiro</label>
                                        <select class="form-select" name="partner">
                                            <option value="">—</option>
                                            @foreach (['Kubinga','T’Leva','UGO','Heetch','Yango','Bolt'] as $p)
                                                <option value="{{ $p }}" @selected(old('partner', optional($order->delivery)->partner) === $p)>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">ID externo (opcional)</label>
                                        <input class="form-control" name="external_id" maxlength="120" value="{{ old('external_id', optional($order->delivery)->external_id) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Telefone do motorista (opcional)</label>
                                        <input class="form-control" name="driver_phone" maxlength="60" value="{{ old('driver_phone', optional($order->delivery)->driver_phone) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Motorista (opcional)</label>
                                        <input class="form-control" name="driver_name" maxlength="120" value="{{ old('driver_name', optional($order->delivery)->driver_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Preço estimado (opcional)</label>
                                        <input class="form-control" type="number" step="0.01" min="0" name="estimated_price" value="{{ old('estimated_price', optional($order->delivery)->estimated_price) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Moeda</label>
                                        <input class="form-control" name="currency" maxlength="10" value="{{ old('currency', optional($order->delivery)->currency ?: 'Kz') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notas (opcional)</label>
                                        <textarea class="form-control" name="notes" rows="2" maxlength="2000">{{ old('notes', optional($order->delivery)->notes) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span class="ms-1">Guardar</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Parceiro</div>
                                <div class="fw-semibold">{{ optional($order->delivery)->partner ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">ID externo</div>
                                <div class="fw-semibold">{{ optional($order->delivery)->external_id ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Motorista</div>
                                <div class="fw-semibold">{{ optional($order->delivery)->driver_name ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Telefone</div>
                                <div class="fw-semibold">{{ optional($order->delivery)->driver_phone ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Preço estimado</div>
                                <div class="fw-semibold">
                                    @if (!is_null(optional($order->delivery)->estimated_price))
                                        {{ number_format((float) $order->delivery->estimated_price, 0, ',', '.') }} {{ $order->delivery->currency ?: 'Kz' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Estado</div>
                                <div class="fw-semibold">{{ optional($order->delivery)->status ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Solicitado em</div>
                                <div class="fw-semibold">{{ optional(optional($order->delivery)->requested_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Iniciado em</div>
                                <div class="fw-semibold">{{ optional(optional($order->delivery)->started_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Entregue em</div>
                                <div class="fw-semibold">{{ optional(optional($order->delivery)->delivered_at)->format('d/m/Y H:i') ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Notas</div>
                                <div>{{ optional($order->delivery)->notes ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Cliente</div>
                <div class="card-body">
                    <div class="fw-semibold">{{ optional($order->client)->name ?: '—' }}</div>
                    <div class="text-muted">{{ optional($order->client)->email ?: '—' }}</div>
                    <div class="text-muted">{{ optional($order->client)->phone ?: '—' }}</div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Ações</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if ($order->payment && $order->payment->status === 'submitted')
                            <form method="POST" action="{{ route('pharmacy.orders.confirmPayment', $order) }}">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm btn-success" type="submit" data-confirm="Confirmar o pagamento deste pedido?">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span class="ms-1">Confirmar pagamento</span>
                                </button>
                            </form>

                            <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#rejectPaymentBox" aria-expanded="false" aria-controls="rejectPaymentBox">
                                <i class="fa-solid fa-ban"></i>
                                <span class="ms-1">Recusar pagamento</span>
                            </button>
                        @endif

                        @if ($order->pickup_method === 'external_transport' && (string) $order->status === 'paid')
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#requestDeliveryBox" aria-expanded="false" aria-controls="requestDeliveryBox">
                                <i class="fa-solid fa-truck"></i>
                                <span class="ms-1">Solicitar entrega</span>
                            </button>
                        @endif

                        @if ($order->pickup_method === 'external_transport' && (string) $order->status === 'delivery_requested')
                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#startDeliveryBox" aria-expanded="false" aria-controls="startDeliveryBox">
                                <i class="fa-solid fa-truck-fast"></i>
                                <span class="ms-1">Iniciar entrega</span>
                            </button>
                        @endif

                        @if ($order->pickup_method !== 'external_transport' && (string) $order->status === 'paid')
                            <form method="POST" action="{{ route('pharmacy.orders.ready', $order) }}">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm btn-success" type="submit" data-confirm="Marcar este pedido como pronto para levantamento?" onclick="event.preventDefault(); event.stopPropagation(); this.closest('form').submit();">
                                    <i class="fa-solid fa-check"></i>
                                    <span class="ms-1">Marcar pronto</span>
                                </button>
                            </form>
                        @endif

                        @if (
                            ($order->pickup_method !== 'external_transport' && (string) $order->status === 'ready_for_pickup')
                            || ($order->pickup_method === 'external_transport' && (string) $order->status === 'delivery_in_progress')
                        )
                            <form method="POST" action="{{ route('pharmacy.orders.delivered', $order) }}">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm btn-primary" type="submit" data-confirm="Marcar este pedido como entregue?">
                                    <i class="fa-solid fa-box"></i>
                                    <span class="ms-1">Marcar entregue</span>
                                </button>
                            </form>
                        @endif

                        @if (! in_array($order->status, ['delivered','cancelled'], true))
                            <form method="POST" action="{{ route('pharmacy.orders.cancel', $order) }}">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Cancelar este pedido?">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span class="ms-1">Cancelar</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Entrega / Levantamento</div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="text-muted small">Método</div>
                        <div class="fw-semibold">{{ $order->pickup_method ?: '—' }}</div>
                    </div>

                    @if ($order->pickup_method === 'external_transport')
                        <div class="mb-2">
                            <div class="text-muted small">Transporte</div>
                            <div class="fw-semibold">{{ $order->external_transport_name ?: '—' }}</div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <div class="text-muted small">Agendado para</div>
                        <div class="fw-semibold">{{ optional($order->scheduled_pickup_at)->format('Y-m-d H:i') ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-muted small">Notas de agendamento</div>
                        @if (! empty($order->schedule_notes))
                            <div class="small" style="white-space: pre-wrap;">{{ $order->schedule_notes }}</div>
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </div>

                    @if ($order->status === 'schedule_requested')
                        <hr class="my-3">
                        <form method="POST" action="{{ route('pharmacy.orders.confirmSchedule', $order) }}">
                            @csrf
                            @method('PUT')
                            <label class="form-label">Confirmar (opcional: actualizar notas)</label>
                            <textarea class="form-control mb-2" name="schedule_notes" rows="2" maxlength="2000" placeholder="Notas internas / confirmação para o cliente..."></textarea>
                            <button class="btn btn-sm btn-primary" type="submit" data-confirm="Confirmar o agendamento deste pedido?">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span class="ms-1">Confirmar agendamento</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Observações do cliente</div>
                <div class="card-body">
                    @if (! empty($order->customer_notes))
                        <div class="small" style="white-space: pre-wrap;">{{ $order->customer_notes }}</div>
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
