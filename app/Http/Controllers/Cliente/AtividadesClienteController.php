<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AtividadesClienteController extends Controller
{
    public function clear(Request $request)
    {
        $userId = (int) $request->user()->id;

        ActivityLog::query()->where('user_id', $userId)->delete();

        return redirect()->route('client.painel')->with('success', 'As suas atividades foram limpas com sucesso.');
    }
}
