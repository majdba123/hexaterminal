<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    | Cross-origin browser access is restricted to explicitly configured
    | origins. Set CORS_ALLOWED_ORIGINS in each environment to a comma-
    | separated allow-list of the exact frontend origins, e.g.
    |   CORS_ALLOWED_ORIGINS=https://staging.hexaterminal.com
    |
    | The default "*" is for LOCAL DEV ONLY. Staging and production MUST set an
    | explicit list -- never ship "*" for an authenticated surface. (Note: the
    | Next.js frontend calls the API server-side and via its own /api proxy, so
    | it needs no cross-origin grant; this list is for any direct browser use.)
    */
    'allowed_origins' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*')))
    )),

    // Optional regex patterns (e.g. staging preview subdomains). Comma-separated.
    'allowed_origins_patterns' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS_PATTERNS', '')))
    )),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => (int) env('CORS_MAX_AGE', 0),

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),

];
