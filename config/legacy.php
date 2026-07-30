<?php

/*
|--------------------------------------------------------------------------
| Legacy Surface Isolation
|--------------------------------------------------------------------------
|
| Fail-closed toggles for the pre-cutover legacy surfaces that still coexist
| with the Next.js frontend, the Filament CMS (/cms), and the versioned public
| API (/api/v1/public/*):
|
|   public_web  legacy Blade public pages   (routes/web.php: /, /projects, ...)
|   admin       legacy custom admin panel   (routes/web.php: /admin/*)
|   api         legacy /api/* endpoints     (routes/api.php)
|
| Every toggle defaults to FALSE. A missing, empty, or unparseable environment
| value resolves to false, so a staging or production deploy that forgets to
| set these can never accidentally expose a legacy surface. Enable a surface
| only where you are actively doing compatibility work (local, or an explicit
| test configuration).
|
| These are the single source of truth -- route files and middleware read
| config('legacy.*'), never env() directly, so `config:cache` works correctly.
|
| See docs/security/legacy-security-baseline.md and
| docs/migration/legacy-route-retirement-matrix.md.
|
*/

$enabled = static function (string $key): bool {
    $value = env($key);

    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true);
    }

    return false;
};

return [
    'public_web' => $enabled('LEGACY_PUBLIC_WEB_ENABLED'),
    'admin' => $enabled('LEGACY_ADMIN_ENABLED'),
    'api' => $enabled('LEGACY_API_ENABLED'),
];
