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

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return url(Storage::disk('public')->url(ltrim($path, '/')));
    }
}
