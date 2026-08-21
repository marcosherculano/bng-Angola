<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'nif',
        'alvara',
        'alvara_document_path',
        'phone',
        'email',
        'province',
        'city',
        'neighborhood',
        'street',
        'latitude',
        'longitude',
        'type',
        'matrix_id',
        'is_active',
        'subscription_plan',
        'monthly_fee',
        'approved_at',
        'approved_by',
        'trial_starts_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }

    public function medicineInventories()
    {
        return $this->morphMany(MedicineInventory::class, 'owner');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function monthlyFees()
    {
        return $this->hasMany(MonthlyFee::class);
    }

    public function branches()
    {
        return $this->hasMany(PharmacyBranch::class, 'matrix_id');
    }

    public function paymentSettings()
    {
        return $this->hasOne(PharmacyPaymentSetting::class);
    }

    public function calculateMonthlyAmountV7(): float
    {
        $type = (string) ($this->type ?? 'normal');

        $hasPharmacyMonthlyFee = Schema::hasColumn('pharmacies', 'monthly_fee');
        $base = $hasPharmacyMonthlyFee ? (float) ($this->monthly_fee ?? 0) : 0.0;

        if ($type === 'matrix') {
            $hasStatus = Schema::hasColumn('pharmacy_branches', 'status');
            $hasBranchMonthlyFee = Schema::hasColumn('pharmacy_branches', 'monthly_fee');

            $activeBranchesQuery = $this->branches()->where('is_active', true);
            if ($hasStatus) {
                $activeBranchesQuery->where('status', 'approved');
            }

            if ($hasBranchMonthlyFee) {
                $branchesSum = (float) $activeBranchesQuery->sum('monthly_fee');
                $fallbackBase = ($base > 0) ? $base : 2700;

                return $fallbackBase + $branchesSum;
            }

            $activeBranches = $activeBranchesQuery->count();
            $fallbackBase = ($base > 0) ? $base : 2700;

            return $fallbackBase + (1000 * (int) $activeBranches);
        }

        if ($base > 0) {
            return $base;
        }

        return 2000;
    }

    public function matrix()
    {
        return $this->belongsTo(Pharmacy::class, 'matrix_id');
    }
}
