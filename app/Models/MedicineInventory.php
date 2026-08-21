<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'owner_type',
        'owner_id',
        'price',
        'stock',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_available' => 'boolean',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
