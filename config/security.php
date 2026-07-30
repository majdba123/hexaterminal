<?php

/*
|--------------------------------------------------------------------------
| Application Security Headers
|--------------------------------------------------------------------------
|
| Single documented source for the security headers applied to every response
| that leaves Laravel (the CMS at /cms, the legacy surfaces, and the API).
| Applied by App\Http\Middleware\SecurityHeaders. The Next.js public frontend
| sets its own baseline headers (frontend/next.config.ts); this file governs
| the Laravel origin.
|
| See docs/security/content-security-policy.md and
| docs/security/production-security-checklist.md.
|
*/

$bool = static function (string $key, bool $default): bool {
    $value = env($key);
    if (is_bool($value)) {
        return $value;
    }
    if (is_string($value)) {
        return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true);
    }

    return $default;
};

return [

    /*
    | Static baseline headers. Safe across Filament, Blade, and JSON responses.
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), browsing-topics=()',
        // Isolate the browsing context; safe for a first-party CMS/API origin.
        'Cross-Origin-Opener-Policy' => 'same-origin',
        // Resources are same-origin by default; media/assets that must be
        // embedded cross-origin are served by the CDN, not this origin.
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ],

    /*
    |----------------------------------------------------------------------
    | Content-Security-Policy
    |----------------------------------------------------------------------
    |
    | Report-Only by DEFAULT. Filament's Alpine.js evaluates expressions with
    | new Function(), which requires 'unsafe-eval'; shipping an *enforcing*
    | policy without it would break the CMS. Until the CMS is served on a
    | separate policy (or a nonce/hash strategy is proven against Filament),
    | we enforce nothing and only collect violation reports.
    |
    | Flip CSP_ENFORCE=true only once the directives below are proven against
    | every Laravel-served surface. See docs/security/content-security-policy.md
    | for the exact remaining blockers.
    */
    'csp' => [
        'enforce' => $bool('CSP_ENFORCE', false),
        'enabled' => $bool('CSP_ENABLED', true),
        // upgrade-insecure-requests is added automatically in production.
        'directives' => [
            'default-src' => ["'self'"],
            // 'unsafe-eval' is required by Filament/Alpine; 'unsafe-inline' by
            // Filament's inline bootstrap. Documented, not silently accepted.
            'script-src' => ["'self'", "'unsafe-eval'", "'unsafe-inline'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
            'font-src' => ["'self'", 'data:'],
            'connect-src' => ["'self'"],
            'media-src' => ["'self'", 'https:'],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'frame-src' => ["'none'"],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Strict-Transport-Security
    |----------------------------------------------------------------------
    |
    | Emitted ONLY on a genuinely secure (HTTPS) production request, so local
    | HTTP testing is never poisoned by a cached HSTS policy. includeSubDomains
    | and preload are opt-in because they are hard to reverse -- do not enable
    | until every subdomain is HTTPS-ready and preload is explicitly approved.
    */
    'hsts' => [
        'enabled' => $bool('HSTS_ENABLED', false),
        'max_age' => (int) env('HSTS_MAX_AGE', 31536000),
        'include_subdomains' => $bool('HSTS_INCLUDE_SUBDOMAINS', false),
        'preload' => $bool('HSTS_PRELOAD', false),
    ],
];
