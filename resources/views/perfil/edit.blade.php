@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Editar perfil</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('perfil.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label class="form-label">Nome</label>
                            <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email (login)</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Província</label>
                            <input class="form-control" name="province" value="{{ old('province', $user->province) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Senha actual (obrigatória para alterar email e/ou senha)</label>
                            <input class="form-control" type="password" name="current_password" autocomplete="current-password">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nova senha (opcional)</label>
                            <input class="form-control" type="password" name="password" autocomplete="new-password">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirmar nova senha</label>
                            <input class="form-control" type="password" name="password_confirmation" autocomplete="new-password">
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary">Guardar alterações</button>
                            <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
