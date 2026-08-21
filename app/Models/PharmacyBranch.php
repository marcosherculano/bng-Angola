<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'matrix_id',
        'user_id',
        'branch_name',
        'nif',
        'alvara',
        'phone',
        'email',
        'province',
        'city',
        'neighborhood',
        'street',
        'opening_hours',
        'latitude',
        'longitude',
        'is_active',
        'status',
        'monthly_fee',
        'document_path',
        'image_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function matrix()
    {
        return $this->belongsTo(Pharmacy::class, 'matrix_id');
    }

    public function medicineInventories()
    {
        return $this->morphMany(MedicineInventory::class, 'owner');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentSettings()
    {
        return $this->hasOne(PharmacyBranchPaymentSetting::class, 'pharmacy_branch_id');
    }
}
