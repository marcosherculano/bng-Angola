<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $settings = \App\Models\SystemSetting::query()
            ->whereIn('key', ['site_name', 'primary_color', 'secondary_color', 'support_email', 'support_phone', 'homepage_video_url'])
            ->get()
            ->keyBy('key');

        $siteName = optional($settings->get('site_name'))->value ?: 'BNG-Angola';
        $primaryColor = optional($settings->get('primary_color'))->value ?: '#1A3C6E';
        $secondaryColor = optional($settings->get('secondary_color'))->value ?: '#007A4D';
        $supportEmail = optional($settings->get('support_email'))->value ?: 'suporte@bng.ao';
        $supportPhone = optional($settings->get('support_phone'))->value ?: '+244 000 000 000';
        $videoUrlFallback = optional($settings->get('homepage_video_url'))->value;

        $heroVideoSrc = $videoUrlFallback ?: null;
        if (\Illuminate\Support\Facades\Schema::hasTable('homepage_videos')) {
            $activeVideo = \App\Models\HomepageVideo::query()->where('is_active', true)->orderByDesc('id')->first();
            $heroVideoSrc = $activeVideo ? route('media.homepage_video', $activeVideo) : ($videoUrlFallback ?: null);
        }
    @endphp

    <title>{{ $siteName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />

    <style>
        :root {
            --bng-green: {{ $secondaryColor }};
            --bng-blue: {{ $primaryColor }};
            --bng-red: #C0392B;
            --bng-gray: #F4F6F8;
        }

        body {
            font-family: "IBM Plex Sans", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
            background: #ffffff;
        }

        .bng-navbar {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .bng-brand {
            font-family: "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: var(--bng-blue);
        }

        .hero {
            position: relative;
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            color: #fff;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(26,60,110,0.78), rgba(0,122,77,0.55));
            z-index: 1;
        }

        .hero-media {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            background: radial-gradient(circle at 20% 20%, rgba(0,122,77,0.35), rgba(26,60,110,0.65));
        }

        .hero-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.95;
            filter: saturate(1.1) contrast(1.05) brightness(0.85);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-family: "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: 700;
            line-height: 1.08;
        }

        .btn-bng-primary {
            background: var(--bng-green);
            border-color: var(--bng-green);
        }

        .btn-bng-primary:hover,
        .btn-bng-primary:focus {
            background: #00643f;
            border-color: #00643f;
        }
        
        .btn-bng-outline {
            border-color: rgba(255,255,255,0.85);
            color: #fff;
        }

        .btn-bng-outline:hover,
        .btn-bng-outline:focus {
            background: rgba(255,255,255,0.14);
            border-color: #fff;
            color: #fff;
        }

        .section {
            padding: 72px 0;
        }

        .card-soft {
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border-radius: 16px;
        }

        .icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,122,77,0.10);
            color: var(--bng-green);
        }

        @media (max-width: 576px) {
            .hero {
                min-height: calc(100vh - 64px);
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bng-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <span class="icon-circle" style="background: rgba(26,60,110,0.10); color: var(--bng-blue);">
                <i class="fa-solid fa-prescription-bottle-medical"></i>
            </span>
            <span class="bng-brand">{{ $siteName }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBNG" aria-controls="navbarBNG" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarBNG">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#servicos">Serviços</a></li>
                <li class="nav-item"><a class="nav-link" href="#como-funciona">Como funciona</a></li>
                <li class="nav-item"><a class="nav-link" href="#contactos">Contactos</a></li>
            </ul>

            <div class="d-flex gap-2 ms-lg-3">
                @if (Route::has('login'))
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Entrar</a>
                @endif

                @if (Route::has('register'))
                    <a class="btn btn-sm btn-bng-primary" href="{{ route('register') }}">Criar conta</a>
                @endif
            </div>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="hero-media">
        @if (! empty($heroVideoSrc))
            <video autoplay muted loop playsinline preload="auto" disablepictureinpicture controlslist="nodownload noplaybackrate noremoteplayback">
                <source src="{{ $heroVideoSrc }}" type="video/mp4">
            </video>
        @endif
    </div>

    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-5 hero-title mb-3">Sistema de Pesquisa de Medicamentos Em farmácias De Luanda</h1>
                <p class="lead mb-4" style="max-width: 52ch; opacity: 0.95;">
                    Encontra medicamentos com rapidez, compara preços e localiza farmácias por província e cidade.
                </p>

                <form class="row g-2" method="GET" action="{{ route('cliente.busca') }}">
                    <div class="col-12 col-md-8">
                        <input class="form-control form-control-lg" name="q" type="text" placeholder="Pesquisar medicamento (ex.: Paracetamol)" aria-label="Pesquisar medicamento">
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-lg btn-bng-primary w-100" type="submit">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar
                        </button>
                    </div>
                </form>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a class="btn btn-bng-outline" href="#servicos"><i class="fa-regular fa-circle-play me-2"></i>Saber mais</a>
                    @if (Route::has('register'))
                        <a class="btn btn-bng-primary btn-lg" href="{{ route('register') }}"><i class="fa-solid fa-user-plus me-2"></i>Criar conta</a>
                    @endif
                </div>
            </div>

            <div class="col-lg-5 mt-5 mt-lg-0">
                <div class="card card-soft p-4" style="background: rgba(255,255,255,0.96); color: #0b1220;">
                    <div class="d-flex align-items-start gap-3">
                        <span class="icon-circle"><i class="fa-solid fa-shield-heart"></i></span>
                        <div>
                            <div class="fw-semibold" style="color: var(--bng-blue);">Confiável futuramente</div>
                            <div class="text-muted small">para as 21 províncias desde o primeiro commit.</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex align-items-start gap-3">
                        <span class="icon-circle"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <div class="fw-semibold" style="color: var(--bng-blue);">Localização e rotas</div>
                            <div class="text-muted small">Mapa integrado com geocodificação e cálculo de rota.</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex align-items-start gap-3">
                        <span class="icon-circle"><i class="fa-solid fa-receipt"></i></span>
                        <div>
                            <div class="fw-semibold" style="color: var(--bng-blue);">Mensalidades em Kz</div>
                            <div class="text-muted small">Fluxo de mensalidade com trial e aprovação central.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="servicos" class="section" style="background: var(--bng-gray);">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="h3" style="font-family: Poppins, sans-serif; color: var(--bng-blue);">Serviços</h2>
                <p class="text-muted mb-0">Uma plataforma para clientes, farmácias e gestão administrativa.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="icon-circle mb-3"><i class="fa-solid fa-user"></i></div>
                    <div class="fw-semibold" style="color: var(--bng-blue);">Cliente</div>
                    <div class="text-muted mt-1">Pesquisa rápida, mapa e pedidos com opções de retirada.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="icon-circle mb-3"><i class="fa-solid fa-store"></i></div>
                    <div class="fw-semibold" style="color: var(--bng-blue);">Farmácia</div>
                    <div class="text-muted mt-1">Gestão de medicamentos, pedidos e mensalidades.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="icon-circle mb-3"><i class="fa-solid fa-gauge-high"></i></div>
                    <div class="fw-semibold" style="color: var(--bng-blue);">Admin</div>
                    <div class="text-muted mt-1">Controlo total, aprovações, pagamentos e configurações.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="como-funciona" class="section">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="h3" style="font-family: Poppins, sans-serif; color: var(--bng-blue);">Como funciona</h2>
                <p class="text-muted mb-0">Três passos simples para encontrar o que precisas.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge" style="background: var(--bng-blue);">1</span>
                        <span class="fw-semibold">Pesquisar</span>
                    </div>
                    <div class="text-muted">Digita o nome do medicamento e vê resultados disponíveis.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge" style="background: var(--bng-blue);">2</span>
                        <span class="fw-semibold">Comparar</span>
                    </div>
                    <div class="text-muted">Compara preço, stock e localização por província e cidade.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge" style="background: var(--bng-blue);">3</span>
                        <span class="fw-semibold">Pedir</span>
                    </div>
                    <div class="text-muted">Cria pedido e escolhe retirada presencial ou transporte externo.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contactos" class="section" style="background: var(--bng-gray);">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <h2 class="h4 mb-1" style="font-family: Poppins, sans-serif; color: var(--bng-blue);">Contactos</h2>
                <div class="text-muted">Suporte nacional para Angola.</div>
            </div>
            <div class="col-lg-4">
                <div class="card card-soft p-3">
                    <div class="small text-muted">Email</div>
                    <div class="fw-semibold">{{ $supportEmail }}</div>
                    <div class="mt-2 small text-muted">Telefone</div>
                    <div class="fw-semibold">{{ $supportPhone }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-4" style="background: #0b1220; color: rgba(255,255,255,0.86);">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
        <div class="small">{{ $siteName }} © {{ date('Y') }}</div>
        <div class="small d-flex gap-3">
            <a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.86);">Política de Privacidade</a>
            <a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.86);">Termos de Uso</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
