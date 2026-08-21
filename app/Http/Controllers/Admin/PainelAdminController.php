<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MonthlyFee;
use App\Models\PharmacyBranch;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PainelAdminController extends Controller
{
    public function __invoke(Request $request)
    {
        $now = Carbon::now();

        $kpis = [
            'users_approved' => User::query()->where('status', 'approved')->count(),
            'users_pending' => User::query()->where('status', 'pending')->count(),
            'users_suspended' => User::query()->where('status', 'suspended')->count(),
            'pharmacies_active' => Pharmacy::query()->where('is_active', true)->count(),
            'pharmacies_trial' => Pharmacy::query()->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', $now)->count(),
            'branches_pending' => PharmacyBranch::query()->where('is_active', false)->count(),
            'monthlyfees_submitted' => MonthlyFee::query()->where('status', 'submitted')->count(),
            'monthlyfees_pending' => MonthlyFee::query()->where('status', 'pending')->count(),
            'monthlyfees_rejected' => MonthlyFee::query()->where('status', 'rejected')->count(),
        ];

        $recentLogs = ActivityLog::query()->with(['user'])->latest()->limit(10)->get();

        return view('admin.painel', [
            'kpis' => $kpis,
            'recentLogs' => $recentLogs,
        ]);
    }
}
