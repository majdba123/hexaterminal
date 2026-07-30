<?php

return [

    /*
    |--------------------------------------------------------------------------
    | On-Demand Frontend Revalidation
    |--------------------------------------------------------------------------
    |
    | When CMS content changes, the app can notify the Next.js frontend to
    | rebuild the affected pages immediately (App\Services\RevalidationService),
    | instead of waiting for the frontend's 5-minute ISR window. This is a
    | server-to-server call authenticated with a shared secret.
    |
    | Disabled by default and a no-op unless BOTH the URL and secret are set,
    | so local/test/CI environments never make outbound calls. Enable it only
    | in deployed environments (staging/production) where the frontend is
    | reachable and REVALIDATION_SECRET matches the frontend's REVALIDATE_SECRET.
    |
    */

    'enabled' => env('REVALIDATION_ENABLED', false),

    // Full URL of the Next.js revalidation route, e.g.
    // https://staging.hexaterminal.com/api/revalidate
    'url' => env('REVALIDATION_URL'),

    // Must equal the frontend's REVALIDATE_SECRET. Never commit a real value.
    'secret' => env('REVALIDATION_SECRET'),

    // HTTP timeout (seconds). Kept short: a slow/unreachable frontend must
    // never delay or fail a CMS save.
    'timeout' => (int) env('REVALIDATION_TIMEOUT', 3),

];
