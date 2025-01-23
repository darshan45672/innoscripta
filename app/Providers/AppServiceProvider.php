<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Define rate limiting configuration for the 'api' route.
         *
         * This rate limiter allows a maximum of 5 requests per minute per user or IP address.
         * If the limit is exceeded, a JSON response with a 429 status code and an error message
         * will be returned.
         *
         * @param \Illuminate\Http\Request $request The incoming HTTP request.
         * @return \Illuminate\Cache\RateLimiting\Limit The rate limit configuration.
         */
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'status_code' => 429,
                        'message' => 'Too many requests. Please try again later.'
                    ], 429);
                });
        });
    }
}
