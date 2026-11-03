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
            // API: return JSON, Web: redirect to login
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            // API: return JSON 403, Web: redirect to proper dashboard
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            $role = $request->user()->role;
            if ($role === 'kader') {
                return redirect()->route('kader.dashboard');
            }
            if ($role === 'bidan') {
                return redirect()->route('bidan.dashboard');
            }

            return redirect()->route('orangtua.anakku');
        }

        return $next($request);
    }
}
