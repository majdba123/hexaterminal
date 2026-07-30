<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fail-closed gate for a legacy surface (public web, admin, or API).
 *
 * Applied as `legacy:<surface>` where <surface> is one of the keys in
 * config/legacy.php (`public_web`, `admin`, `api`).
 *
 *  - Surface DISABLED (the default everywhere): the request never reaches the
 *    legacy controller. It gets a controlled 404 -- a stable JSON contract for
 *    API/JSON requests, or the standard 404 view for web -- so no legacy Blade
 *    page renders and no route internals leak.
 *  - Surface ENABLED (explicit compatibility mode): the request proceeds, and
 *    the response is tagged `X-Robots-Tag: noindex, nofollow` so a legacy page
 *    served for compatibility can never be indexed or compete with the
 *    canonical Next.js page.
 *
 * Reversible by design: flip the env flag; no routes are deleted. Because the
 * decision is read from config() at request time, it is deterministic and
 * fully testable via config() overrides.
 */
class LegacySurface
{
    public function handle(Request $request, Closure $next, string $surface): Response
    {
        $enabled = (bool) config("legacy.{$surface}", false);

        if (! $enabled) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'This endpoint has been retired.',
                ], 404);
            }

            abort(404);
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
