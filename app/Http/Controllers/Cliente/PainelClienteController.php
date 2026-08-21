<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PainelClienteController extends Controller
{
    public function __invoke(Request $request)
    {
        $userId = (int) $request->user()->id;

        $base = Order::query()->where('client_id', $userId);

        $totalOrders = (clone $base)->count();

        $countsByStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $inProgressOrders = (int) (
            ($countsByStatus['pending'] ?? 0)
            + ($countsByStatus['schedule_requested'] ?? 0)
            + ($countsByStatus['schedule_confirmed'] ?? 0)
            + ($countsByStatus['ready_for_pickup'] ?? 0)
        );

        $deliveredOrders = (int) ($countsByStatus['delivered'] ?? 0);

        $totalSpent = (clone $base)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_price');

        $lastOrder = (clone $base)
            ->with(['pharmacy'])
            ->orderByDesc('id')
            ->first();

        $recentOrders = (clone $base)
            ->with(['pharmacy'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('client.painel', [
            'totalOrders' => $totalOrders,
            'inProgressOrders' => $inProgressOrders,
            'deliveredOrders' => $deliveredOrders,
            'countsByStatus' => $countsByStatus,
            'totalSpent' => $totalSpent,
            'lastOrder' => $lastOrder,
            'recentOrders' => $recentOrders,
        ]);
    }
}
