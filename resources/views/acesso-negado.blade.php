@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Acesso negado</div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        Não tens permissão para aceder a esta área.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
