<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CaseStudy
 */
class CaseStudyResource extends JsonResource
{
    use EmbedsSeoMeta;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'context' => $this->context,
            'problem' => $this->problem,
            'constraints' => $this->constraints,
            'solution' => $this->solution,
            'architecture' => $this->architecture,
            'outcomes' => $this->outcomes,
            'evidence' => $this->evidence,
            'features' => $this->features,
            'client_name' => $this->client_name,
            'project_url' => $this->project_url,
            'video_url' => $this->video_url,
            'cover_image' => $this->cover_image,
            'cover_image_alt' => $this->cover_image_alt,
            'gallery' => $this->gallery ?? [],
            'is_featured' => $this->is_featured,
            'service' => new ServiceResource($this->whenLoaded('serviceOffering')),
            'system' => new SystemSummaryResource($this->whenLoaded('system')),
            'industries' => IndustryResource::collection($this->whenLoaded('industries')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }
}
