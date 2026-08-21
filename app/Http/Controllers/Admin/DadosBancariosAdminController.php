<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DadosBancario;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DadosBancariosAdminController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('dados_bancarios')) {
            $history = new LengthAwarePaginator([], 0, 15);

            return view('admin.dados_bancarios.index', [
                'current' => null,
                'history' => $history,
                'missingTable' => true,
            ]);
        }

        $current = DadosBancario::query()->orderByDesc('data_alteracao')->first();

        $history = DadosBancario::query()
            ->orderByDesc('data_alteracao')
            ->paginate(15);

        return view('admin.dados_bancarios.index', [
            'current' => $current,
            'history' => $history,
            'missingTable' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'banco' => ['required', 'string', 'max:150'],
            'titular' => ['required', 'string', 'max:150'],
            'numero_conta' => ['nullable', 'string', 'max:80'],
            'iban' => ['required', 'string', 'max:80'],
        ]);

        $record = DadosBancario::query()->create([
            'banco' => trim((string) $data['banco']),
            'titular' => trim((string) $data['titular']),
            'numero_conta' => isset($data['numero_conta']) && trim((string) $data['numero_conta']) !== ''
                ? trim((string) $data['numero_conta'])
                : null,
            'iban' => trim((string) $data['iban']),
            'data_alteracao' => Carbon::now(),
            'admin_id' => optional($request->user())->id,
        ]);

        ActivityLogger::log(
            $request,
            'admin_bank_data_updated',
            'Dados bancários actualizados (ID '.$record->id.')',
            DadosBancario::class,
            $record->id
        );

        return redirect()->route('admin.dados_bancarios.index')->with('success', 'Dados bancários guardados com sucesso.');
    }

    public function makeCurrent(Request $request, DadosBancario $record)
    {
        if (! Schema::hasTable('dados_bancarios')) {
            return redirect()->route('admin.dados_bancarios.index')->with('error', 'Tabela de dados bancários não encontrada.');
        }

        $new = DB::transaction(function () use ($request, $record) {
            return DadosBancario::query()->create([
                'banco' => (string) $record->banco,
                'titular' => (string) $record->titular,
                'numero_conta' => $record->numero_conta,
                'iban' => (string) $record->iban,
                'data_alteracao' => Carbon::now(),
                'admin_id' => optional($request->user())->id,
            ]);
        });

        ActivityLogger::log(
            $request,
            'admin_bank_data_set_current',
            'Dados bancários definidos como actuais (baseado no ID '.$record->id.', novo ID '.$new->id.')',
            DadosBancario::class,
            $new->id
        );

        return redirect()->route('admin.dados_bancarios.index')->with('success', 'Dados bancários actualizados a partir do histórico.');
    }
}
