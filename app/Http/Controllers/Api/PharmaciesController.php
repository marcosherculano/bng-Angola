<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PharmaciesController extends Controller
{
    public function index(Request $request)
    {
        $pharmacies = Pharmacy::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('business_name')
            ->get([
                'id',
                'business_name',
                'phone',
                'email',
                'province',
                'city',
                'neighborhood',
                'street',
                'latitude',
                'longitude',
                'type',
            ]);

        $branchesQuery = PharmacyBranch::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('branch_name');

        if (Schema::hasColumn('pharmacy_branches', 'status')) {
            $branchesQuery->where('status', 'approved');
        }

        $branches = $branchesQuery->get([
            'id',
            'matrix_id',
            'branch_name',
            'phone',
            'email',
            'province',
            'city',
            'neighborhood',
            'street',
            'latitude',
            'longitude',
        ]);

        $items = [];

        foreach ($pharmacies as $p) {
            $items[] = [
                'type' => 'pharmacy',
                'id' => (int) $p->id,
                'name' => (string) ($p->business_name ?? ''),
                'phone' => $p->phone,
                'email' => $p->email,
                'province' => $p->province,
                'city' => $p->city,
                'neighborhood' => $p->neighborhood,
                'street' => $p->street,
                'latitude' => $p->latitude !== null ? (float) $p->latitude : null,
                'longitude' => $p->longitude !== null ? (float) $p->longitude : null,
                'pharmacy_type' => $p->type,
            ];
        }

        foreach ($branches as $b) {
            $items[] = [
                'type' => 'branch',
                'id' => (int) $b->id,
                'matrix_id' => (int) $b->matrix_id,
                'name' => (string) ($b->branch_name ?? ''),
                'phone' => $b->phone,
                'email' => $b->email,
                'province' => $b->province,
                'city' => $b->city,
                'neighborhood' => $b->neighborhood,
                'street' => $b->street,
                'latitude' => $b->latitude !== null ? (float) $b->latitude : null,
                'longitude' => $b->longitude !== null ? (float) $b->longitude : null,
            ];
        }

        return response()->json([
            'items' => $items,
        ]);
    }
}
