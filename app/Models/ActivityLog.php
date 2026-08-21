<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'description',
        'ip_address',
        'model_type',
        'model_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
