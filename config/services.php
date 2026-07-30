<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Cloudflare Turnstile anti-bot verification for public lead forms.
    // OPTIONAL: when the secret is absent the check is skipped entirely
    // (honeypot + throttle still protect the endpoint).
    'turnstile' => [
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    // AI SEO assistant provider (see App\Services\AiSeo). Anthropic only when
    // a key exists; otherwise the assistant reports itself disabled -- it
    // never fakes success.
    'ai_seo' => [
        'provider' => env('AI_SEO_PROVIDER', 'anthropic'),
        'anthropic_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('AI_SEO_MODEL', 'claude-opus-4-8'),
        'timeout' => (int) env('AI_SEO_TIMEOUT', 30),
    ],

    // Public Next.js origin, used to build secure CMS preview links (see
    // App\Filament\Support\PreviewAction). Defaults to the local dev server.
    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ],

];
