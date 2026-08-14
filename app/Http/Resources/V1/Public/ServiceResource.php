<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    use EmbedsSeoMeta;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'summary' => $this->summary,
            'description' => $this->description,
            'icon' => $this->icon,
            'cover_image' => $this->coverImageUrl(),
            'cover_image_alt' => $this->cover_image_alt,
            'features' => $this->features ?? [],
            'tech_stack' => $this->tech_stack ?? [],
            'related_case_studies' => CaseStudySummaryResource::collection($this->whenLoaded('caseStudies')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }

    private function coverImageUrl(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        return url('/api/storage/'.ltrim($this->cover_image, '/'));
    }
}
