<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
        'mime',
        'size_bytes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'size_bytes' => 'integer',
    ];
}
