@extends('layouts.app')

@section('content')
<div class="container">
    @php
        $bngUser = auth()->user();
        $bngRole = auth()->check() ? (string) (optional($bngUser)->role ?? '') : '';
        $bngBranch = null;
        if ($bngRole === 'pharmacy_branch' && auth()->check()) {
            $bngBranch = \App\Models\PharmacyBranch::query()->with(['matrix'])->where('user_id', $bngUser->id)->first();
        }

        $bngPharmacy = auth()->check() ? optional($bngUser)->pharmacy : null;
        if (! $bngPharmacy && $bngBranch) {
            $bngPharmacy = $bngBranch->matrix;
        }
        $bngLastFee = $bngPharmacy
            ? \App\Models\MonthlyFee::query()->where('pharmacy_id', $bngPharmacy->id)->orderByDesc('cycle_start')->first()
            : null;

        $bngMedicinesCount = $bngPharmacy ? \App\Models\Medicine::query()->where('pharmacy_id', $bngPharmacy->id)->count() : 0;
        $bngLowStockCount = 0;
        if ($bngBranch) {
            $bngLowStockCount = \App\Models\MedicineInventory::query()
                ->where('owner_type', 'pharmacy_branch')
                ->where('owner_id', $bngBranch->id)
                ->where('stock', '<=', 5)
                ->count();
        } elseif ($bngPharmacy) {
            $bngLowStockCount = \App\Models\Medicine::query()
                ->where('pharmacy_id', $bngPharmacy->id)
                ->where('stock', '<=', 5)
                ->count();
        }
        $bngOrdersCount = $bngPharmacy ? \App\Models\Order::query()->where('pharmacy_id', $bngPharmacy->id)->count() : 0;
        $bngOrdersPendingCount = $bngPharmacy
            ? \App\Models\Order::query()->where('pharmacy_id', $bngPharmacy->id)->whereIn('status', ['pending', 'schedule_requested', 'schedule_confirmed'])->count()
            : 0;
        $bngLastOrders = $bngPharmacy
            ? \App\Models\Order::query()->where('pharmacy_id', $bngPharmacy->id)->orderByDesc('id')->limit(5)->get()
            : collect();

        $bngFeeStatusLabel = $bngLastFee ? (string) $bngLastFee->status : '';
        $bngFeeBadge = 'bg-secondary';
        if (in_array($bngFeeStatusLabel, ['approved', 'paid', 'active'], true)) $bngFeeBadge = 'bg-success';
        if (in_array($bngFeeStatusLabel, ['pending', 'submitted'], true)) $bngFeeBadge = 'bg-warning text-dark';
        if (in_array($bngFeeStatusLabel, ['rejected', 'overdue', 'blocked'], true)) $bngFeeBadge = 'bg-danger';

        $bngIsMatrix = auth()->check() && (string) (auth()->user()->role ?? '') === 'pharmacy_matrix';
        $bngIsNormal = auth()->check() && (string) (auth()->user()->role ?? '') === 'pharmacy_normal';
        $bngIsBranch = auth()->check() && (string) (auth()->user()->role ?? '') === 'pharmacy_branch';
        $bngBranches = collect();
        $bngBranchesTotal = 0;
        $bngBranchesActive = 0;
        $bngBranchesPending = 0;
        $bngBranchesInactive = 0;
        $bngMatrixMonthlyAmount = null;
        $bngHasBranchStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('pharmacy_branches', 'status');
        $bngHasBranchMonthlyFeeColumn = \Illuminate\Support\Facades\Schema::hasColumn('pharmacy_branches', 'monthly_fee');
        $bngMatrixBaseFee = null;
        $bngBranchesMonthlySum = null;

        $bngNormalBaseFee = null;
        $bngNormalMonthlyAmount = null;
        $bngBranchMonthlyFee = null;

        if ($bngIsMatrix && $bngPharmacy) {
            $bngBranches = \App\Models\PharmacyBranch::query()
                ->with(['user'])
                ->where('matrix_id', $bngPharmacy->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get();

            $bngBranchesTotal = \App\Models\PharmacyBranch::query()
                ->where('matrix_id', $bngPharmacy->id)
                ->count();

            $bngBranchesPending = \App\Models\PharmacyBranch::query()
                ->where('matrix_id', $bngPharmacy->id)
                ->when($bngHasBranchStatusColumn, function ($q) {
                    $q->where('status', 'pending');
                }, function ($q) {
                    $q->where('is_active', false);
                })
                ->count();

            $bngBranchesActive = \App\Models\PharmacyBranch::query()
                ->where('matrix_id', $bngPharmacy->id)
                ->where('is_active', true)
                ->when($bngHasBranchStatusColumn, function ($q) {
                    $q->where('status', 'approved');
                })
                ->count();

            $bngBranchesInactive = max(0, (int) $bngBranchesTotal - (int) $bngBranchesPending - (int) $bngBranchesActive);

            $bngMatrixBaseFee = (float) ($bngPharmacy->monthly_fee ?? 0);
            if ($bngMatrixBaseFee <= 0) {
                $bngMatrixBaseFee = 2700;
            }

            if ($bngHasBranchMonthlyFeeColumn) {
                $activeBranchesMonthlyQuery = \App\Models\PharmacyBranch::query()
                    ->where('matrix_id', $bngPharmacy->id)
                    ->where('is_active', true)
                    ->when($bngHasBranchStatusColumn, function ($q) {
                        $q->where('status', 'approved');
                    });

                $bngBranchesMonthlySum = (float) $activeBranchesMonthlyQuery->sum('monthly_fee');
            }

            $bngMatrixMonthlyAmount = (float) $bngPharmacy->calculateMonthlyAmountV7();
        }

        if ($bngIsNormal && $bngPharmacy) {
            $bngNormalBaseFee = (float) ($bngPharmacy->monthly_fee ?? 0);
            if ($bngNormalBaseFee <= 0) {
                $bngNormalBaseFee = 2000;
            }

            $bngNormalMonthlyAmount = (float) $bngPharmacy->calculateMonthlyAmountV7();
        }

        if ($bngIsBranch && $bngBranch) {
            $bngBranchMonthlyFee = (float) ($bngBranch->monthly_fee ?? 0);
        }
    @endphp

    <div class="card shadow-sm border-0 mb-3" style="background: linear-gradient(135deg, rgba(13,110,253,.12), rgba(25,135,84,.10));">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="h4 mb-1">Painel da Farmácia</div>
                    <div class="text-muted">
                        {{ $bngBranch ? $bngBranch->branch_name : ($bngPharmacy ? $bngPharmacy->business_name : '—') }}
                        @if ($bngPharmacy && $bngPharmacy->province)
                            <span class="mx-2 text-muted">•</span>
                            {{ $bngBranch ? $bngBranch->province : $bngPharmacy->province }}
                        @endif
                    </div>
                    <div class="mt-2">
                        @if (auth()->check())
                            <span class="badge bg-light text-dark border">{{ (string) (auth()->user()->role ?? '') }}</span>
                        @endif
                        @if ($bngLastFee)
                            <span class="badge {{ $bngFeeBadge }} ms-1">Mensalidade: {{ $bngFeeStatusLabel }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary" href="{{ url('/') }}">Página pública</a>
                    <a class="btn btn-primary shadow-sm fw-semibold px-4 py-2 rounded-pill w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('cliente.busca') }}" style="background: linear-gradient(135deg, #0d6efd, #198754); border: 0;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="d-inline-block text-start">
                            <span class="d-block">Buscar medicamentos</span>
                            <span class="d-block small" style="opacity: .9;">Ver estoque e rotas</span>
                        </span>
                    </a>
                    <a class="btn btn-primary" href="{{ $bngBranch ? route('pharmacy.branch_medicines.index') : route('pharmacy.medicines.index') }}">
                        <i class="fa-solid fa-pills"></i>
                        <span class="ms-1">Medicamentos</span>
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('pharmacy.orders.index') }}">
                        <i class="fa-solid fa-receipt"></i>
                        <span class="ms-1">Pedidos</span>
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('pharmacy.payment_settings.edit') }}">
                        <i class="fa-solid fa-money-check-dollar"></i>
                        <span class="ms-1">Pagamentos</span>
                    </a>

                    @if ($bngIsMatrix)
                        <a class="btn btn-outline-primary" href="{{ route('pharmacy.filiais.index') }}">
                            <i class="fa-solid fa-code-branch"></i>
                            <span class="ms-1">Filiais</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-muted small">Medicamentos</div>
                            <div class="h4 mb-0">{{ number_format((int) $bngMedicinesCount, 0, ',', '.') }}</div>
                            <div class="small text-muted">No catálogo</div>
                            @if ((int) $bngLowStockCount > 0)
                                <div class="mt-1">
                                    <span class="badge bg-warning text-dark">Stock baixo: {{ (int) $bngLowStockCount }}</span>
                                </div>
                            @endif
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ $bngBranch ? route('pharmacy.branch_medicines.index', ['low_stock' => 1]) : route('pharmacy.medicines.index', ['low_stock' => 1]) }}" title="Ver stock baixo" aria-label="Ver stock baixo" data-bs-toggle="tooltip" data-bs-title="Ver stock baixo">
                            <i class="fa-solid fa-pills"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-muted small">Pedidos</div>
                            <div class="h4 mb-0">{{ number_format((int) $bngOrdersCount, 0, ',', '.') }}</div>
                            <div class="small text-muted">{{ number_format((int) $bngOrdersPendingCount, 0, ',', '.') }} pendentes</div>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.orders.index') }}" title="Ver pedidos" aria-label="Ver pedidos" data-bs-toggle="tooltip" data-bs-title="Ver pedidos">
                            <i class="fa-solid fa-receipt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @if (auth()->check() && (string) (auth()->user()->role ?? '') === 'pharmacy_matrix')
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Filiais</div>
                                <div class="h4 mb-0">Gerir</div>
                                <div class="small text-muted">Aprovações e activação</div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.filiais.index') }}" title="Gerir filiais" aria-label="Gerir filiais" data-bs-toggle="tooltip" data-bs-title="Gerir filiais">
                                <i class="fa-solid fa-code-branch"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($bngIsNormal)
            <div class="col-12">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(13,110,253,.10), rgba(25,135,84,.06));">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="h5 mb-1">Resumo da Farmácia</div>
                                <div class="text-muted small">
                                    Valor mensal {{ number_format((float) ($bngNormalBaseFee ?? 2000), 0, ',', '.') }} Kz.
                                </div>
                            </div>

                            <div class="text-md-end">
                                <div class="text-muted small">Total mensal calculado</div>
                                <div class="h3 mb-0">{{ number_format((float) ($bngNormalMonthlyAmount ?? 0), 0, ',', '.') }} Kz</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($bngIsBranch && $bngBranch)
            <div class="col-12">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(13,110,253,.06));">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="h5 mb-1">Resumo da Filial</div>
                                <div class="text-muted small">
                                    Valor mensal da filial {{ number_format((float) ($bngBranchMonthlyFee ?? 0), 0, ',', '.') }} Kz.
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-light text-dark border">Matriz: {{ optional($bngBranch->matrix)->business_name ?: '—' }}</span>
                                </div>
                            </div>

                            <div class="text-md-end">
                                <div class="text-muted small">Estado</div>
                                @php
                                    $branchStatus = (string) ($bngBranch->status ?? 'pending');
                                    $branchBadge = 'bg-secondary';
                                    $branchLabel = 'Desactivada';
                                    if ($branchStatus === 'pending') {
                                        $branchBadge = 'bg-warning text-dark';
                                        $branchLabel = 'Pendente';
                                    } elseif ($branchStatus === 'approved' && (bool) $bngBranch->is_active) {
                                        $branchBadge = 'bg-success';
                                        $branchLabel = 'Activa';
                                    }
                                @endphp
                                <div class="h5 mb-0"><span class="badge {{ $branchBadge }}">{{ $branchLabel }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-muted small">Stock baixo</div>
                            <div class="h4 mb-0">{{ number_format((int) $bngLowStockCount, 0, ',', '.') }}</div>
                            <div class="small text-muted">Até 5 unidades</div>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.medicines.index', ['low_stock' => 1]) }}" title="Ver stock baixo" aria-label="Ver stock baixo" data-bs-toggle="tooltip" data-bs-title="Ver stock baixo">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($bngIsMatrix)
            <div class="col-12">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(13,110,253,.08));">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="h5 mb-1">Resumo da Matriz</div>
                                <div class="text-muted small">
                                    Valor mensal base {{ number_format((float) ($bngMatrixBaseFee ?? 2700), 0, ',', '.') }} Kz
                                    @if ($bngHasBranchMonthlyFeeColumn)
                                        + {{ number_format((float) ($bngBranchesMonthlySum ?? 0), 0, ',', '.') }} Kz (filiais activas/aprovadas)
                                    @else
                                        + 1.000 Kz por filial activa
                                    @endif
                                    .
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-light text-dark border">Total de filiais: {{ number_format((int) $bngBranchesTotal, 0, ',', '.') }}</span>
                                    <span class="badge bg-success ms-1">Activas: {{ number_format((int) $bngBranchesActive, 0, ',', '.') }}</span>
                                    <span class="badge bg-warning text-dark ms-1">Pendentes: {{ number_format((int) $bngBranchesPending, 0, ',', '.') }}</span>
                                    <span class="badge bg-secondary ms-1">Desactivadas: {{ number_format((int) $bngBranchesInactive, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="text-md-end">
                                <div class="text-muted small">Total mensal calculado</div>
                                <div class="h3 mb-2">{{ number_format((float) ($bngMatrixMonthlyAmount ?? 0), 0, ',', '.') }} Kz</div>
                                <a class="btn btn-outline-primary" href="{{ route('pharmacy.filiais.index') }}">
                                    <i class="fa-solid fa-code-branch"></i>
                                    <span class="ms-1">Gerir filiais</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Filiais (visão geral)</div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.filiais.index') }}">Ver todas</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Filial</th>
                                        <th>Província</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th class="text-end">Criada em</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bngBranches as $br)
                                        @php
                                            $brStatus = $bngHasBranchStatusColumn ? (string) ($br->status ?? 'pending') : null;
                                            $brBadge = 'bg-secondary';
                                            $brLabel = 'Desactivada';
                                            if ($bngHasBranchStatusColumn && $brStatus === 'pending') {
                                                $brBadge = 'bg-warning text-dark';
                                                $brLabel = 'Pendente';
                                            } elseif ($bngHasBranchStatusColumn && $brStatus === 'approved' && (bool) $br->is_active) {
                                                $brBadge = 'bg-success';
                                                $brLabel = 'Activa';
                                            } elseif (! $bngHasBranchStatusColumn && (bool) $br->is_active) {
                                                $brBadge = 'bg-success';
                                                $brLabel = 'Activa';
                                            } elseif (! $bngHasBranchStatusColumn && ! (bool) $br->is_active) {
                                                $brBadge = 'bg-warning text-dark';
                                                $brLabel = 'Pendente';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $br->branch_name }}</td>
                                            <td>{{ $br->province }}</td>
                                            <td>
                                                <div class="small text-muted">{{ $br->phone ?: '—' }}</div>
                                                <div class="small text-muted">{{ $br->email ?: '—' }}</div>
                                                <div class="small text-muted">Login: {{ optional($br->user)->email ?: '—' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $brBadge }}">{{ $brLabel }}</span>
                                            </td>
                                            <td class="text-end text-muted small">{{ optional($br->created_at)->format('d/m/Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted p-3">Sem filiais cadastradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-3">
            @if (!auth()->user() || (string) (auth()->user()->role ?? '') !== 'pharmacy_branch')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted small">Mensalidade</div>
                                @if ($bngLastFee)
                                    <div class="h4 mb-0">{{ number_format((float) $bngLastFee->amount, 0, ',', '.') }} Kz</div>
                                    <div class="small"><span class="badge {{ $bngFeeBadge }}">{{ $bngLastFee->status }}</span></div>
                                @else
                                    <div class="h4 mb-0">— Kz</div>
                                @endif
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.mensalidades.index') }}" title="Ver mensalidades" aria-label="Ver mensalidades" data-bs-toggle="tooltip" data-bs-title="Ver mensalidades">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Últimos pedidos</div>
                    <div class="d-flex gap-2 align-items-center">
                        <form method="POST" action="{{ route('pharmacy.atividades.clear') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Limpar as suas atividades?">
                                <i class="fa-solid fa-broom"></i>
                                <span class="ms-1">Limpar atividades</span>
                            </button>
                        </form>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('pharmacy.orders.index') }}">Ver todos</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bngLastOrders as $o)
                                    <tr>
                                        <td class="fw-semibold">{{ $o->id }}</td>
                                        <td>{{ optional($o->client)->name ?: '—' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $o->status_label }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format((float) ($o->total_price ?? 0), 0, ',', '.') }} Kz</td>
                                        <td class="text-end text-muted small">{{ optional($o->created_at)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted p-3">Sem pedidos recentes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
