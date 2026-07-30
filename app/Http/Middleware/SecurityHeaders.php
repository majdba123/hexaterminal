<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the documented production security-header baseline to every response
 * that leaves the Laravel origin (CMS, legacy surfaces, API).
 *
 * Policy lives in config/security.php. Behaviour:
 *  - Static baseline headers (nosniff, Referrer-Policy, X-Frame-Options,
 *    Permissions-Policy, COOP, CORP) always applied.
 *  - CSP applied as Report-Only by default (Filament/Alpine needs
 *    'unsafe-eval'); enforcing mode is opt-in via CSP_ENFORCE. See
 *    docs/security/content-security-policy.md.
 *  - HSTS emitted only on a secure production request, and only when enabled.
 *
 * Existing headers are never overwritten, so a downstream layer (reverse proxy)
 * or a route that sets its own header wins and we avoid contradictory duplicates.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        foreach ((array) config('security.headers', []) as $name => $value) {
            if (! $headers->has($name)) {
                $headers->set($name, $value);
            }
        }

        $this->applyCsp($request, $response);
        $this->applyHsts($request, $response);

        return $response;
    }

    private function applyCsp(Request $request, Response $response): void
    {
        $csp = (array) config('security.csp', []);
        if (empty($csp['enabled'])) {
            return;
        }

        $directives = (array) ($csp['directives'] ?? []);
        $parts = [];
        foreach ($directives as $directive => $sources) {
            $parts[] = trim($directive.' '.implode(' ', (array) $sources));
        }

        if (app()->environment('production') && $request->isSecure()) {
            $parts[] = 'upgrade-insecure-requests';
        }

        if ($parts === []) {
            return;
        }

        $value = implode('; ', $parts);
        $header = ! empty($csp['enforce'])
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        if (! $response->headers->has($header)) {
            $response->headers->set($header, $value);
        }
    }

    private function applyHsts(Request $request, Response $response): void
    {
        $hsts = (array) config('security.hsts', []);

        if (empty($hsts['enabled'])) {
            return;
        }

        // Never emit HSTS on a plaintext or non-production request -- a cached
        // policy would break local HTTP development.
        if (! $request->isSecure() || ! app()->environment('production')) {
            return;
        }

        $value = 'max-age='.(int) ($hsts['max_age'] ?? 31536000);
        if (! empty($hsts['include_subdomains'])) {
            $value .= '; includeSubDomains';
        }
        if (! empty($hsts['preload'])) {
            $value .= '; preload';
        }

        $response->headers->set('Strict-Transport-Security', $value);
    }
}
