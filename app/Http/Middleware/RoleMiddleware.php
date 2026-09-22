<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Check if the authenticated user has the required role.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            // Redirect to their proper home if they have wrong role
            $role = $request->user()->role;
            if ($role === 'kader' || $role === 'bidan') {
                return redirect()->route('kader.dashboard');
            }
            return redirect()->route('orangtua.anakku');
        }

        return $next($request);
    }
}
