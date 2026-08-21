<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'schedule_requested' => 'Agendamento solicitado',
        'schedule_confirmed' => 'Agendamento confirmado',
        'ready_for_pickup' => 'Pronto para levantamento',
        'delivery_requested' => 'Entrega solicitada',
        'delivery_in_progress' => 'Entrega em curso',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado',
    ];

    protected $fillable = [
        'client_id',
        'pharmacy_id',
        'medicine_inventory_id',
        'pharmacy_branch_id',
        'status',
        'pickup_method',
        'external_transport_name',
        'total_price',
        'customer_notes',
        'scheduled_pickup_at',
        'schedule_notes',
        'schedule_confirmed_at',
        'delivered_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'scheduled_pickup_at' => 'datetime',
        'schedule_confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $with = ['items'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function branch()
    {
        return $this->belongsTo(PharmacyBranch::class, 'pharmacy_branch_id');
    }

    public function medicineInventory()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_inventory_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(OrderPayment::class);
    }

    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $status = (string) ($this->status ?? '');

        return (string) (self::STATUS_LABELS[$status] ?? $status);
    }
}
