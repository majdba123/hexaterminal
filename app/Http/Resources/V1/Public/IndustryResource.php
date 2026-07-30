<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Industry
 */
class IndustryResource extends JsonResource
{
    use EmbedsSeoMeta;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'summary' => $this->summary,
            'description' => $this->description,
            'icon' => $this->icon,
            'cover_image' => $this->cover_image,
            'cover_image_alt' => $this->cover_image_alt,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }
}
