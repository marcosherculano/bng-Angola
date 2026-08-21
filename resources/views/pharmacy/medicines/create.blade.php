@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Novo medicamento</div>
            <div class="text-muted">Adicionar ao catálogo</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('pharmacy.medicines.index') }}">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('pharmacy.medicines.store') }}">
                @csrf

                @include('pharmacy.medicines.partials.form', ['medicine' => null])

                <div class="d-flex gap-2 justify-content-end">
                    <a class="btn btn-outline-secondary" href="{{ route('pharmacy.medicines.index') }}">Cancelar</a>
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
