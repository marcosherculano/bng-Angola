@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="h4 mb-0">Filiais</div>
            <div class="text-muted">{{ $pharmacy->business_name }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('cliente.busca', ['pharmacy_id' => $pharmacy->id]) }}">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="ms-1">Ver medicamentos</span>
            </a>
            <a class="btn btn-outline-secondary" href="{{ route('cliente.busca') }}">Voltar</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Lista de filiais</div>
            <div class="text-muted small">{{ $branches->count() }} activa(s)</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Filial</th>
                            <th>Endereço</th>
                            <th class="text-end" style="width: 180px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $b)
                            @php
                                $addrParts = [$b->street, $b->neighborhood, $b->city, $b->province];
                                $addrParts = array_values(array_filter($addrParts, function ($v) {
                                    return ! empty($v);
                                }));
                                $addr = count($addrParts) ? implode(', ', $addrParts) : '—';
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $b->branch_name }}</td>
                                <td class="text-muted small">{{ $addr }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('cliente.busca', ['pharmacy_id' => $pharmacy->id]) }}" aria-label="Criar pedido" data-bs-toggle="tooltip" data-bs-title="Criar pedido">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>

                                    <button type="button" class="btn btn-sm btn-outline-secondary js-route-btn" aria-label="Calcular rota" data-bs-toggle="tooltip" data-bs-title="Calcular rota"
                                        data-pharmacy-name="{{ $b->branch_name }}"
                                        data-pharmacy-phone="{{ $b->phone }}"
                                        data-pharmacy-email="{{ $b->email }}"
                                        data-pharmacy-province="{{ $b->province }}"
                                        data-pharmacy-city="{{ $b->city }}"
                                        data-pharmacy-neighborhood="{{ $b->neighborhood }}"
                                        data-pharmacy-street="{{ $b->street }}"
                                        data-pharmacy-lat="{{ $b->latitude }}"
                                        data-pharmacy-lng="{{ $b->longitude }}">
                                        <i class="fa-solid fa-route"></i>
                                    </button>

                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('cliente.busca', ['pharmacy_id' => $pharmacy->id]) }}" aria-label="Agendar retirada" data-bs-toggle="tooltip" data-bs-title="Agendar retirada">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </a>

                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('cliente.busca', ['pharmacy_id' => $pharmacy->id]) }}" aria-label="Transporte externo" data-bs-toggle="tooltip" data-bs-title="Transporte externo">
                                        <i class="fa-solid fa-taxi"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted p-3">Sem filiais activas disponíveis.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<div class="modal fade" id="routeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="modal-title h5 mb-0">Rota até a filial</div>
                    <div class="text-muted small" id="routeModalSubtitle">—</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="fw-semibold" id="pharmacyName">—</div>
                                <div class="text-muted small" id="pharmacyAddress">—</div>

                                <hr>

                                <div class="small">
                                    <div class="mb-2"><span class="text-muted">Contacto:</span> <span id="pharmacyContact">—</span></div>
                                    <div class="mb-2"><span class="text-muted">Serviços:</span> <span id="pharmacyServices">Venda de medicamentos, atendimento e recolha</span></div>
                                    <div><span class="text-muted">Horário:</span> <span id="pharmacyHours">08:00 - 18:00</span></div>
                                </div>

                                <hr>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnProfileWalk">
                                        <i class="fa-solid fa-person-walking me-1"></i>
                                        A pé
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnProfileCar">
                                        <i class="fa-solid fa-car-side me-1"></i>
                                        Carro
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm ms-auto" id="btnStartNav" disabled>
                                        <i class="fa-solid fa-location-arrow me-1"></i>
                                        Iniciar navegação
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Distância</span>
                                        <span class="fw-semibold" id="routeDistance">—</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span class="text-muted">Tempo estimado</span>
                                        <span class="fw-semibold" id="routeDuration">—</span>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-3 d-none" role="alert" id="routeWarning"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-body p-2">
                                <div id="routeMap" style="height: 360px; border-radius: .5rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('routeModal');
        var bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;

        var map = null;
        var routeLayer = null;
        var startMarker = null;
        var endMarker = null;

        var currentProfile = 'walking';
        var currentFrom = null;
        var currentTo = null;

        var els = {
            subtitle: document.getElementById('routeModalSubtitle'),
            name: document.getElementById('pharmacyName'),
            address: document.getElementById('pharmacyAddress'),
            contact: document.getElementById('pharmacyContact'),
            distance: document.getElementById('routeDistance'),
            duration: document.getElementById('routeDuration'),
            warning: document.getElementById('routeWarning'),
            btnWalk: document.getElementById('btnProfileWalk'),
            btnCar: document.getElementById('btnProfileCar'),
            btnStartNav: document.getElementById('btnStartNav'),
        };

        function setWarning(text) {
            if (!els.warning) return;
            if (text) {
                els.warning.textContent = text;
                els.warning.classList.remove('d-none');
            } else {
                els.warning.textContent = '';
                els.warning.classList.add('d-none');
            }
        }

        function fmtKm(meters) {
            var km = (meters || 0) / 1000;
            return km.toFixed(2).replace('.', ',') + ' km';
        }

        function fmtMin(seconds) {
            var min = Math.round((seconds || 0) / 60);
            return min + ' min';
        }

        function initMap() {
            if (map) return;

            map = L.map('routeMap', {
                zoomControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            map.setView([-8.83, 13.24], 12);
        }

        async function fetchRoute(profile, from, to) {
            var url = 'https://router.project-osrm.org/route/v1/' + profile + '/' +
                from.lng + ',' + from.lat + ';' + to.lng + ',' + to.lat +
                '?overview=full&geometries=geojson&alternatives=false&steps=false';

            var controller = new AbortController();
            var timeoutId = setTimeout(function () {
                controller.abort();
            }, 12000);

            var resp;
            try {
                resp = await fetch(url, { signal: controller.signal });
            } catch (e) {
                if (e && (e.name === 'AbortError' || e.code === 20)) {
                    throw new Error('Tempo esgotado ao obter rota. Verifique a sua internet e tente novamente.');
                }
                throw e;
            } finally {
                clearTimeout(timeoutId);
            }
            if (!resp.ok) {
                throw new Error('Falha ao obter rota.');
            }

            var data = await resp.json();
            if (!data || data.code !== 'Ok' || !data.routes || !data.routes.length) {
                throw new Error('Rota indisponível.');
            }

            return data.routes[0];
        }

        async function renderRoute() {
            if (!currentFrom || !currentTo) return;
            if (!els.btnStartNav) return;

            els.distance.textContent = 'A calcular...';
            els.duration.textContent = 'A calcular...';
            els.btnStartNav.disabled = true;
            setWarning('');

            try {
                var route = await fetchRoute(currentProfile, currentFrom, currentTo);

                els.distance.textContent = fmtKm(route.distance);
                els.duration.textContent = fmtMin(route.duration);

                if (routeLayer) {
                    routeLayer.remove();
                }

                var coords = route.geometry.coordinates.map(function (c) {
                    return [c[1], c[0]];
                });

                routeLayer = L.polyline(coords, { color: '#1A3C6E', weight: 5, opacity: 0.9 }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [20, 20] });

                if (startMarker) startMarker.remove();
                if (endMarker) endMarker.remove();
                startMarker = L.marker([currentFrom.lat, currentFrom.lng]).addTo(map);
                endMarker = L.marker([currentTo.lat, currentTo.lng]).addTo(map);

                els.btnStartNav.disabled = false;
            } catch (e) {
                els.distance.textContent = '—';
                els.duration.textContent = '—';
                setWarning(e && e.message ? e.message : 'Não foi possível calcular a rota.');
                // Mesmo sem rota desenhada, permite abrir navegação externa se houver destino
                els.btnStartNav.disabled = !currentTo;
            }
        }

        function updateProfileButtons() {
            if (!els.btnWalk || !els.btnCar) return;
            els.btnWalk.classList.toggle('btn-primary', currentProfile === 'walking');
            els.btnWalk.classList.toggle('btn-outline-primary', currentProfile !== 'walking');
            els.btnCar.classList.toggle('btn-primary', currentProfile === 'driving');
            els.btnCar.classList.toggle('btn-outline-primary', currentProfile !== 'driving');
        }

        function setPharmacyInfo(ph) {
            var addrParts = [ph.street, ph.neighborhood, ph.city, ph.province].filter(Boolean);
            var addr = addrParts.join(', ');
            var contactParts = [ph.phone, ph.email].filter(Boolean);

            els.subtitle.textContent = addr || '—';
            els.name.textContent = ph.name || '—';
            els.address.textContent = addr || '—';
            els.contact.textContent = contactParts.length ? contactParts.join(' | ') : '—';
        }

        function getGeolocation() {
            return new Promise(function (resolve, reject) {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocalização não suportada neste navegador.'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    },
                    function () {
                        reject(new Error('Não foi possível obter a sua localização. Permita o acesso à localização.'));
                    },
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                );
            });
        }

        function openNavigation(profile) {
            if (!currentTo) return;
            var travelmode = profile === 'walking' ? 'walking' : 'driving';
            var url = 'https://www.google.com/maps/dir/?api=1' +
                '&destination=' + currentTo.lat + ',' + currentTo.lng +
                '&travelmode=' + travelmode;
            if (currentFrom) {
                url += '&origin=' + currentFrom.lat + ',' + currentFrom.lng;
            }
            // Abre preferencialmente o Google Maps (no telemóvel abre o app)
            window.open(url, '_blank');
        }

        function parseCoord(value) {
            if (value === null || value === undefined) return NaN;
            var v = String(value).trim();
            if (v === '') return NaN;
            v = v.replace(',', '.');
            return parseFloat(v);
        }

        document.querySelectorAll('.js-route-btn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                var lat = parseCoord(btn.getAttribute('data-pharmacy-lat'));
                var lng = parseCoord(btn.getAttribute('data-pharmacy-lng'));

                var ph = {
                    name: btn.getAttribute('data-pharmacy-name') || '',
                    phone: btn.getAttribute('data-pharmacy-phone') || '',
                    email: btn.getAttribute('data-pharmacy-email') || '',
                    province: btn.getAttribute('data-pharmacy-province') || '',
                    city: btn.getAttribute('data-pharmacy-city') || '',
                    neighborhood: btn.getAttribute('data-pharmacy-neighborhood') || '',
                    street: btn.getAttribute('data-pharmacy-street') || '',
                };

                setPharmacyInfo(ph);
                initMap();

                currentTo = (isFinite(lat) && isFinite(lng)) ? { lat: lat, lng: lng } : null;
                currentFrom = null;
                currentProfile = 'walking';
                updateProfileButtons();
                els.distance.textContent = '—';
                els.duration.textContent = '—';
                setWarning('');
                els.btnStartNav.disabled = !currentTo;

                if (!currentTo) {
                    setWarning('Filial sem coordenadas (latitude/longitude).');
                }

                bsModal.show();

                setTimeout(function () {
                    if (map) {
                        map.invalidateSize();
                    }
                }, 200);

                try {
                    currentFrom = await getGeolocation();
                } catch (e) {
                    setWarning(e && e.message ? e.message : 'Falha ao obter localização.');
                }

                if (currentFrom && currentTo) {
                    renderRoute();
                }
            });
        });

        if (els.btnWalk) {
            els.btnWalk.addEventListener('click', function () {
                currentProfile = 'walking';
                updateProfileButtons();
                renderRoute();
            });
        }
        if (els.btnCar) {
            els.btnCar.addEventListener('click', function () {
                currentProfile = 'driving';
                updateProfileButtons();
                renderRoute();
            });
        }
        if (els.btnStartNav) {
            els.btnStartNav.addEventListener('click', function () {
                openNavigation(currentProfile);
            });
        }
    });
</script>
@endsection
