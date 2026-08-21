@extends('layouts.app')

@section('content')
<div class="container py-4">
    <style>
        .bng-auth-wrap {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
        }

        .bng-auth-bg {
            border-radius: 18px;
            padding: 28px;
            background:
                radial-gradient(900px circle at 12% 18%, rgba(26, 60, 110, 0.22), transparent 55%),
                radial-gradient(700px circle at 88% 20%, rgba(241, 141, 0, 0.18), transparent 50%),
                linear-gradient(135deg, rgba(26, 60, 110, 0.06), rgba(241, 141, 0, 0.04));
            border: 1px solid rgba(0,0,0,0.06);
        }

        @media (max-width: 576px) {
            .bng-auth-bg {
                padding: 18px;
            }
        }

        .bng-auth-card {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.14);
            backdrop-filter: blur(10px);
        }

        .bng-auth-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 18px 22px;
        }

        .bng-auth-title {
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .bng-auth-subtitle {
            color: rgba(0,0,0,0.6);
        }

        [data-bs-theme="dark"] .bng-auth-subtitle {
            color: rgba(255,255,255,0.65);
        }

        .bng-input {
            border-radius: 12px;
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
        }

        @media (min-width: 992px) {
            .bng-input {
                padding-top: 0.95rem;
                padding-bottom: 0.95rem;
            }
        }

        .bng-input:focus {
            border-color: var(--bng-primary);
            box-shadow: 0 0 0 .25rem rgba(26, 60, 110, 0.18);
        }

        .bng-btn {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, var(--bng-primary), var(--bng-secondary));
            border: none;
            box-shadow: 0 14px 30px rgba(0,0,0,0.16);
        }

        @media (min-width: 992px) {
            .bng-btn {
                padding: 1rem 1.2rem;
            }
        }

        .bng-btn:hover {
            filter: brightness(1.06);
        }

        .bng-auth-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--bng-primary), var(--bng-secondary));
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
        }
    </style>

    <div class="bng-auth-wrap">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="bng-auth-bg">
                    <div class="row g-4 align-items-center">
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <div class="bng-auth-icon mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                                <div class="h3 bng-auth-title mb-1">Acesso seguro</div>
                                <div class="bng-auth-subtitle">Entre com a sua conta para gerir pedidos, acompanhar estados e receber notificações.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill text-bg-light">Rápido</span>
                                <span class="badge rounded-pill text-bg-light">Seguro</span>
                                <span class="badge rounded-pill text-bg-light">Organizado</span>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="card bng-auth-card">
                                <div class="card-header">
                                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                        <div class="fw-semibold">{{ __('Login') }}</div>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() }}">
                                            <i class="fa-solid fa-house me-1"></i>
                                            Página Principal
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body p-4 p-lg-5">
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                                <input id="email" type="email" class="form-control bng-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Senha</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                                <input id="password" type="password" class="form-control bng-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                                <button class="btn btn-outline-secondary" type="button" id="toggle_password" aria-label="Mostrar senha">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">Lembrar-me</label>
                                            </div>

                                            @if (Route::has('password.request'))
                                                <a class="small text-decoration-none" href="{{ route('password.request') }}">Esqueceu a senha?</a>
                                            @endif
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 bng-btn">
                                            <i class="fa-solid fa-right-to-bracket me-1"></i>
                                            Entrar
                                        </button>
                                    </form>

                                    <script>
                                        (function () {
                                            var btn = document.getElementById('toggle_password');
                                            var input = document.getElementById('password');
                                            if (!btn || !input) return;

                                            btn.addEventListener('click', function () {
                                                var isPassword = input.getAttribute('type') === 'password';
                                                input.setAttribute('type', isPassword ? 'text' : 'password');
                                                btn.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
                                                var icon = btn.querySelector('i');
                                                if (icon) {
                                                    icon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                                                }
                                            });
                                        })();
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
