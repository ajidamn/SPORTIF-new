<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LogSistem;

class LogSensitiveAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log jika response success (200)
        if ($response->getStatusCode() === 200) {
            $user = $request->user();
            $path = $request->path();
            $method = $request->method();

            // Tentukan modul berdasarkan URL path
            $module = 'System';
            if (str_contains($path, 'api/v1/orang')) {
                $module = 'Orang';
            } elseif (str_contains($path, 'api/v1/operators')) {
                $module = 'Operator';
            } elseif (str_contains($path, 'api/v1/export')) {
                $module = 'Export';
            }

            LogSistem::catat(
                'READ',
                $module,
                "Mengakses data sensitif melalui URL: {$path} dengan metode {$method}"
            );
        }

        return $response;
    }
}
