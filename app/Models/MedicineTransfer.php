<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function from()
    {
        return $this->morphTo(__FUNCTION__, 'from_type', 'from_id');
    }

    public function to()
    {
        return $this->morphTo(__FUNCTION__, 'to_type', 'to_id');
    }
}
