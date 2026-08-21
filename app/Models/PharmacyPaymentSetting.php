<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyPaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'is_active',
        'bank_name',
        'account_holder',
        'account_number',
        'iban',
        'express_number',
        'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
