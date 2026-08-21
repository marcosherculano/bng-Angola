@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Criar pedido</div>
            <div class="text-muted">{{ $medicine->name }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('cliente.busca') }}">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <div class="fw-semibold">Farmácia</div>
                <div class="text-muted">
                    {{ isset($pharmacy) ? (optional($pharmacy)->business_name ?: '—') : (optional($medicine->pharmacy)->business_name ?: '—') }}
                    —
                    {{ isset($branch) && $branch ? (optional($branch)->province ?: '—') : (isset($pharmacy) ? (optional($pharmacy)->province ?: '—') : (optional($medicine->pharmacy)->province ?: '—')) }}
                    @if (isset($branch) && $branch)
                        <span class="mx-2 text-muted">•</span>
                        Filial: {{ $branch->branch_name }}
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('cliente.pedidos.store') }}">
                @csrf
                <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                <input type="hidden" name="inventory_id" value="{{ old('inventory_id', isset($inventory) && $inventory ? $inventory->id : request('inventory_id')) }}">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Quantidade</label>
                        <input class="form-control" type="number" name="quantity" min="1" max="999" step="1" value="{{ old('quantity', 1) }}" required>
                        <div class="form-text">Stock actual: {{ isset($inventory) && $inventory ? (int) $inventory->stock : (int) $medicine->stock }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Preço unitário</label>
                        <input class="form-control" value="{{ number_format((float) (isset($inventory) && $inventory ? $inventory->price : $medicine->price), 0, ',', '.') }} Kz" disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" name="customer_notes" rows="3" maxlength="2000" placeholder="Ex.: preciso de 2 caixas, alternativa genérica, etc.">{{ old('customer_notes') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Método</label>
                        <select class="form-select" name="pickup_method">
                            <option value="pickup" @selected(old('pickup_method', 'pickup') === 'pickup')>Levantamento na farmácia</option>
                            <option value="external_transport" @selected(old('pickup_method') === 'external_transport')>Transporte externo</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Transporte externo (se aplicável)</label>
                        <input class="form-control" name="external_transport_name" maxlength="255" value="{{ old('external_transport_name') }}" placeholder="Ex.: Moto Táxi, Entregas XYZ">
                    </div>

                    <div class="col-12" id="externalTransportApps" style="display: none;">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="fw-semibold mb-1">Apps de transporte (opcional)</div>
                                <div class="text-muted small mb-2">Use estes links apenas para facilitar o acesso (não interfere com o agendamento).</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-dark" href="https://www.google.com/search?q=UGO+Angola+app" target="_blank" rel="noopener">UGO</a>
                                    <a class="btn btn-sm btn-outline-dark" href="https://www.google.com/search?q=Heetch+Angola+app" target="_blank" rel="noopener">Heetch</a>
                                    <a class="btn btn-sm btn-outline-dark" href="https://www.google.com/search?q=Yango+Angola+app" target="_blank" rel="noopener">Yango</a>
                                    <a class="btn btn-sm btn-outline-dark" href="https://www.google.com/search?q=Bolt+Angola+app" target="_blank" rel="noopener">Bolt</a>
                                    <a class="btn btn-sm btn-outline-dark" href="https://www.google.com/search?q=Alure+T%C3%A1xi+Angola+app" target="_blank" rel="noopener">Alure Táxi</a>
                                </div>
                                <div class="text-muted small mt-2">
                                    Luanda: UGO, Heetch, Yango, Bolt
                                    <br>
                                    18 províncias: Alure Táxi
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agendar para (opcional)</label>
                        <input class="form-control" type="datetime-local" name="scheduled_pickup_at" value="{{ old('scheduled_pickup_at') }}">
                        <div class="form-text">Se preencher, o pedido ficará como agendamento solicitado.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notas de agendamento (opcional)</label>
                        <textarea class="form-control" name="schedule_notes" rows="2" maxlength="2000" placeholder="Ex.: Vou buscar após as 17h; confirmar por telefone.">{{ old('schedule_notes') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2 justify-content-end">
                    <a class="btn btn-outline-secondary" href="{{ route('cliente.busca') }}">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-check"></i>
                        <span class="ms-1">Confirmar pedido</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        var pickupSelect = document.querySelector('select[name="pickup_method"]');
        var appsBox = document.getElementById('externalTransportApps');

        if (!pickupSelect || !appsBox) return;

        function syncAppsVisibility() {
            var isExternal = pickupSelect.value === 'external_transport';
            appsBox.style.display = isExternal ? '' : 'none';
        }

        pickupSelect.addEventListener('change', syncAppsVisibility);
        syncAppsVisibility();
    })();
</script>
@endsection
