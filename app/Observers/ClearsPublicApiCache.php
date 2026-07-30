<?php

namespace App\Observers;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Invalidates the public v1 API cache (App\Http\Controllers\Api\V1\Public)
 * when CMS content changes. The cache driver is `file` (no tag support),
 * so this targets the keys that matter most rather than attempting
 * exhaustive invalidation of every paginated/filtered list permutation:
 *
 *  - the model's own `show` cache, for every supported locale
 *  - the homepage aggregate (it summarizes most content types)
 *  - any fixed "list:all" style keys the subclass declares (used by the
 *    small number of unpaginated collection endpoints: industries, team,
 *    faqs, testimonials)
 *
 * Paginated/filtered list caches (services, systems, case-studies,
 * articles) are left to expire on their 5-minute TTL -- a deliberate,
 * documented trade-off (see docs/architecture/nextjs-laravel-boundary.md)
 * rather than hand-maintaining every page/filter key combination.
 */
abstract class ClearsPublicApiCache
{
    private const LOCALES = ['en', 'ar'];

    abstract protected function resourceName(): string;

    /**
     * @return list<string> extra fixed suffixes to forget under this
     *                      resource's `list` cache (e.g. ['all']).
     */
    protected function extraListSuffixes(): array
    {
        return [];
    }

    public function saved(Model $model): void
    {
        $this->clear($model);
    }

    public function deleted(Model $model): void
    {
        $this->clear($model);
    }

    private function clear(Model $model): void
    {
        $resource = $this->resourceName();

        foreach (self::LOCALES as $locale) {
            if (isset($model->slug)) {
                Cache::forget("api:v1:public:{$resource}:show:{$locale}:{$model->slug}");
            }

            foreach ($this->extraListSuffixes() as $suffix) {
                Cache::forget("api:v1:public:{$resource}:list:{$locale}:{$suffix}");
            }

            Cache::forget("api:v1:public:home:list:{$locale}:all");
        }

        $this->requestFrontendRevalidation($resource, $model);
    }

    /**
     * Best-effort on-demand revalidation of the Next.js frontend. Fully
     * decoupled from cache clearing and completely fail-safe: it is a no-op
     * unless revalidation is enabled + configured, and any error is swallowed
     * so a CMS save can never fail because the frontend was slow/unreachable.
     */
    private function requestFrontendRevalidation(string $resource, Model $model): void
    {
        try {
            $service = app(RevalidationService::class);
            if (! $service->enabled()) {
                return;
            }

            $slug = isset($model->slug) ? (string) $model->slug : null;
            $service->revalidate($resource, $slug);
        } catch (Throwable) {
            // Never allow revalidation wiring to affect the write path.
        }
    }
}
