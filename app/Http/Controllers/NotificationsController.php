<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('notifications')) {
            $notifications = new LengthAwarePaginator([], 0, 20);

            return view('notificacoes.index', [
                'notifications' => $notifications,
            ]);
        }

        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('notificacoes.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        if (! Schema::hasTable('notifications')) {
            return redirect()->route('notificacoes.index');
        }

        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return redirect()->route('notificacoes.index')->with('error', 'Acesso não autorizado.');
        }

        if ($notification->read_at === null) {
            $notification->read_at = Carbon::now();
            $notification->save();
        }

        if (! empty($notification->resolved_url)) {
            return redirect()->to($notification->resolved_url);
        }

        return redirect()->route('notificacoes.index')->with('success', 'Notificação marcada como lida.');
    }

    public function markAllRead(Request $request)
    {
        if (! Schema::hasTable('notifications')) {
            return redirect()->route('notificacoes.index');
        }

        Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return redirect()->route('notificacoes.index')->with('success', 'Notificações marcadas como lidas.');
    }
}
