<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'partner',
        'external_id',
        'driver_name',
        'driver_phone',
        'estimated_price',
        'currency',
        'notes',
        'status',
        'partner_status',
        'requested_at',
        'started_at',
        'delivered_at',
        'eta_at',
        'raw_payload',
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'requested_at' => 'datetime',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'eta_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
