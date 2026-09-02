<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifies the Next.js frontend to revalidate the pages affected by a CMS
 * content change (see config/revalidation.php and the frontend
 * app/api/revalidate/route.ts endpoint).
 *
 * Design guarantees:
 *  - Fail-safe: every failure is caught and logged; this NEVER throws, so a
 *    slow or unreachable frontend can never break a CMS save.
 *  - No-op unless explicitly enabled AND fully configured (url + secret),
 *    so local, test, and CI runs make no outbound requests.
 *  - The secret is sent as a header and is never logged.
 */
class RevalidationService
{
    /**
     * Frontend content resources that have dedicated list/detail routes.
     * Anything else (testimonials, faqs, etc.) only surfaces on aggregate
     * pages, so it maps to a "home" revalidation.
     */
    private const CONTENT_RESOURCES = [
        'services', 'systems', 'case-studies', 'industries', 'articles', 'team',
    ];

    public function enabled(): bool
    {
        return (bool) config('revalidation.enabled')
            && filled(config('revalidation.url'))
            && filled(config('revalidation.secret'));
    }

    /**
     * Ask the frontend to revalidate the pages for a given resource/slug.
     * Returns true on a successful (2xx) notification, false otherwise.
     */
    public function revalidate(string $resource, ?string $slug = null): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $payload = [
            'resource' => in_array($resource, self::CONTENT_RESOURCES, true) ? $resource : 'home',
            'ts' => time(),
        ];
        if ($slug !== null && in_array($resource, self::CONTENT_RESOURCES, true)) {
            $payload['slug'] = $slug;
        }

        try {
            $response = Http::withHeaders([
                'x-revalidate-secret' => (string) config('revalidation.secret'),
            ])
                ->timeout((int) config('revalidation.timeout', 3))
                ->acceptJson()
                ->post((string) config('revalidation.url'), $payload);

            if ($response->successful()) {
                return true;
            }

            // Log status only -- never the secret or full response body.
            Log::warning('Frontend revalidation returned a non-2xx status', [
                'resource' => $payload['resource'],
                'slug' => $payload['slug'] ?? null,
                'status' => $response->status(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::warning('Frontend revalidation failed', [
                'resource' => $payload['resource'],
                'slug' => $payload['slug'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
