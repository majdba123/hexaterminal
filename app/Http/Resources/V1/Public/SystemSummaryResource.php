<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\ResolvesPublicMediaUrls;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A minimal System reference for back-links (e.g. CaseStudy::system).
 * Deliberately does not include `case_studies` -- SystemResource does,
 * and nesting the full resource both directions would recurse forever.
 *
 * @mixin System
 */
class SystemSummaryResource extends JsonResource
{
    use ResolvesPublicMediaUrls;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'type' => $this->type,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'cover_image' => $this->publicMediaUrl($this->cover_image),
            'cover_image_alt' => $this->cover_image_alt,
        ];
    }
}
