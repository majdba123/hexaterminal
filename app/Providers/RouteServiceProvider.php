<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Higher-throughput limiter for the versioned public read API
        // (routes/api_v1.php): cached, read-only, and hit heavily during
        // Next.js static generation as well as normal public browsing --
        // the legacy 60/min "api" limiter above is meant for the
        // authenticated/admin surface and is too strict for this traffic
        // shape. Public writes (e.g. lead submission) keep their own
        // explicit throttle:5,1 on the individual route.
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });

        $this->routes(function () {
            // Health/readiness probes: registered without the throttle:api
            // limiter so aggressive load-balancer/uptime polling can never be
            // rate-limited into a false-negative outage. Dependency-free
            // liveness stays cheap; readiness checks DB + cache.
            Route::middleware([SubstituteBindings::class])
                ->prefix('api')
                ->group(base_path('routes/health.php'));

            // Legacy /api/* surface (routes/api.php). Fail-closed behind
            // config/legacy.php: when LEGACY_API_ENABLED is off (the default in
            // staging/production) every route here returns a controlled JSON
            // 404 before its controller runs. The versioned public API
            // (api_v1.php) and health probes below are unaffected.
            Route::middleware(['api', 'legacy:api'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware([
                EnsureFrontendRequestsAreStateful::class,
                'throttle:public-api',
                SubstituteBindings::class,
            ])
                ->prefix('api')
                ->group(base_path('routes/api_v1.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
