@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="h4 mb-0">Mensalidades</div>
            <div class="text-muted">Pagamento e histórico</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('pharmacy.painel') }}">Voltar</a>
    </div>

    @if (!empty($trialEnded) && $trialEnded)
        <div class="alert alert-warning">
            <div class="fw-semibold">Período gratuito terminado. Efetue o pagamento para continuar ativo.</div>
            @if (!empty($bankData))
                <div class="small mt-2">
                    <div><span class="text-muted">Banco:</span> <span class="fw-semibold">{{ $bankData->banco }}</span></div>
                    <div><span class="text-muted">Titular:</span> <span class="fw-semibold">{{ $bankData->titular }}</span></div>
                    <div><span class="text-muted">Nº da conta:</span> <span class="fw-semibold">{{ $bankData->numero_conta }}</span></div>
                    <div><span class="text-muted">IBAN:</span> <span class="fw-semibold">{{ $bankData->iban ?: '—' }}</span></div>
                </div>
            @else
                <div class="small mt-2 text-muted">Dados bancários ainda não foram configurados pelo administrador.</div>
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Mensalidade actual</div>
                <div class="card-body">
                    @if ($currentFee)
                        <div class="mb-2"><span class="text-muted small">Ciclo</span><div class="fw-semibold">{{ $currentFee->cycle_start }} → {{ $currentFee->cycle_end }}</div></div>
                        <div class="mb-2"><span class="text-muted small">Valor</span><div class="fw-semibold">{{ number_format((float) $currentFee->amount, 0, ',', '.') }} Kz</div></div>
                        <div class="mb-2"><span class="text-muted small">Estado</span><div><span class="badge bg-secondary">{{ $currentFee->status }}</span></div></div>
                        <div class="mb-2"><span class="text-muted small">Prazo</span><div class="fw-semibold">{{ optional($currentFee->due_at)->format('Y-m-d H:i') }}</div></div>

                        @if (! empty($currentFee->rejection_reason))
                            <div class="alert alert-warning">
                                <div class="fw-semibold">Rejeitado</div>
                                <div class="small">{{ $currentFee->rejection_reason }}</div>
                            </div>
                        @endif

                        @if (in_array($currentFee->status, ['pending', 'rejected'], true))
                            <form method="POST" action="{{ route('pharmacy.mensalidades.submitProof', $currentFee) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="mb-2">
                                    <label class="form-label">Enviar comprovativo (PDF/JPG/PNG)</label>
                                    <input class="form-control" type="file" name="proof" required>
                                    <div class="form-text">Tamanho máximo: 10MB</div>
                                </div>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa-solid fa-upload"></i>
                                    <span class="ms-1">Enviar</span>
                                </button>
                            </form>
                        @else
                            <div class="text-muted small">O comprovativo só pode ser enviado quando o estado estiver em pending ou rejected.</div>
                        @endif
                    @else
                        <div class="text-muted">Sem mensalidade gerada ainda.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Histórico</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Ciclo</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th>Prazo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fees as $f)
                                    <tr>
                                        <td>{{ $f->cycle_start }} → {{ $f->cycle_end }}</td>
                                        <td>{{ number_format((float) $f->amount, 0, ',', '.') }} Kz</td>
                                        <td><span class="badge bg-secondary">{{ $f->status }}</span></td>
                                        <td>{{ optional($f->due_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">Sem registos.</td></tr>
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
