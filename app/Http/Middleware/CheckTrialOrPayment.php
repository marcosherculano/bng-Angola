<?php

namespace App\Http\Middleware;

use App\Models\MonthlyFee;
use App\Models\Pharmacy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialOrPayment
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! in_array($user->role, ['pharmacy_normal', 'pharmacy_matrix', 'pharmacy_branch'], true)) {
            return $next($request);
        }

        if (in_array($user->role, ['pharmacy_normal', 'pharmacy_matrix'], true)) {
            $pharmacy = Pharmacy::query()->where('user_id', $user->id)->first();

            if (! $pharmacy) {
                if ((string) ($user->status ?? '') !== 'blocked') {
                    $user->status = 'blocked';
                    $user->save();
                }

                return redirect()->route('conta-suspensa');
            }

            if ($request->routeIs('pharmacy.mensalidades.*')) {
                return $next($request);
            }

            if ($pharmacy->trial_ends_at && Carbon::now()->lessThanOrEqualTo($pharmacy->trial_ends_at)) {
                return $next($request);
            }

            $now = Carbon::now();

            $currentFee = MonthlyFee::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('cycle_start', '<=', $now->toDateString())
                ->where('cycle_end', '>=', $now->toDateString())
                ->orderByDesc('cycle_start')
                ->first();

            if (! $currentFee) {
                $currentFee = MonthlyFee::query()
                    ->where('pharmacy_id', $pharmacy->id)
                    ->orderByDesc('cycle_start')
                    ->first();
            }

            if ($currentFee && $currentFee->status === 'approved') {
                return $next($request);
            }

            if ($currentFee && $currentFee->due_at) {
                $blockAt = Carbon::parse($currentFee->due_at)->addDays(5);
                if ($now->lessThanOrEqualTo($blockAt)) {
                    return $next($request);
                }
            }

            if ((string) ($user->status ?? '') !== 'blocked') {
                $user->status = 'blocked';
                $user->save();
            }

            return redirect()->route('conta-suspensa');
        }

        return $next($request);
    }
}
