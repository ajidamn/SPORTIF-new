<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is SuperAdmin and MFA is not set up
        if ($user && $user->hasRole('SuperAdmin') && !$user->google2fa_secret) {
            // Redirect to MFA setup page
            if (!$request->is('admin/mfa/*') && !$request->is('admin/logout')) {
                return redirect()->route('admin.mfa.setup');
            }
        }

        return $next($request);
    }
}
