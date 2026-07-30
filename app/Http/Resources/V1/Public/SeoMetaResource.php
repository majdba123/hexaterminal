<?php

namespace App\Http\Resources\V1\Public;

use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SeoMeta
 */
class SeoMetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical_url' => $this->canonical_url,
            'og_image' => $this->og_image,
            'noindex' => $this->noindex,
            'nofollow' => $this->nofollow,
        ];
    }
}
