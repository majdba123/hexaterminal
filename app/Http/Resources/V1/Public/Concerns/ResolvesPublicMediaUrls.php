<?php

namespace App\Http\Resources\V1\Public\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesPublicMediaUrls
{
    protected function publicMediaUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        // Root-relative assets are intentionally served by the frontend itself
        // (for example /team/yusuf-jojeh.webp). Preserve them as-is so Next.js
        // treats them as local images instead of routing them through a remote
        // image host/optimizer path that can fail with HTTP 400.
        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $url = Storage::disk('public')->url(ltrim($path, '/'));

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $base = rtrim((string) config('app.public_media_url', config('app.url')), '/');

        return $base.'/'.ltrim($url, '/');
    }
}
