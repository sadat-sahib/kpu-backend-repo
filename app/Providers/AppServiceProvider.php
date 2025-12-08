<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    // Configure the rate limiters for the application.
    
    protected function configureRateLimiting(): void
    {
        // Public high-traffic routes: 60 requests per minute per IP
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Authentication routes: stricter limit (e.g., 10 per minute per IP)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}