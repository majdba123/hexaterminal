<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Operational health checks for load balancers, uptime monitors, and the
 * deployment pipeline. Deliberately split into liveness and readiness:
 *
 *  - liveness  (`/api/health`)        the PHP process is up and can answer.
 *                                     Never touches external dependencies, so
 *                                     a slow/broken DB does not cause the
 *                                     orchestrator to kill an otherwise
 *                                     healthy container.
 *  - readiness (`/api/health/ready`)  the app can actually serve traffic:
 *                                     database and cache are reachable. A load
 *                                     balancer should only route to instances
 *                                     that pass this.
 *
 * Responses expose only safe operational state -- a boolean per dependency,
 * the environment name, and the build/commit SHA (from APP_VERSION, if set).
 * No credentials, connection strings, or exception traces are ever returned;
 * failures are reduced to `"error"` and logged server-side instead.
 */
class HealthController extends Controller
{
    /** Liveness: process is up. Cheap, dependency-free, always 200 when reachable. */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'hexa-terminal-api',
            'environment' => app()->environment(),
            'version' => (string) config('app.version', 'unknown'),
            'time' => now()->toIso8601String(),
        ]);
    }

    /** Readiness: dependencies reachable. 200 only when every check passes. */
    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'environment' => app()->environment(),
            'version' => (string) config('app.version', 'unknown'),
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            $token = 'health:'.bin2hex(random_bytes(8));
            Cache::put($token, 1, 10);
            $ok = Cache::get($token) === 1;
            Cache::forget($token);

            return $ok;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
