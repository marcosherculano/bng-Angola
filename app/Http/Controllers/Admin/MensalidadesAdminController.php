<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFee;
use App\Models\Pharmacy;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class MensalidadesAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = MonthlyFee::query()->with(['pharmacy.user']);

        if ($request->filled('status')) {
            $query->where('status', trim((string) $request->input('status')));
        }

        $fees = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.mensalidades.index', [
            'fees' => $fees,
        ]);
    }

    public function proof(Request $request, MonthlyFee $fee)
    {
        if (empty($fee->proof_path)) {
            return redirect()->route('admin.mensalidades.index')->with('error', 'Sem comprovativo para este registo.');
        }

        if (! Storage::disk('local')->exists($fee->proof_path)) {
            return redirect()->route('admin.mensalidades.index')->with('error', 'Comprovativo não encontrado.');
        }

        return Storage::disk('local')->download($fee->proof_path);
    }

    public function approve(Request $request, MonthlyFee $fee)
    {
        $fee->status = 'approved';
        $fee->approved_at = Carbon::now();
        $fee->approved_by = optional($request->user())->id;
        $fee->rejection_reason = null;
        $fee->save();

        $fee->loadMissing(['pharmacy.user']);

        $pharmacy = $fee->pharmacy;
        if ($pharmacy && $pharmacy->user) {
            if ($pharmacy->user->status === 'blocked') {
                $pharmacy->user->status = 'approved';
                $pharmacy->user->save();
            }
        }

        if ($pharmacy) {
            $nextStart = Carbon::parse($fee->approved_at)->toDateString();

            MonthlyFee::query()->firstOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'cycle_start' => $nextStart],
                [
                    'cycle_end' => Carbon::parse($nextStart)->addDays(30)->toDateString(),
                    'due_at' => Carbon::parse($nextStart)->addDays(30),
                    'amount' => (float) $pharmacy->calculateMonthlyAmountV7(),
                    'status' => 'pending',
                ]
            );
        }

        ActivityLogger::log(
            $request,
            'admin_monthly_fee_approved',
            'Mensalidade aprovada (ID '.$fee->id.')',
            MonthlyFee::class,
            $fee->id
        );

        return redirect()->route('admin.mensalidades.index')->with('success', 'Mensalidade aprovada.');
    }

    public function reject(Request $request, MonthlyFee $fee)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $fee->status = 'rejected';
        $fee->approved_at = null;
        $fee->approved_by = optional($request->user())->id;
        $fee->rejection_reason = $data['rejection_reason'];
        $fee->save();

        ActivityLogger::log(
            $request,
            'admin_monthly_fee_rejected',
            'Mensalidade rejeitada (ID '.$fee->id.'): '.$data['rejection_reason'],
            MonthlyFee::class,
            $fee->id
        );

        return redirect()->route('admin.mensalidades.index')->with('success', 'Mensalidade rejeitada.');
    }
}
