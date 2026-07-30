<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\RouteMatched;
use App\Http\Controllers\LandingController;

class RouteCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Clear API routes cache when routes are cleared
        Event::listen('routes:cleared', function () {
            Cache::forget(LandingController::CACHE_KEY);
        });
    }
}

