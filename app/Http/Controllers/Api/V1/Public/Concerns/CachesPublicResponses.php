<?php

namespace App\Http\Controllers\Api\V1\Public\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Server-side query caching for public v1 endpoints, on top of the
 * `cache.headers` (Cache-Control/ETag) route middleware. Keys are
 * locale-scoped since translatable accessors depend on app()->getLocale().
 * Invalidated by the model observers in app/Observers -- see
 * docs/architecture/nextjs-laravel-boundary.md.
 */
trait CachesPublicResponses
{
    protected function cacheTtlSeconds(): int
    {
        return 300;
    }

    protected function rememberList(string $resource, string $suffix, \Closure $callback)
    {
        $key = "api:v1:public:{$resource}:list:".app()->getLocale().":{$suffix}";

        return Cache::remember($key, $this->cacheTtlSeconds(), $callback);
    }

    protected function rememberShow(string $resource, string $slug, \Closure $callback)
    {
        $key = "api:v1:public:{$resource}:show:".app()->getLocale().":{$slug}";

        return Cache::remember($key, $this->cacheTtlSeconds(), $callback);
    }
}
