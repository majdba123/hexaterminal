<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Http\Resources\V1\Public\Concerns\ResolvesPublicMediaUrls;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin System
 */
class SystemResource extends JsonResource
{
    use EmbedsSeoMeta;
    use ResolvesPublicMediaUrls;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'type' => $this->type,
            'category' => $this->category,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'problem' => $this->problem,
            'solution' => $this->solution,
            'features' => $this->features,
            'business_outcomes' => $this->business_outcomes,
            'target_audience' => $this->target_audience,
            'tech_stack' => $this->tech_stack ?? [],
            'cover_image' => $this->publicMediaUrl($this->cover_image),
            'cover_image_alt' => $this->cover_image_alt,
            'gallery' => array_values(array_filter(array_map(
                fn (?string $path): ?string => $this->publicMediaUrl($path),
                $this->gallery ?? [],
            ))),
            'demo_url' => $this->demo_url,
            'live_url' => $this->live_url,
            'is_featured' => $this->is_featured,
            'industries' => IndustryResource::collection($this->whenLoaded('industries')),
            'use_cases' => SystemUseCaseResource::collection($this->whenLoaded('useCases')),
            // Lightweight CaseStudy summaries -- avoids nesting the full
            // CaseStudyResource (which itself embeds a System) to prevent
            // System <-> CaseStudy recursive nesting.
            'case_studies' => CaseStudySummaryResource::collection($this->whenLoaded('caseStudies')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }
}
