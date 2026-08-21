<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPharmacyStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($request->routeIs('aguardar-aprovacao', 'conta-suspensa', 'acesso-negado')) {
            return $next($request);
        }

        if ($user->status === 'pending') {
            return redirect()->route('aguardar-aprovacao');
        }

        if ($user->status === 'blocked' && $request->routeIs('pharmacy.mensalidades.*')) {
            return $next($request);
        }

        if (in_array($user->status, ['suspended', 'blocked'], true)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('conta-suspensa');
        }

        return $next($request);
    }
}
