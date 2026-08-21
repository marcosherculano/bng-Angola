@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Configurações de Pagamento</div>
            <div class="text-muted">Defina os dados que serão mostrados ao cliente para pagamento do medicamento</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar ao painel</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('pharmacy.payment_settings.update') }}">
                @csrf
                @method('PUT')

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" value="1" @checked(old('is_active', optional($settings)->is_active ? 1 : 0) == 1)>
                    <label class="form-check-label" for="isActive">Mostrar dados de pagamento ao cliente</label>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Banco (opcional)</label>
                        <input class="form-control" name="bank_name" maxlength="150" value="{{ old('bank_name', optional($settings)->bank_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Titular (opcional)</label>
                        <input class="form-control" name="account_holder" maxlength="150" value="{{ old('account_holder', optional($settings)->account_holder) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nº de Conta (opcional)</label>
                        <input class="form-control" name="account_number" maxlength="80" value="{{ old('account_number', optional($settings)->account_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">IBAN/IBAM (opcional)</label>
                        <input class="form-control" name="iban" maxlength="80" value="{{ old('iban', optional($settings)->iban) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Express (Nº/Conta) (opcional)</label>
                        <input class="form-control" name="express_number" maxlength="80" value="{{ old('express_number', optional($settings)->express_number) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Instruções (opcional)</label>
                        <textarea class="form-control" name="instructions" rows="3" maxlength="2000" placeholder="Ex.: Envie o comprovativo e indique o Nº do pedido na descrição.">{{ old('instructions', optional($settings)->instructions) }}</textarea>
                        <div class="form-text">O cliente poderá ver estas instruções no momento do pedido.</div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span class="ms-1">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
