<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
 * Responses expose only safe operational state. No credentials, connection
 * strings, environment names, version identifiers, or exception traces are
 * ever returned to callers.
 */
class HealthController extends Controller
{
    /** Liveness: process is up. Cheap, dependency-free, always 200 when reachable. */
    public function live(): JsonResponse
    {
        return $this->healthResponse([
            'status' => 'ok',
            'service' => 'hexaterminal-backend',
        ], 200);
    }

    /** Readiness: dependencies reachable. 200 only when every check passes. */
    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = ! in_array('failed', $checks, true);

        return $this->healthResponse([
            'status' => $healthy ? 'ready' : 'not_ready',
            'service' => 'hexaterminal-backend',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->select('select 1');

            return 'ok';
        } catch (Throwable $e) {
            Log::warning('Backend readiness database check failed.', [
                'exception' => $e,
            ]);

            return 'failed';
        }
    }

    private function checkCache(): string
    {
        try {
            $token = 'health:'.bin2hex(random_bytes(8));
            Cache::put($token, 1, 10);
            $ok = Cache::get($token) === 1;
            Cache::forget($token);

            return $ok ? 'ok' : 'failed';
        } catch (Throwable $e) {
            Log::warning('Backend readiness cache check failed.', [
                'exception' => $e,
            ]);

            return 'failed';
        }
    }

    private function healthResponse(array $payload, int $status): JsonResponse
    {
        return response()->json($payload, $status, [
            'Cache-Control' => 'no-store',
        ]);
    }
}
