<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyBranch;
use App\Models\MonthlyFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DadosBancario;
use App\Models\Pharmacy;

class MensalidadesFarmaciaController extends Controller
{
    private function denyBranch(Request $request)
    {
        $user = $request->user();
        if ($user && (string) ($user->role ?? '') === 'pharmacy_branch') {
            return redirect()->route('pharmacy.painel')->with('error', 'A cobrança é centralizada na matriz. A filial não pode pagar mensalidade.');
        }

        return null;
    }

    private function pharmacyOrRedirect(Request $request)
    {
        $user = $request->user();
        $pharmacy = optional($user)->pharmacy;

        if (! $pharmacy && $user && (string) ($user->role ?? '') === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
            $pharmacy = optional($branch)->matrix;
        }

        if (! $pharmacy) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma farmácia.');
        }

        return $pharmacy;
    }

    public function index(Request $request)
    {
        $deny = $this->denyBranch($request);
        if ($deny) {
            return $deny;
        }

        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $now = Carbon::now();
        $trialEnded = $pharmacy->trial_ends_at ? $now->greaterThan($pharmacy->trial_ends_at) : true;
        $bankData = null;
        if ($trialEnded) {
            $bankData = DadosBancario::query()->orderByDesc('data_alteracao')->first();
        }

        $fees = MonthlyFee::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->orderByDesc('cycle_start')
            ->paginate(20);

        $currentFee = MonthlyFee::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->orderByDesc('cycle_start')
            ->first();

        if (! $currentFee) {
            $start = $pharmacy->trial_ends_at
                ? Carbon::parse($pharmacy->trial_ends_at)->toDateString()
                : $now->toDateString();

            $currentFee = MonthlyFee::query()->create([
                'pharmacy_id' => $pharmacy->id,
                'cycle_start' => $start,
                'cycle_end' => Carbon::parse($start)->addDays(30)->toDateString(),
                'due_at' => Carbon::parse($start)->addDays(30),
                'amount' => (float) $pharmacy->calculateMonthlyAmountV7(),
                'status' => 'pending',
            ]);

            $fees = MonthlyFee::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->orderByDesc('cycle_start')
                ->paginate(20);
        }

        if ($currentFee) {
            $needsFix = empty($currentFee->cycle_end) || empty($currentFee->due_at) || $currentFee->amount === null;
            if ($needsFix) {
                DB::transaction(function () use ($currentFee, $pharmacy) {
                    $start = Carbon::parse($currentFee->cycle_start)->toDateString();
                    if (empty($currentFee->cycle_end)) {
                        $currentFee->cycle_end = Carbon::parse($start)->addDays(30)->toDateString();
                    }
                    if (empty($currentFee->due_at)) {
                        $currentFee->due_at = Carbon::parse($start)->addDays(30);
                    }
                    if ($currentFee->amount === null) {
                        $currentFee->amount = (float) $pharmacy->calculateMonthlyAmountV7();
                    }
                    $currentFee->save();
                });

                $currentFee = MonthlyFee::query()->whereKey($currentFee->id)->first();
            }
        }

        return view('pharmacy.mensalidades.index', [
            'pharmacy' => $pharmacy,
            'currentFee' => $currentFee,
            'fees' => $fees,
            'trialEnded' => $trialEnded,
            'bankData' => $bankData,
        ]);
    }

    public function submitProof(Request $request, MonthlyFee $fee)
    {
        $deny = $this->denyBranch($request);
        if ($deny) {
            return $deny;
        }

        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((int) $fee->pharmacy_id !== (int) $pharmacy->id) {
            return redirect()->route('pharmacy.mensalidades.index')->with('error', 'Acesso não autorizado.');
        }

        if (! in_array($fee->status, ['pending', 'rejected'], true)) {
            return redirect()->route('pharmacy.mensalidades.index')->with('error', 'Não é possível enviar comprovativo neste estado.');
        }

        $data = $request->validate([
            'proof' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $path = $data['proof']->store('private/proofs', 'local');

        $fee->proof_path = $path;
        $fee->submitted_at = Carbon::now();
        $fee->status = 'submitted';
        $fee->rejection_reason = null;
        $fee->save();

        $fee->loadMissing(['pharmacy']);
        $pharmacyName = (string) optional($fee->pharmacy)->business_name;
        $adminIds = User::query()->where('role', 'admin')->pluck('id')->all();

        NotificationService::notifyUsers(
            $adminIds,
            'Novo comprovativo de mensalidade',
            'A farmácia '.($pharmacyName !== '' ? $pharmacyName : '—').' enviou um comprovativo (mensalidade ID '.$fee->id.').',
            route('admin.mensalidades.index', ['status' => 'submitted'])
        );

        return redirect()->route('pharmacy.mensalidades.index')->with('success', 'Comprovativo enviado com sucesso.');
    }
}
