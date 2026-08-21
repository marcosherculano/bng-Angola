@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-2">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Admin</div>
                <div class="card-body p-0">
                    @include('admin.partials.nav')
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Configurações</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white fw-semibold">Identidade e tema</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.configuracoes.update') }}" class="row g-3">
                                        @csrf
                                        @method('PUT')

                                        <div class="col-md-6">
                                            <label class="form-label">Nome da plataforma</label>
                                            <input class="form-control" name="site_name" value="{{ optional($settings->get('site_name'))->value }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Modo do tema</label>
                                            <select class="form-select" name="theme_mode">
                                                @php
                                                    $currentThemeMode = optional($settings->get('theme_mode'))->value ?: 'light';
                                                @endphp
                                                <option value="light" @selected($currentThemeMode==='light')>Claro</option>
                                                <option value="dark" @selected($currentThemeMode==='dark')>Noturno</option>
                                            </select>
                                            <div class="form-text">Aplica-se a toda a plataforma (admin, cliente e farmácia).</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Cor primária</label>
                                            <div class="input-group">
                                                <input class="form-control form-control-color" type="color" name="primary_color_picker" value="{{ optional($settings->get('primary_color'))->value ?: '#1A3C6E' }}" title="Escolher cor primária">
                                                <input class="form-control" name="primary_color" value="{{ optional($settings->get('primary_color'))->value }}" placeholder="#1A3C6E" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Cor secundária</label>
                                            <div class="input-group">
                                                <input class="form-control form-control-color" type="color" name="secondary_color_picker" value="{{ optional($settings->get('secondary_color'))->value ?: '#007A4D' }}" title="Escolher cor secundária">
                                                <input class="form-control" name="secondary_color" value="{{ optional($settings->get('secondary_color'))->value }}" placeholder="#007A4D" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="card border-0" style="background: rgba(0,0,0,0.02);">
                                                <div class="card-body">
                                                    <div class="fw-semibold mb-2">Pré-visualização do tema</div>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                                        <button type="button" class="btn btn-primary btn-sm">Botão primário</button>
                                                        <button type="button" class="btn btn-outline-primary btn-sm">Outline</button>
                                                        <a href="#" class="small">Link de exemplo</a>
                                                        <span class="badge" style="background: var(--bng-primary);">Destaque</span>
                                                        <span class="badge" style="background: var(--bng-secondary);">Suporte</span>
                                                    </div>
                                                    <div class="p-3 rounded" style="background: #fff; border: 1px solid rgba(0,0,0,0.06);">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="fw-semibold" style="color: var(--bng-primary);">{{ optional($settings->get('site_name'))->value ?: 'BNG-Angola' }}</div>
                                                            <div class="d-flex gap-2">
                                                                <span class="badge" style="background: rgba(0,0,0,0.06); color: #111;">Menu</span>
                                                                <span class="badge" style="background: rgba(0,0,0,0.06); color: #111;">Sair</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Email de suporte</label>
                                            <input class="form-control" name="support_email" value="{{ optional($settings->get('support_email'))->value }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Telefone de suporte</label>
                                            <input class="form-control" name="support_phone" value="{{ optional($settings->get('support_phone'))->value }}">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">URL do vídeo da homepage (opcional)</label>
                                            <input class="form-control" name="homepage_video_url" value="{{ optional($settings->get('homepage_video_url'))->value }}" placeholder="https://...">
                                            <div class="form-text">Se existir um vídeo activo carregado no sistema, ele tem prioridade sobre o URL.</div>
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white fw-semibold">Gestão de vídeos</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.configuracoes.videos.upload') }}" enctype="multipart/form-data" class="row g-3">
                                        @csrf
                                        <div class="col-md-6">
                                            <label class="form-label">Título (opcional)</label>
                                            <input class="form-control" name="title" placeholder="Ex.: Vídeo principal">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ficheiro MP4</label>
                                            <input class="form-control" type="file" name="video" accept="video/mp4" required>
                                            <div class="form-text">Máx. 200MB</div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-outline-primary">
                                                <i class="fa-solid fa-upload"></i>
                                                <span class="ms-1">Carregar vídeo</span>
                                            </button>
                                        </div>
                                    </form>

                                    <hr>

                                    @php
                                        $activeVideo = $videos->firstWhere('is_active', true);
                                    @endphp

                                    @if ($activeVideo)
                                        <div class="mb-3">
                                            <div class="fw-semibold mb-2">Vídeo activo</div>
                                            <div class="ratio ratio-16x9 rounded overflow-hidden" style="background: #0b1220;">
                                                <video autoplay muted loop playsinline controls>
                                                    <source src="{{ route('media.homepage_video', $activeVideo) }}" type="video/mp4">
                                                </video>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="fw-semibold mb-2">Vídeos carregados</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Tamanho</th>
                                                    <th>Estado</th>
                                                    <th class="text-end">Acções</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($videos as $v)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $v->title ?: '—' }}</div>
                                                            <div class="small text-muted">ID {{ $v->id }}</div>
                                                        </td>
                                                        <td class="small text-muted">{{ $v->size_bytes ? number_format($v->size_bytes / 1024 / 1024, 1, ',', '.') . ' MB' : '—' }}</td>
                                                        <td>
                                                            @if ($v->is_active)
                                                                <span class="badge bg-success">Activo</span>
                                                            @else
                                                                <span class="badge bg-secondary">Inactivo</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                                                @if (! $v->is_active)
                                                                    <form method="POST" action="{{ route('admin.configuracoes.videos.activate', $v) }}">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <button class="btn btn-sm btn-primary" type="submit" data-confirm="Activar este vídeo como fundo principal?">
                                                                            <i class="fa-solid fa-bolt"></i>
                                                                            <span class="ms-1">Activar</span>
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                                <form method="POST" action="{{ route('admin.configuracoes.videos.delete', $v) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Eliminar este vídeo? Esta acção não pode ser desfeita.">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                        <span class="ms-1">Eliminar</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-muted">Sem vídeos carregados.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var primary = document.querySelector('input[name="primary_color"]');
        var secondary = document.querySelector('input[name="secondary_color"]');
        var primaryPicker = document.querySelector('input[name="primary_color_picker"]');
        var secondaryPicker = document.querySelector('input[name="secondary_color_picker"]');

        function normalizeHex(v) {
            if (!v) return '';
            v = String(v).trim();
            if (v && v[0] !== '#') v = '#' + v;
            return v;
        }

        function applyThemePreview() {
            var p = normalizeHex(primary ? primary.value : (primaryPicker ? primaryPicker.value : ''));
            var s = normalizeHex(secondary ? secondary.value : (secondaryPicker ? secondaryPicker.value : ''));

            if (p) {
                document.documentElement.style.setProperty('--bng-primary', p);
            }
            if (s) {
                document.documentElement.style.setProperty('--bng-secondary', s);
            }
        }

        function syncFromPickers() {
            if (primaryPicker && primary) {
                primary.value = primaryPicker.value;
            }
            if (secondaryPicker && secondary) {
                secondary.value = secondaryPicker.value;
            }
            applyThemePreview();
        }

        function syncFromText() {
            var p = normalizeHex(primary ? primary.value : '');
            var s = normalizeHex(secondary ? secondary.value : '');

            if (p && primaryPicker) {
                primaryPicker.value = p;
            }
            if (s && secondaryPicker) {
                secondaryPicker.value = s;
            }
            applyThemePreview();
        }

        if (primary) {
            primary.addEventListener('input', syncFromText);
        }
        if (secondary) {
            secondary.addEventListener('input', syncFromText);
        }
        if (primaryPicker) {
            primaryPicker.addEventListener('input', syncFromPickers);
        }
        if (secondaryPicker) {
            secondaryPicker.addEventListener('input', syncFromPickers);
        }

        syncFromText();
    });
</script>
@endsection
