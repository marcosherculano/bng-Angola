<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($roles === []) {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role === 'pharmacy_normal' && in_array('client', $roles, true)) {
            return $next($request);
        }

        if ($user->role === 'pharmacy_matrix' && in_array('client', $roles, true)) {
            return $next($request);
        }

        if ($user->role === 'pharmacy_branch' && in_array('client', $roles, true)) {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route('acesso-negado');
        }

        return $next($request);
    }
}
