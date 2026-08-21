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
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Mensalidades</div>
                </div>

                <div class="card-body">
                    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.mensalidades.index') }}">
                        <div class="col-12 col-md-4">
                            <select class="form-select" name="status">
                                <option value="">Estado</option>
                                @foreach (['pending','submitted','approved','rejected','overdue'] as $s)
                                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Filtrar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.mensalidades.index') }}">Limpar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Farmácia</th>
                                    <th>Plano</th>
                                    <th>Valor</th>
                                    <th>Ciclo</th>
                                    <th>Estado</th>
                                    <th>Prazo</th>
                                    <th class="text-end">Acções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fees as $f)
                                    <tr>
                                        <td>{{ optional($f->pharmacy)->business_name }}</td>
                                        <td>{{ optional($f->pharmacy)->subscription_plan }}</td>
                                        <td>{{ number_format((float) $f->amount, 0, ',', '.') }} Kz</td>
                                        <td>{{ $f->cycle_start }} → {{ $f->cycle_end }}</td>
                                        <td><span class="badge bg-secondary">{{ $f->status }}</span></td>
                                        <td>{{ optional($f->due_at)->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex flex-column gap-2 align-items-end">
                                                @if (! empty($f->proof_path))
                                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.mensalidades.proof', $f) }}" title="Ver comprovativo" aria-label="Ver comprovativo" data-bs-toggle="tooltip" data-bs-title="Ver comprovativo">
                                                        <i class="fa-solid fa-file-arrow-down"></i>
                                                        <span class="ms-1">Comprovativo</span>
                                                    </a>
                                                @endif

                                                @if ($f->status !== 'approved')
                                                    <form method="POST" action="{{ route('admin.mensalidades.approve', $f) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-sm btn-success" type="submit" title="Aprovar" aria-label="Aprovar" data-bs-toggle="tooltip" data-bs-title="Aprovar">
                                                            <i class="fa-solid fa-check"></i>
                                                            <span class="ms-1">Aprovar</span>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('admin.mensalidades.reject', $f) }}" style="min-width: 280px;">
                                                        @csrf
                                                        @method('PUT')
                                                        <textarea class="form-control form-control-sm mb-1" name="rejection_reason" rows="2" placeholder="Motivo da rejeição" required></textarea>
                                                        <button class="btn btn-sm btn-danger" type="submit" title="Rejeitar" aria-label="Rejeitar" data-bs-toggle="tooltip" data-bs-title="Rejeitar" data-confirm="Rejeitar esta mensalidade?">
                                                            <i class="fa-solid fa-xmark"></i>
                                                            <span class="ms-1">Rejeitar</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-muted">Sem registos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $fees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
