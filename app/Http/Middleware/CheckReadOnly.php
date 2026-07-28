<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReadOnly
{
    /**
     * Role dengan prefix "Kepala" atau "Ketua" bersifat read-only.
     * Hanya method GET yang diizinkan untuk role ini.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $roles = $request->user()->getRoleNames();
            
            $isReadOnly = $roles->contains(function ($role) {
                return str_starts_with($role, 'Kepala') || str_starts_with($role, 'Ketua');
            });

            if ($isReadOnly && !$request->isMethod('GET')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akses ditolak: Akun Anda bersifat read-only.',
                    ], 403);
                }
                abort(403, 'Akses ditolak: Akun Anda bersifat read-only.');
            }
        }

        return $next($request);
    }
}
