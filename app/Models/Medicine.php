<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'barcode',
        'category',
        'description',
        'price',
        'stock',
        'requires_prescription',
        'image_path',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventories()
    {
        return $this->hasMany(MedicineInventory::class);
    }
}
