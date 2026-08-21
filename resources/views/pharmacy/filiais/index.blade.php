@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        .bng-branch-modal .modal-header,
        .bng-branch-modal .modal-body,
        .bng-branch-modal .modal-footer {
            padding: .75rem;
        }

        .bng-branch-modal .modal-title,
        .bng-branch-modal .h5 {
            margin-bottom: 0;
        }

        .bng-branch-modal .form-label {
            margin-bottom: .25rem;
            font-size: .85rem;
        }

        .bng-branch-modal .accordion-button {
            padding: .5rem .75rem;
            font-size: .9rem;
        }

        .bng-branch-modal .accordion-body {
            padding: .75rem;
        }

        .bng-modal-footer-sticky {
            position: sticky;
            bottom: 0;
            background: var(--bs-body-bg);
            z-index: 2000;
            border-top: 1px solid rgba(0,0,0,.1);
        }

        .bng-branch-map {
            height: 300px;
            border-radius: .5rem;
            border: 1px solid rgba(0,0,0,.08);
        }

        @media (max-width: 576px) {
            .bng-branch-map { height: 240px; }
        }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Filiais</div>
            <div class="text-muted">Gestão de filiais da matriz</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalAddBranch">
                <i class="fa-solid fa-plus"></i>
                <span class="ms-1">Adicionar filial</span>
            </button>
            <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar</a>
        </div>
    </div>

    <div class="modal fade bng-branch-modal" id="modalAddBranch" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-sm-down modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('pharmacy.filiais.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <div class="h5 mb-0">Adicionar filial</div>
                            <div class="text-muted small">Cria a filial e o respetivo utilizador (login)</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="accordion" id="accAddBranchMain">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accAddBranchBranch" aria-expanded="true">
                                        1) Dados da filial
                                    </button>
                                </h2>
                                <div id="accAddBranchBranch" class="accordion-collapse collapse show" data-bs-parent="#accAddBranchMain">
                                    <div class="accordion-body">
                                        <div class="row g-2">
                                            <div class="col-md-8">
                                                <label class="form-label">Nome da filial</label>
                                                <input class="form-control form-control-sm" name="branch_name" value="{{ old('branch_name') }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Província</label>
                                                <input class="form-control form-control-sm" name="province" value="{{ old('province') }}" required data-bng-autofill>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Horário de funcionamento</label>
                                                <input class="form-control form-control-sm" name="opening_hours" value="{{ old('opening_hours') }}" list="bngOpeningHoursOptions" placeholder="Ex: Seg-Sex 08:00-18:00; Sáb 08:00-13:00" required>
                                            </div>
                                        </div>

                                        <datalist id="bngOpeningHoursOptions">
                                            <option value="Seg-Sex 08:00-18:00; Sáb 08:00-13:00"></option>
                                            <option value="Seg-Sáb 08:00-18:00"></option>
                                            <option value="Todos os dias 08:00-20:00"></option>
                                            <option value="24 horas"></option>
                                            <option value="Seg-Sex 08:00-17:00"></option>
                                            <option value="Seg-Sex 09:00-18:00; Sáb 09:00-13:00"></option>
                                        </datalist>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accAddBranchLogin" aria-expanded="false">
                                        2) Login da filial
                                    </button>
                                </h2>
                                <div id="accAddBranchLogin" class="accordion-collapse collapse" data-bs-parent="#accAddBranchMain">
                                    <div class="accordion-body">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Nome do responsável</label>
                                                <input class="form-control form-control-sm" name="user_name" value="{{ old('user_name') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Telefone</label>
                                                <input class="form-control form-control-sm" name="user_phone" value="{{ old('user_phone') }}">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label">Email (login)</label>
                                                <input class="form-control form-control-sm" name="user_email" value="{{ old('user_email') }}" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Senha</label>
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control" type="password" name="user_password" autocomplete="new-password" required data-bng-password>
                                                    <button class="btn btn-outline-secondary" type="button" data-bng-toggle-password>
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Mínimo 8 caracteres.</div>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Confirmar senha</label>
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control" type="password" name="user_password_confirmation" autocomplete="new-password" required data-bng-password>
                                                    <button class="btn btn-outline-secondary" type="button" data-bng-toggle-password>
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accAddBranchLocation" aria-expanded="false">
                                        3) Localização (mapa)
                                    </button>
                                </h2>
                                <div id="accAddBranchLocation" class="accordion-collapse collapse" data-bs-parent="#accAddBranchMain" data-branch-map-collapse>
                                    <div class="accordion-body">
                                        <div class="alert alert-light border small mb-2 d-none" data-leaflet-warning>
                                            O mapa não carregou. Verifique a internet/Firewall e faça refresh (Ctrl+F5). Se persistir, limpe cache de views.
                                        </div>

                                        <input type="hidden" name="latitude" value="{{ old('latitude', '-8.838333') }}">
                                        <input type="hidden" name="longitude" value="{{ old('longitude', '13.234444') }}">

                                        <div class="row g-2 mb-2">
                                            <div class="col-md-4">
                                                <label class="form-label">Município</label>
                                                <input class="form-control form-control-sm" name="city" value="{{ old('city') }}" data-branch-city data-bng-autofill>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Bairro</label>
                                                <input class="form-control form-control-sm" name="neighborhood" value="{{ old('neighborhood') }}" data-branch-neighborhood data-bng-autofill>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Rua</label>
                                                <input class="form-control form-control-sm" name="street" value="{{ old('street') }}" data-branch-street data-bng-autofill>
                                            </div>
                                        </div>

                                        <div class="text-muted small mb-2">Arraste o ponteiro para ajustar. A latitude/longitude serão atualizadas automaticamente.</div>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input class="form-control" type="text" placeholder="Pesquisar província/cidade/bairro..." data-branch-map-search>
                                            <button class="btn btn-outline-primary" type="button" data-branch-map-search-btn>Buscar</button>
                                        </div>
                                        <div id="branchMapAdd" class="bng-branch-map" data-branch-map data-lat-input="latitude" data-lng-input="longitude"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accAddBranchOptionalBody" aria-expanded="false">
                                        4) Detalhes (opcional)
                                    </button>
                                </h2>
                                <div id="accAddBranchOptionalBody" class="accordion-collapse collapse" data-bs-parent="#accAddBranchMain">
                                    <div class="accordion-body">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Telefone (filial)</label>
                                                <input class="form-control form-control-sm" name="phone" value="{{ old('phone') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email (filial)</label>
                                                <input class="form-control form-control-sm" name="email" value="{{ old('email') }}">
                                            </div>

                                            <div class="col-12"><hr class="my-2"></div>

                                            <div class="col-md-6">
                                                <label class="form-label">Alvará (PDF)</label>
                                                <input class="form-control form-control-sm" type="file" name="branch_document" accept="application/pdf" required>
                                                <div class="form-text">Obrigatório. PDF até 5MB.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Imagem (opcional)</label>
                                                <input class="form-control form-control-sm" type="file" name="branch_image" accept="image/png,image/jpeg">
                                                <div class="form-text">JPG/PNG até 5MB.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bng-modal-footer-sticky d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-sm btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span class="ms-1">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Lista de filiais</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Província</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th class="text-end">Acções</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $b)
                            <tr>
                                <td class="fw-semibold">{{ $b->branch_name }}</td>
                                <td>{{ $b->province }}</td>
                                <td>
                                    <div class="small text-muted">{{ $b->phone ?: '—' }}</div>
                                    <div class="small text-muted">{{ $b->email ?: '—' }}</div>
                                    <div class="small text-muted">Login: {{ optional($b->user)->email ?: '—' }}</div>
                                </td>
                                <td>
                                    @php
                                        $bStatus = (string) ($b->status ?? 'pending');
                                    @endphp
                                    @if ($bStatus === 'pending')
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    @elseif ($bStatus === 'approved' && $b->is_active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Desactivada</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2 justify-content-end flex-wrap">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalEditBranch{{ $b->id }}">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                            <span class="ms-1">Editar</span>
                                        </button>
                                        @if ($bStatus === 'approved')
                                            <form method="POST" action="{{ route('pharmacy.filiais.toggleActive', $b) }}">
                                                @csrf
                                                @method('PUT')
                                                @if ($b->is_active)
                                                    <button class="btn btn-sm btn-outline-primary" type="submit">
                                                        <i class="fa-solid fa-pause"></i>
                                                        <span class="ms-1">Desactivar</span>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-success" type="submit">
                                                        <i class="fa-solid fa-play"></i>
                                                        <span class="ms-1">Activar</span>
                                                    </button>
                                                @endif
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                                                <i class="fa-solid fa-clock"></i>
                                                <span class="ms-1">Aguardando admin</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade bng-branch-modal" id="modalEditBranch{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-fullscreen-sm-down modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('pharmacy.filiais.update', $b) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <div>
                                                    <div class="h5 mb-0">Editar filial</div>
                                                    <div class="text-muted small">Actualizar dados da filial e do utilizador</div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="accordion" id="accEditBranchMain{{ $b->id }}">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accEditBranchBranch{{ $b->id }}" aria-expanded="true">
                                                                1) Dados da filial
                                                            </button>
                                                        </h2>
                                                        <div id="accEditBranchBranch{{ $b->id }}" class="accordion-collapse collapse show" data-bs-parent="#accEditBranchMain{{ $b->id }}">
                                                            <div class="accordion-body">
                                                                <div class="row g-2">
                                                                    <div class="col-md-8">
                                                                        <label class="form-label">Nome da filial</label>
                                                                        <input class="form-control form-control-sm" name="branch_name" value="{{ old('branch_name', $b->branch_name) }}" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Província</label>
                                                                        <input class="form-control form-control-sm" name="province" value="{{ old('province', $b->province) }}" required data-bng-autofill>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label">Horário de funcionamento</label>
                                                                        <input class="form-control form-control-sm" name="opening_hours" value="{{ old('opening_hours', $b->opening_hours) }}" list="bngOpeningHoursOptions" placeholder="Ex: Seg-Sex 08:00-18:00; Sáb 08:00-13:00" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accEditBranchLogin{{ $b->id }}" aria-expanded="false">
                                                                2) Login da filial
                                                            </button>
                                                        </h2>
                                                        <div id="accEditBranchLogin{{ $b->id }}" class="accordion-collapse collapse" data-bs-parent="#accEditBranchMain{{ $b->id }}">
                                                            <div class="accordion-body">
                                                                <div class="row g-2">
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Nome do responsável</label>
                                                                        <input class="form-control form-control-sm" name="user_name" value="{{ old('user_name', optional($b->user)->name) }}" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Telefone</label>
                                                                        <input class="form-control form-control-sm" name="user_phone" value="{{ old('user_phone', optional($b->user)->phone) }}">
                                                                    </div>
                                                                    <div class="col-md-7">
                                                                        <label class="form-label">Email (login)</label>
                                                                        <input class="form-control form-control-sm" name="user_email" value="{{ old('user_email', optional($b->user)->email) }}" required>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label class="form-label">Nova senha (opcional)</label>
                                                                        <div class="input-group input-group-sm">
                                                                            <input class="form-control" type="password" name="user_password" autocomplete="new-password" data-bng-password>
                                                                            <button class="btn btn-outline-secondary" type="button" data-bng-toggle-password>
                                                                                <i class="fa-regular fa-eye"></i>
                                                                            </button>
                                                                        </div>
                                                                        <div class="form-text">Se preencher, mínimo 8 caracteres.</div>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label class="form-label">Confirmar nova senha</label>
                                                                        <div class="input-group input-group-sm">
                                                                            <input class="form-control" type="password" name="user_password_confirmation" autocomplete="new-password" data-bng-password>
                                                                            <button class="btn btn-outline-secondary" type="button" data-bng-toggle-password>
                                                                                <i class="fa-regular fa-eye"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accEditBranchLocation{{ $b->id }}" aria-expanded="false">
                                                                3) Localização (mapa)
                                                            </button>
                                                        </h2>
                                                        <div id="accEditBranchLocation{{ $b->id }}" class="accordion-collapse collapse" data-bs-parent="#accEditBranchMain{{ $b->id }}" data-branch-map-collapse>
                                                            <div class="accordion-body">
                                                                <div class="alert alert-light border small mb-2 d-none" data-leaflet-warning>
                                                                    O mapa não carregou. Verifique a internet/Firewall e faça refresh (Ctrl+F5). Se persistir, limpe cache de views.
                                                                </div>

                                                                <input type="hidden" name="latitude" value="{{ old('latitude', $b->latitude) }}">
                                                                <input type="hidden" name="longitude" value="{{ old('longitude', $b->longitude) }}">

                                                                <div class="row g-2 mb-2">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Município</label>
                                                                        <input class="form-control form-control-sm" name="city" value="{{ old('city', $b->city) }}" data-branch-city data-bng-autofill>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Bairro</label>
                                                                        <input class="form-control form-control-sm" name="neighborhood" value="{{ old('neighborhood', $b->neighborhood) }}" data-branch-neighborhood data-bng-autofill>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Rua</label>
                                                                        <input class="form-control form-control-sm" name="street" value="{{ old('street', $b->street) }}" data-branch-street data-bng-autofill>
                                                                    </div>
                                                                </div>

                                                                <div class="text-muted small mb-2">Arraste o ponteiro para ajustar. A latitude/longitude serão atualizadas automaticamente.</div>
                                                                <div class="input-group input-group-sm mb-2">
                                                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                                    <input class="form-control" type="text" placeholder="Pesquisar província/cidade/bairro..." data-branch-map-search>
                                                                    <button class="btn btn-outline-primary" type="button" data-branch-map-search-btn>Buscar</button>
                                                                </div>
                                                                <div id="branchMapEdit{{ $b->id }}" class="bng-branch-map" data-branch-map data-lat-input="latitude" data-lng-input="longitude"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accEditBranchOptionalBody{{ $b->id }}" aria-expanded="false">
                                                                4) Detalhes (opcional)
                                                            </button>
                                                        </h2>
                                                        <div id="accEditBranchOptionalBody{{ $b->id }}" class="accordion-collapse collapse" data-bs-parent="#accEditBranchMain{{ $b->id }}">
                                                            <div class="accordion-body">
                                                                <div class="row g-2">
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Telefone (filial)</label>
                                                                        <input class="form-control form-control-sm" name="phone" value="{{ old('phone', $b->phone) }}">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Email (filial)</label>
                                                                        <input class="form-control form-control-sm" name="email" value="{{ old('email', $b->email) }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bng-modal-footer-sticky d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button class="btn btn-sm btn-primary" type="submit">
                                                    <i class="fa-solid fa-floppy-disk"></i>
                                                    <span class="ms-1">Guardar</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="5" class="text-muted">Sem filiais cadastradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $branches->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            window.bngLeafletLoaded = typeof window.L !== 'undefined';
            if (!window.bngLeafletLoaded) {
                console.warn('Leaflet não carregou. Verifique internet/caches e se a stack de scripts está sendo renderizada no layout.');
            }

            function ensureLeafletFallbackLoaded() {
                if (typeof window.L !== 'undefined') return;
                if (window.__bngLeafletFallbackLoading) return;
                window.__bngLeafletFallbackLoading = true;

                var css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css';
                document.head.appendChild(css);

                var s = document.createElement('script');
                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js';
                s.onload = function () {
                    window.bngLeafletLoaded = typeof window.L !== 'undefined';
                    if (!window.bngLeafletLoaded) return;

                    document.querySelectorAll('.modal.show').forEach(function (m) {
                        try { createMapInModal(m); } catch (e) {}
                    });
                };
                document.head.appendChild(s);
            }
            function toNumber(v, fallback) {
                var n = parseFloat(v);
                return Number.isFinite(n) ? n : fallback;
            }

            function createMapInModal(modalEl) {
                var warnings = modalEl.querySelectorAll('[data-leaflet-warning]');
                warnings.forEach(function (w) {
                    w.classList.add('d-none');
                });

                if (typeof window.L === 'undefined') {
                    warnings.forEach(function (w) {
                        w.classList.remove('d-none');
                    });

                    ensureLeafletFallbackLoaded();
                    return;
                }

                var mapContainers = modalEl.querySelectorAll('[data-branch-map]');
                mapContainers.forEach(function (mapEl) {
                    if (mapEl.offsetWidth === 0 || mapEl.offsetHeight === 0) {
                        return;
                    }

                    if (mapEl._bngMap) {
                        mapEl._bngMap.invalidateSize();
                        return;
                    }

                    var latInput = modalEl.querySelector('input[name="' + mapEl.getAttribute('data-lat-input') + '"]');
                    var lngInput = modalEl.querySelector('input[name="' + mapEl.getAttribute('data-lng-input') + '"]');

                    var lat = toNumber(latInput ? latInput.value : null, -8.838333); // Luanda
                    var lng = toNumber(lngInput ? lngInput.value : null, 13.234444);

                    var map = L.map(mapEl, { scrollWheelZoom: true }).setView([lat, lng], 12);
                    mapEl._bngMap = map;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

                    function syncInputs(ll) {
                        if (latInput) latInput.value = ll.lat.toFixed(8);
                        if (lngInput) lngInput.value = ll.lng.toFixed(8);

                        var latDisplays = modalEl.querySelectorAll('[data-lat-display]');
                        latDisplays.forEach(function (el) { el.value = ll.lat.toFixed(8); });

                        var lngDisplays = modalEl.querySelectorAll('[data-lng-display]');
                        lngDisplays.forEach(function (el) { el.value = ll.lng.toFixed(8); });
                    }

                    var reverseGeocodeTimer = null;
                    async function reverseGeocodeFill(ll) {
                        if (reverseGeocodeTimer) {
                            clearTimeout(reverseGeocodeTimer);
                        }

                        reverseGeocodeTimer = setTimeout(async function () {
                            try {
                                var url = 'https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&addressdetails=1&lat=' + encodeURIComponent(ll.lat) + '&lon=' + encodeURIComponent(ll.lng);
                                var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                if (!res.ok) return;
                                var data = await res.json();
                                if (!data || !data.address) return;

                                var address = data.address || {};
                                var province = address.state || address.region || '';
                                var city = address.city || address.town || address.county || address.municipality || '';
                                var neighborhood = address.suburb || address.neighbourhood || address.quarter || '';
                                var street = address.road || address.pedestrian || address.path || address.residential || '';

                                var provinceInput = modalEl.querySelector('input[name="province"]');
                                if (province && provinceInput && (provinceInput.hasAttribute('data-bng-autofill') || !provinceInput.value || provinceInput.value.trim().length < 2)) {
                                    provinceInput.value = province;
                                }

                                var cityInput = modalEl.querySelector('input[name="city"]');
                                if (city && cityInput && (cityInput.hasAttribute('data-bng-autofill') || !cityInput.value || cityInput.value.trim().length < 2)) {
                                    cityInput.value = city;
                                }

                                var neighborhoodInput = modalEl.querySelector('input[name="neighborhood"]');
                                if (neighborhood && neighborhoodInput && (neighborhoodInput.hasAttribute('data-bng-autofill') || !neighborhoodInput.value || neighborhoodInput.value.trim().length < 2)) {
                                    neighborhoodInput.value = neighborhood;
                                }

                                var streetInput = modalEl.querySelector('input[name="street"]');
                                if (street && streetInput && (streetInput.hasAttribute('data-bng-autofill') || !streetInput.value || streetInput.value.trim().length < 2)) {
                                    streetInput.value = street;
                                }
                            } catch (e) {
                            }
                        }, 350);
                    }

                    syncInputs({ lat: lat, lng: lng });
                    reverseGeocodeFill({ lat: lat, lng: lng });

                    marker.on('dragend', function () {
                        var ll = marker.getLatLng();
                        syncInputs(ll);
                        reverseGeocodeFill(ll);
                    });

                    map.on('click', function (e) {
                        marker.setLatLng(e.latlng);
                        syncInputs(e.latlng);
                        reverseGeocodeFill(e.latlng);
                    });

                    if (latInput && lngInput) {
                        latInput.addEventListener('change', function () {
                            var nlat = toNumber(latInput.value, lat);
                            var nlng = toNumber(lngInput.value, lng);
                            marker.setLatLng([nlat, nlng]);
                            map.setView([nlat, nlng], map.getZoom());
                        });
                        lngInput.addEventListener('change', function () {
                            var nlat = toNumber(latInput.value, lat);
                            var nlng = toNumber(lngInput.value, lng);
                            marker.setLatLng([nlat, nlng]);
                            map.setView([nlat, nlng], map.getZoom());
                        });
                    }

                    var searchInput = modalEl.querySelector('[data-branch-map-search]');
                    var searchBtn = modalEl.querySelector('[data-branch-map-search-btn]');
                    var searching = false;

                    async function doSearch() {
                        if (searching) return;
                        var q = searchInput ? (searchInput.value || '').trim() : '';
                        if (!q) return;
                        searching = true;
                        try {
                            var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q + ', Angola');
                            var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) return;
                            var arr = await res.json();
                            if (!arr || !arr[0]) return;
                            var nlat = parseFloat(arr[0].lat);
                            var nlng = parseFloat(arr[0].lon);
                            if (!Number.isFinite(nlat) || !Number.isFinite(nlng)) return;
                            marker.setLatLng([nlat, nlng]);
                            map.setView([nlat, nlng], 14);
                            syncInputs({ lat: nlat, lng: nlng });
                            reverseGeocodeFill({ lat: nlat, lng: nlng });
                        } finally {
                            searching = false;
                        }
                    }

                    if (searchBtn) searchBtn.addEventListener('click', doSearch);
                    if (searchInput) searchInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            doSearch();
                        }
                    });
                });
            }

            document.addEventListener('shown.bs.modal', function (e) {
                if (!e.target) return;
                createMapInModal(e.target);
            });

            document.addEventListener('shown.bs.collapse', function (e) {
                if (!e.target) return;
                if (!e.target.matches('[data-branch-map-collapse]')) return;
                var modalEl = e.target.closest('.modal');
                if (!modalEl) return;
                setTimeout(function () {
                    createMapInModal(modalEl);
                }, 50);
            });

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-bng-toggle-password]');
                if (!btn) return;
                var group = btn.closest('.input-group');
                if (!group) return;
                var input = group.querySelector('input');
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        })();
    </script>
@endpush
