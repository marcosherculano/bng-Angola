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

        .bng-map {
            border-radius: 14px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            height: 260px;
        }

        @media (max-width: 576px) {
            .bng-auth-bg {
                padding: 18px;
            }

            .bng-map {
                height: 220px;
            }
        }

        @media (min-width: 992px) {
            .bng-map {
                height: 300px;
            }
        }

        [data-bs-theme="dark"] .bng-map {
            border-color: rgba(255,255,255,0.12);
        }

        .bng-section {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 14px;
        }

        [data-bs-theme="dark"] .bng-section {
            border-color: rgba(255,255,255,0.12);
        }

        .bng-section-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <div class="bng-auth-wrap">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="bng-auth-bg">
                    <div class="row g-4 align-items-center">
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <div class="bng-auth-icon mb-3"><i class="fa-solid fa-user-plus"></i></div>
                                <div class="h3 bng-auth-title mb-1">Crie a sua conta</div>
                                <div class="bng-auth-subtitle">Registe-se para pesquisar medicamentos, fazer pedidos e acompanhar tudo num só lugar.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill text-bg-light">Simples</span>
                                <span class="badge rounded-pill text-bg-light">Rápido</span>
                                <span class="badge rounded-pill text-bg-light">Confiável</span>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="card bng-auth-card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-semibold">Registo</div>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() }}">
                                            <i class="fa-solid fa-house me-1"></i>
                                            Página Principal
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="account_type" class="form-label">Tipo de cadastro</label>
                                            <select id="account_type" name="account_type" class="form-select @error('account_type') is-invalid @enderror">
                                                <option value="client" @selected(old('account_type', 'client') === 'client')>Cliente</option>
                                                <option value="pharmacy_normal" @selected(old('account_type') === 'pharmacy_normal')>Farmácia Normal</option>
                                                <option value="pharmacy_matrix" @selected(old('account_type') === 'pharmacy_matrix')>Farmácia Matriz</option>
                                            </select>
                                            @error('account_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="client_fields">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nome</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                                <input id="name" type="text" class="form-control bng-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                                @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label mb-0">Localização</label>
                                                <button class="btn btn-sm btn-outline-secondary" type="button" id="btn_use_my_location_client">
                                                    <i class="fa-solid fa-location-crosshairs me-1"></i>
                                                    Usar minha localização
                                                </button>
                                            </div>
                                            <div class="input-group mt-2">
                                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                <input type="text" class="form-control bng-input" id="client_search" placeholder="Escreva a província, cidade ou bairro (ex: Luanda, Talatona)" autocomplete="off">
                                                <button class="btn btn-outline-primary" type="button" id="btn_client_search">Procurar</button>
                                            </div>
                                            <div class="bng-map mt-2" id="map_client"></div>
                                            <div class="small text-muted mt-2" id="client_address_preview">Selecione um ponto no mapa.</div>
                                            @if ($errors->has('province') || $errors->has('location_lat') || $errors->has('location_lng'))
                                                <div class="text-danger small mt-2">
                                                    Selecione um ponto no mapa para preencher a localização.
                                                </div>
                                            @endif
                                            <input type="hidden" name="province" id="client_province" value="{{ old('province') }}">
                                            <input type="hidden" name="location_lat" id="client_location_lat" value="{{ old('location_lat') }}">
                                            <input type="hidden" name="location_lng" id="client_location_lng" value="{{ old('location_lng') }}">
                                        </div>
                                        </div>
                                        </div>

                                        <div id="pharmacy_fields" style="display: none;">
                                            <div class="alert alert-info border-0 shadow-sm">
                                                <div class="d-flex align-items-start gap-2">
                                                    <div class="mt-1">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">Cadastro de Farmácia</div>
                                                        <div class="small">Preencha os dados e envie o documento do Alvará. Após o envio, a conta fica <span class="fw-semibold">Pendente</span> até a aprovação do admin.</div>
                                                        <div class="small mt-2">
                                                            <div class="fw-semibold">O que o admin valida</div>
                                                            <div>1) NIF e Alvará</div>
                                                            <div>2) Documento do Alvará (PDF/Imagem)</div>
                                                            <div>3) Localização no mapa</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bng-section">
                                                <div class="bng-section-title">Dados da farmácia</div>

                                                <div class="mb-3">
                                                    <label for="business_name" class="form-label">Nome da farmácia</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-solid fa-store"></i></span>
                                                        <input id="business_name" type="text" class="form-control bng-input @error('business_name') is-invalid @enderror" name="business_name" value="{{ old('business_name') }}" autocomplete="organization">
                                                        @error('business_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="responsible_name" class="form-label">Nome do responsável</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-regular fa-id-card"></i></span>
                                                        <input id="responsible_name" type="text" class="form-control bng-input @error('responsible_name') is-invalid @enderror" name="responsible_name" value="{{ old('responsible_name') }}" autocomplete="name">
                                                        @error('responsible_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="nif" class="form-label">NIF</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                                                                <input id="nif" type="text" class="form-control bng-input @error('nif') is-invalid @enderror" name="nif" value="{{ old('nif') }}" autocomplete="off">
                                                                @error('nif')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="alvara" class="form-label">Alvará</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fa-solid fa-file-signature"></i></span>
                                                                <input id="alvara" type="text" class="form-control bng-input @error('alvara') is-invalid @enderror" name="alvara" value="{{ old('alvara') }}" autocomplete="off">
                                                                @error('alvara')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="alvara_document" class="form-label">Documento do Alvará (PDF ou Imagem)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fa-solid fa-file-arrow-up"></i></span>
                                                                <input id="alvara_document" type="file" class="form-control bng-input @error('alvara_document') is-invalid @enderror" name="alvara_document" accept=".pdf,.jpg,.jpeg,.png">
                                                            </div>
                                                            @error('alvara_document')
                                                                <span class="invalid-feedback d-block" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                            <div class="form-text">Obrigatório para farmácias. O admin usará este documento para validar a autenticidade.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bng-section">
                                                <div class="bng-section-title">Endereço</div>

                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="province" class="form-label">Província</label>
                                                            <input id="province" type="text" class="form-control bng-input @error('province') is-invalid @enderror" name="province" value="{{ old('province') }}" autocomplete="address-level1" readonly>
                                                            @error('province')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="city" class="form-label">Município/Cidade</label>
                                                            <input id="city" type="text" class="form-control bng-input @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" autocomplete="address-level2" readonly>
                                                            @error('city')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="neighborhood" class="form-label">Bairro</label>
                                                            <input id="neighborhood" type="text" class="form-control bng-input @error('neighborhood') is-invalid @enderror" name="neighborhood" value="{{ old('neighborhood') }}" autocomplete="address-level3" readonly>
                                                            @error('neighborhood')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-3">
                                                            <label for="street" class="form-label">Rua</label>
                                                            <input id="street" type="text" class="form-control bng-input @error('street') is-invalid @enderror" name="street" value="{{ old('street') }}" autocomplete="street-address" readonly>
                                                            @error('street')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bng-section">
                                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
                                                    <div class="bng-section-title mb-0">Localização no mapa</div>
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" id="btn_use_my_location_pharmacy">
                                                        <i class="fa-solid fa-location-crosshairs me-1"></i>
                                                        Usar minha localização
                                                    </button>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                    <input type="text" class="form-control bng-input" id="pharmacy_search" placeholder="Escreva a província, cidade ou bairro (ex: Benguela, Lobito)" autocomplete="off">
                                                    <button class="btn btn-outline-primary" type="button" id="btn_pharmacy_search">Procurar</button>
                                                </div>
                                                <div class="bng-map mt-2" id="map_pharmacy"></div>
                                                <div class="small text-muted mt-2" id="pharmacy_address_preview">Clique no mapa ou arraste o marcador para ajustar.</div>

                                                <div class="row g-2 mt-1">
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-2">
                                                            <label for="latitude" class="form-label">Latitude</label>
                                                            <input id="latitude" type="text" class="form-control bng-input @error('latitude') is-invalid @enderror" name="latitude" value="{{ old('latitude') }}" autocomplete="off" placeholder="-8.83" readonly>
                                                            @error('latitude')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="mb-2">
                                                            <label for="longitude" class="form-label">Longitude</label>
                                                            <input id="longitude" type="text" class="form-control bng-input @error('longitude') is-invalid @enderror" name="longitude" value="{{ old('longitude') }}" autocomplete="off" placeholder="13.23" readonly>
                                                            @error('longitude')
                                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                                <input id="email" type="email" class="form-control bng-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
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
                                                <input id="password" type="password" class="form-control bng-input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                                <button class="btn btn-outline-secondary" type="button" id="btn_toggle_password" aria-label="Mostrar/ocultar senha">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="form-text">
                                                Regras: mínimo 6 caracteres (pode usar letras, números e símbolos). Não use espaços.
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password-confirm" class="form-label">Confirmar senha</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-shield"></i></span>
                                                <input id="password-confirm" type="password" class="form-control bng-input" name="password_confirmation" required autocomplete="new-password">
                                                <button class="btn btn-outline-secondary" type="button" id="btn_toggle_password_confirm" aria-label="Mostrar/ocultar senha">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 bng-btn">
                                            <i class="fa-solid fa-user-check me-1"></i>
                                            Criar conta
                                        </button>

                                        <div class="text-center mt-3">
                                            <a href="{{ route('login') }}" class="small text-decoration-none">Já tem conta? Entrar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    (function () {
        function toggleRegisterFields() {
            var typeEl = document.getElementById('account_type');
            var clientEl = document.getElementById('client_fields');
            var pharmEl = document.getElementById('pharmacy_fields');
            var alvaraDocEl = document.getElementById('alvara_document');

            if (!typeEl || !clientEl || !pharmEl) return;

            var v = String(typeEl.value || 'client');
            var isPharmacy = typeEl && (typeEl.value === 'pharmacy_normal' || typeEl.value === 'pharmacy_matrix');

            pharmEl.style.display = isPharmacy ? '' : 'none';
            clientEl.style.display = isPharmacy ? 'none' : '';

            var nameInput = document.getElementById('name');
            if (nameInput) {
                nameInput.required = !isPharmacy;
            }

            clientEl.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                if (el && el.id !== 'account_type') {
                    el.disabled = isPharmacy;
                }
            });
            pharmEl.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                el.disabled = !isPharmacy;
            });

            if (alvaraDocEl) {
                alvaraDocEl.required = isPharmacy;
            }
        }

        function setInputValue(id, value) {
            var el = document.getElementById(id);
            if (!el) return;
            el.value = value == null ? '' : String(value);
        }

        function safeNumber(v) {
            if (v == null) return null;
            var s = String(v).trim().replace(',', '.');
            var n = Number(s);
            return Number.isFinite(n) ? n : null;
        }

        function normalizeInitialCoord(v) {
            var n = safeNumber(v);
            if (n == null) return null;
            if (Math.abs(n) < 0.0000001) return null;
            return n;
        }

        async function geocodeSearch(q) {
            var query = String(q || '').trim();
            if (!query) return null;
            var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=ao&q=' + encodeURIComponent(query);
            var res = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error('falha na pesquisa de localização');
            var json = await res.json();
            if (!Array.isArray(json) || json.length === 0) return null;
            return json[0];
        }

        function buildAddressText(addr) {
            if (!addr) return '';
            var parts = [];
            if (addr.road) parts.push(addr.road);
            if (addr.suburb) parts.push(addr.suburb);
            if (addr.city || addr.town || addr.village) parts.push(addr.city || addr.town || addr.village);
            if (addr.state) parts.push(addr.state);
            return parts.join(' · ');
        }

        async function reverseGeocode(lat, lng) {
            var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);
            var res = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error('falha ao obter endereço');
            return await res.json();
        }

        function initLeafletMap(mapId, btnId, opts) {
            var mapEl = document.getElementById(mapId);
            if (!mapEl || !window.L) return null;

            var initialLat = normalizeInitialCoord(opts.initialLat) ?? -11.202692;
            var initialLng = normalizeInitialCoord(opts.initialLng) ?? 17.873887;

            var map = L.map(mapId, { zoomControl: true }).setView([initialLat, initialLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            var busy = false;
            async function applyPosition(lat, lng) {
                if (busy) return;
                busy = true;
                try {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], Math.max(map.getZoom(), 14));
                    var data = await reverseGeocode(lat, lng);
                    opts.onUpdate(lat, lng, data);
                } catch (e) {
                } finally {
                    busy = false;
                }
            }

            marker.on('dragend', function () {
                var p = marker.getLatLng();
                applyPosition(p.lat, p.lng);
            });

            map.on('click', function (e) {
                if (!e || !e.latlng) return;
                applyPosition(e.latlng.lat, e.latlng.lng);
            });

            var btn = document.getElementById(btnId);
            if (btn) {
                btn.addEventListener('click', function () {
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        applyPosition(pos.coords.latitude, pos.coords.longitude);
                    });
                });
            }

            if (opts.tryGeolocationOnLoad && navigator.geolocation && (normalizeInitialCoord(opts.initialLat) == null || normalizeInitialCoord(opts.initialLng) == null)) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    applyPosition(pos.coords.latitude, pos.coords.longitude);
                });
            }

            setTimeout(function () { map.invalidateSize(); }, 250);
            applyPosition(initialLat, initialLng);

            return { map: map, marker: marker, applyPosition: applyPosition };
        }

        document.addEventListener('DOMContentLoaded', function () {
            var typeEl = document.getElementById('account_type');
            if (typeEl) {
                typeEl.addEventListener('change', toggleRegisterFields);
            }
            toggleRegisterFields();

            var btnTogglePassword = document.getElementById('btn_toggle_password');
            var btnTogglePasswordConfirm = document.getElementById('btn_toggle_password_confirm');
            var passwordEl = document.getElementById('password');
            var passwordConfirmEl = document.getElementById('password-confirm');
            function setEyeButtonState(btn, isHidden) {
                if (!btn) return;
                btn.setAttribute('aria-label', isHidden ? 'Mostrar senha' : 'Ocultar senha');
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isHidden ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
                }
            }

            function togglePasswords() {
                if (!passwordEl) return;
                var isHidden = passwordEl.type === 'password';
                passwordEl.type = isHidden ? 'text' : 'password';
                if (passwordConfirmEl) {
                    passwordConfirmEl.type = isHidden ? 'text' : 'password';
                }
                setEyeButtonState(btnTogglePassword, !isHidden);
                setEyeButtonState(btnTogglePasswordConfirm, !isHidden);
            }

            if (btnTogglePassword && passwordEl) btnTogglePassword.addEventListener('click', togglePasswords);
            if (btnTogglePasswordConfirm && passwordEl) btnTogglePasswordConfirm.addEventListener('click', togglePasswords);
            if (btnTogglePassword && passwordEl) setEyeButtonState(btnTogglePassword, true);
            if (btnTogglePasswordConfirm && passwordEl) setEyeButtonState(btnTogglePasswordConfirm, true);

            var clientMap = initLeafletMap('map_client', 'btn_use_my_location_client', {
                initialLat: document.getElementById('client_location_lat')?.value,
                initialLng: document.getElementById('client_location_lng')?.value,
                tryGeolocationOnLoad: true,
                onUpdate: function (lat, lng, data) {
                    setInputValue('client_location_lat', lat.toFixed(8));
                    setInputValue('client_location_lng', lng.toFixed(8));

                    var addr = data && data.address ? data.address : null;
                    var province = addr ? (addr.state || addr.region || '') : '';
                    setInputValue('client_province', province);

                    var preview = document.getElementById('client_address_preview');
                    if (preview) {
                        preview.textContent = buildAddressText(addr) || (data && data.display_name ? data.display_name : 'Localização selecionada.');
                    }
                }
            });

            var pharmacyMap = initLeafletMap('map_pharmacy', 'btn_use_my_location_pharmacy', {
                initialLat: document.getElementById('latitude')?.value,
                initialLng: document.getElementById('longitude')?.value,
                tryGeolocationOnLoad: true,
                onUpdate: function (lat, lng, data) {
                    setInputValue('latitude', lat.toFixed(8));
                    setInputValue('longitude', lng.toFixed(8));

                    var addr = data && data.address ? data.address : null;
                    var province = addr ? (addr.state || addr.region || '') : '';
                    var city = addr ? (addr.city || addr.town || addr.village || '') : '';
                    var neighborhood = addr ? (addr.suburb || addr.neighbourhood || addr.quarter || '') : '';
                    var street = addr ? (addr.road || '') : '';

                    setInputValue('province', province);
                    setInputValue('city', city);
                    setInputValue('neighborhood', neighborhood);
                    setInputValue('street', street);

                    var preview = document.getElementById('pharmacy_address_preview');
                    if (preview) {
                        preview.textContent = buildAddressText(addr) || (data && data.display_name ? data.display_name : 'Localização selecionada.');
                    }
                }
            });

            var btnClientSearch = document.getElementById('btn_client_search');
            var inputClientSearch = document.getElementById('client_search');
            async function doClientSearch() {
                if (!clientMap) return;
                var q = inputClientSearch ? inputClientSearch.value : '';
                try {
                    var r = await geocodeSearch(q);
                    if (r && r.lat && r.lon) {
                        clientMap.applyPosition(Number(r.lat), Number(r.lon));
                    }
                } catch (e) {
                }
            }
            if (btnClientSearch) btnClientSearch.addEventListener('click', doClientSearch);
            if (inputClientSearch) inputClientSearch.addEventListener('keydown', function (e) {
                if (e && e.key === 'Enter') {
                    e.preventDefault();
                    doClientSearch();
                }
            });

            var btnPharmacySearch = document.getElementById('btn_pharmacy_search');
            var inputPharmacySearch = document.getElementById('pharmacy_search');
            async function doPharmacySearch() {
                if (!pharmacyMap) return;
                var q = inputPharmacySearch ? inputPharmacySearch.value : '';
                try {
                    var r = await geocodeSearch(q);
                    if (r && r.lat && r.lon) {
                        pharmacyMap.applyPosition(Number(r.lat), Number(r.lon));
                    }
                } catch (e) {
                }
            }
            if (btnPharmacySearch) btnPharmacySearch.addEventListener('click', doPharmacySearch);
            if (inputPharmacySearch) inputPharmacySearch.addEventListener('keydown', function (e) {
                if (e && e.key === 'Enter') {
                    e.preventDefault();
                    doPharmacySearch();
                }
            });

            if (typeEl) {
                typeEl.addEventListener('change', function () {
                    setTimeout(function () {
                        if (clientMap && clientMap.map) clientMap.map.invalidateSize();
                        if (pharmacyMap && pharmacyMap.map) pharmacyMap.map.invalidateSize();
                    }, 250);
                });
            }
        });
    })();
</script>
@endsection
