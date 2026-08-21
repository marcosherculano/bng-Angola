<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'cycle_start',
        'cycle_end',
        'due_at',
        'amount',
        'status',
        'proof_path',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'cycle_start' => 'date',
        'cycle_end' => 'date',
        'due_at' => 'datetime',
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
