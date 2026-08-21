<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FarmaciasFiliaisClienteController extends Controller
{
    public function index(Request $request, Pharmacy $pharmacy)
    {
        if ((string) ($pharmacy->type ?? 'normal') !== 'matrix') {
            abort(404);
        }

        $branchesQuery = PharmacyBranch::query()
            ->where('matrix_id', $pharmacy->id)
            ->orderBy('branch_name');

        if (Schema::hasColumn('pharmacy_branches', 'status')) {
            $branchesQuery->where('status', 'approved');
        }

        $branches = $branchesQuery->where('is_active', true)->get();

        return view('cliente.farmacias.filiais', [
            'pharmacy' => $pharmacy,
            'branches' => $branches,
        ]);
    }
}
