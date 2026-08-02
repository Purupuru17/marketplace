<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $account = Auth::guard($guard)->user();

        if ($account && $account->status !== 'active') {
            Auth::guard($guard)->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your account is inactive. Please contact the administrator.');
        }

        return $next($request);
    }
}