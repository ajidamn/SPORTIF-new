<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi(); // Sanctum stateful sessions
        $middleware->redirectGuestsTo('/admin/login'); // ISO 27001 — Redirect ke halaman login admin

        // ISO 27001 — Security Headers pada semua response
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Security: Single-Session Enforcement & MFA Check
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\RequireMfa::class,
        ]);

        // Middleware Aliases
        $middleware->alias([
            'role'     => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'readonly' => \App\Http\Middleware\CheckReadOnly::class,
            'log-sensitive' => \App\Http\Middleware\LogSensitiveAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ISO 27001 — Jangan expose stack trace di API (production)
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Terlalu banyak permintaan. Coba lagi nanti.',
                ], 429);
            }
        });

        // Generic error for API in production
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && !config('app.debug')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'message' => $status === 404
                        ? 'Resource tidak ditemukan.'
                        : ($status === 403 ? 'Akses ditolak.' : 'Terjadi kesalahan server.'),
                ], $status);
            }
        });
    })->create();
