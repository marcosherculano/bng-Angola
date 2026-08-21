<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk',
        'path',
        'size_bytes',
        'status',
        'error_message',
        'options',
        'created_by',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'options' => 'array',
        'restored_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
