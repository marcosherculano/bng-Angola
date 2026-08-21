@php
    $bngSettings = \App\Models\SystemSetting::query()
        ->whereIn('key', ['site_name', 'primary_color', 'secondary_color', 'theme_mode'])
        ->get()
        ->keyBy('key');
    $bngSiteName = optional($bngSettings->get('site_name'))->value ?: config('app.name', 'BNG-Angola');
    $bngPrimary = optional($bngSettings->get('primary_color'))->value ?: '#1A3C6E';
    $bngSecondary = optional($bngSettings->get('secondary_color'))->value ?: '#007A4D';
    $bngThemeMode = optional($bngSettings->get('theme_mode'))->value ?: 'light';
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $bngThemeMode }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $bngSiteName }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />

    @php
        $hasAppCss = is_file(public_path('css/app.css'));
    @endphp

    @if ($hasAppCss)
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --bng-primary: {{ $bngPrimary }};
            --bng-secondary: {{ $bngSecondary }};

            --bs-primary: var(--bng-primary);
            --bs-link-color: var(--bng-primary);
            --bs-link-hover-color: var(--bng-secondary);
        }

        .btn-primary {
            --bs-btn-bg: var(--bng-primary);
            --bs-btn-border-color: var(--bng-primary);
            --bs-btn-hover-bg: var(--bng-secondary);
            --bs-btn-hover-border-color: var(--bng-secondary);
            --bs-btn-active-bg: var(--bng-secondary);
            --bs-btn-active-border-color: var(--bng-secondary);
        }

        .btn-outline-primary {
            --bs-btn-color: var(--bng-primary);
            --bs-btn-border-color: var(--bng-primary);
            --bs-btn-hover-bg: var(--bng-primary);
            --bs-btn-hover-border-color: var(--bng-primary);
            --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: var(--bng-primary);
            --bs-btn-active-border-color: var(--bng-primary);
        }

        .navbar.bng-navbar {
            background: rgba(255,255,255,0.92) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: relative;
            z-index: 3000;
            overflow: visible;
        }

        .navbar.bng-navbar .container,
        .navbar.bng-navbar .navbar-collapse {
            overflow: visible;
        }

        .navbar .navbar-brand {
            font-weight: 700;
            color: var(--bng-primary);
        }

        .navbar .navbar-brand:hover {
            color: var(--bng-secondary);
        }

        .dropdown-menu {
            z-index: 3100;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm bng-navbar">
            <div class="container">
                @php
                    $bngPublicHomeUrl = request()->getSchemeAndHttpHost() . request()->getBaseUrl();
                @endphp

                <a class="navbar-brand" href="{{ $bngPublicHomeUrl }}">
                    {{ $bngSiteName }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Entrar</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">Registo</a>
                                </li>
                            @endif
                        @else
                            @php
                                $bngUnreadNotifications = 0;
                                if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                                    $bngUnreadNotifications = \App\Models\Notification::query()
                                        ->where('user_id', Auth::user()->id)
                                        ->whereNull('read_at')
                                        ->count();
                                }
                            @endphp

                            <li class="nav-item">
                                <a class="nav-link position-relative" href="{{ route('notificacoes.index') }}" title="Notificações" aria-label="Notificações" data-bs-toggle="tooltip" data-bs-title="Notificações">
                                    <i class="fa-regular fa-bell"></i>
                                    @if ($bngUnreadNotifications > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ $bngUnreadNotifications > 99 ? '99+' : $bngUnreadNotifications }}
                                        </span>
                                    @endif
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <div class="dropdown-item-text fw-semibold">
                                        {{ Auth::user()->name }}
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('perfil.edit') }}">Editar perfil</a>

                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Sair
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1">Existem erros no formulário:</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $bngPrevUrl = url()->previous();
                    $bngCurrUrl = url()->current();

                    $bngRouteName = null;
                    try {
                        $bngRouteName = \Illuminate\Support\Facades\Route::currentRouteName();
                    } catch (\Throwable $e) {
                        $bngRouteName = null;
                    }

                    $bngBackUrl = (! empty($bngPrevUrl) && $bngPrevUrl !== $bngCurrUrl) ? $bngPrevUrl : null;
                    if ($bngBackUrl === null && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');

                        if ($role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.painel')) {
                            $bngBackUrl = route('admin.painel');
                        } elseif ($role === 'pharmacy' && \Illuminate\Support\Facades\Route::has('pharmacy.painel')) {
                            $bngBackUrl = route('pharmacy.painel');
                        } elseif ($role === 'client' && \Illuminate\Support\Facades\Route::has('client.painel')) {
                            $bngBackUrl = route('client.painel');
                        } elseif ($role === 'client' && \Illuminate\Support\Facades\Route::has('cliente.pedidos.index')) {
                            $bngBackUrl = route('cliente.pedidos.index');
                        } else {
                            $bngBackUrl = url('/');
                        }
                    }

                    if ($bngRouteName === 'notificacoes.index' && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');
                        if ($role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.painel')) {
                            $bngBackUrl = route('admin.painel');
                        } elseif ($role === 'pharmacy' && \Illuminate\Support\Facades\Route::has('pharmacy.painel')) {
                            $bngBackUrl = route('pharmacy.painel');
                        } elseif ($role === 'client' && \Illuminate\Support\Facades\Route::has('client.painel')) {
                            $bngBackUrl = route('client.painel');
                        } elseif ($role === 'client' && \Illuminate\Support\Facades\Route::has('cliente.pedidos.index')) {
                            $bngBackUrl = route('cliente.pedidos.index');
                        } else {
                            $bngBackUrl = url('/');
                        }
                    }

                    if ($bngRouteName === 'cliente.pedidos.index' && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');
                        if ($role === 'client' && \Illuminate\Support\Facades\Route::has('client.painel')) {
                            $bngBackUrl = route('client.painel');
                        }
                    }

                    if ($bngRouteName === 'cliente.busca' && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');
                        if ($role === 'client' && \Illuminate\Support\Facades\Route::has('client.painel')) {
                            $bngBackUrl = route('client.painel');
                        }
                    }

                    if ($bngRouteName === 'client.painel' && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');
                        if ($role === 'client') {
                            $bngBackUrl = request()->getSchemeAndHttpHost() . request()->getBaseUrl();
                        }
                    }

                    if (! empty($bngBackUrl) && $bngBackUrl === $bngCurrUrl && auth()->check()) {
                        $role = (string) (auth()->user()->role ?? '');
                        if ($role === 'client' && \Illuminate\Support\Facades\Route::has('cliente.busca')) {
                            $bngBackUrl = route('cliente.busca');
                        } else {
                            $bngBackUrl = null;
                        }
                    }
                @endphp

                @if (! empty($bngBackUrl))
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <a href="{{ $bngBackUrl }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Voltar
                        </a>
                    </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.bootstrap) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            document.querySelectorAll('[data-confirm]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    var msg = el.getAttribute('data-confirm');
                    if (msg && !window.confirm(msg)) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
