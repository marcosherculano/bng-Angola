<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogsAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('action_type', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('model_type', 'like', "%{$q}%");
            });
        }

        $logs = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
        ]);
    }

    public function clear(Request $request)
    {
        ActivityLog::query()->delete();

        return redirect()->route('admin.painel')->with('success', 'Atividades limpas com sucesso.');
    }
}
