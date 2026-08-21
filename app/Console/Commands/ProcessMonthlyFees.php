<?php

namespace App\Console\Commands;

use App\Models\MonthlyFee;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProcessMonthlyFees extends Command
{
    protected $signature = 'bng:process-monthly-fees';

    protected $description = 'Gera mensalidades em falta e bloqueia farmácias por atraso (due_at + 5 dias).';

    public function handle(): int
    {
        $now = Carbon::now();

        Pharmacy::query()
            ->with(['user'])
            ->where('is_active', true)
            ->chunkById(100, function ($pharmacies) use ($now) {
                foreach ($pharmacies as $pharmacy) {
                    $this->processPharmacy($pharmacy, $now);
                }
            });

        $this->info('Processamento concluído.');

        return 0;
    }

    private function processPharmacy(Pharmacy $pharmacy, Carbon $now): void
    {
        if (! $pharmacy->user) {
            return;
        }

        if ($pharmacy->trial_ends_at && $now->lessThanOrEqualTo($pharmacy->trial_ends_at)) {
            return;
        }

        $currentFee = MonthlyFee::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('cycle_start', '<=', $now->toDateString())
            ->where('cycle_end', '>=', $now->toDateString())
            ->orderByDesc('cycle_start')
            ->first();

        if (! $currentFee) {
            $lastApproved = MonthlyFee::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('status', 'approved')
                ->orderByDesc('approved_at')
                ->first();

            $start = null;
            if ($lastApproved && $lastApproved->approved_at) {
                $start = Carbon::parse($lastApproved->approved_at)->toDateString();
            } elseif ($pharmacy->trial_ends_at) {
                $start = Carbon::parse($pharmacy->trial_ends_at)->toDateString();
            } else {
                $start = $now->toDateString();
            }

            $currentFee = MonthlyFee::query()->firstOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'cycle_start' => $start],
                [
                    'cycle_end' => Carbon::parse($start)->addDays(30)->toDateString(),
                    'due_at' => Carbon::parse($start)->addDays(30),
                    'amount' => (float) $pharmacy->calculateMonthlyAmountV7(),
                    'status' => 'pending',
                ]
            );
        }

        if ($currentFee->status === 'approved') {
            return;
        }

        if ($currentFee->due_at && $now->greaterThan(Carbon::parse($currentFee->due_at))) {
            if (in_array($currentFee->status, ['pending', 'submitted', 'rejected'], true)) {
                $currentFee->status = 'overdue';
                $currentFee->save();
            }
        }

        if ($currentFee->due_at && $now->greaterThan(Carbon::parse($currentFee->due_at)->addDays(5))) {
            if (in_array($currentFee->status, ['pending', 'submitted', 'rejected', 'overdue'], true)) {
                if ($pharmacy->user->status !== 'blocked') {
                    $pharmacy->user->status = 'blocked';
                    $pharmacy->user->save();

                    $request = Request::create('/', 'GET');
                    $request->setUserResolver(function () {
                        return null;
                    });

                    ActivityLogger::log(
                        $request,
                        'pharmacy_auto_blocked_payment_delay',
                        'Bloqueio automático por atraso de pagamento',
                        Pharmacy::class,
                        $pharmacy->id
                    );
                }
            }
        }
    }
}
