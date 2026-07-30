<?php

namespace App\Http\Resources\V1\Public\Concerns;

use App\Http\Resources\V1\Public\SeoMetaResource;

/**
 * Embeds the optional polymorphic SeoMeta override under a `seo` key.
 * Every model exposing this must load its `seo` relation (or it will be
 * lazy-loaded per record -- callers should eager-load `with('seo')`).
 */
trait EmbedsSeoMeta
{
    protected function seoMeta(): ?array
    {
        return $this->seo ? (new SeoMetaResource($this->seo))->resolve() : null;
    }
}
