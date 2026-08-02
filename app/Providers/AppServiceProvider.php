<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by($request->input('email').'|'.$request->ip()));

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)
            ->by($request->ip()));

        RateLimiter::for('payment', fn (Request $request) => Limit::perMinute(10)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('action', fn (Request $request) => Limit::perMinute(30)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }
}
