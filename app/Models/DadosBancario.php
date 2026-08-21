<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DadosBancario extends Model
{
    use HasFactory;

    protected $table = 'dados_bancarios';

    protected $fillable = [
        'banco',
        'titular',
        'numero_conta',
        'iban',
        'data_alteracao',
        'admin_id',
    ];

    protected $casts = [
        'data_alteracao' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
