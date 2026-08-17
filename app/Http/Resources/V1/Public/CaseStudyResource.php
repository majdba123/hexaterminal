<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Http\Resources\V1\Public\Concerns\ResolvesPublicMediaUrls;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CaseStudy
 */
class CaseStudyResource extends JsonResource
{
    use EmbedsSeoMeta;
    use ResolvesPublicMediaUrls;

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
            'project_classification' => $this->project_classification,
            'project_url' => $this->publicProjectUrl(),
            'video_url' => $this->video_url,
            'cover_image' => $this->publicMediaUrl($this->cover_image),
            'cover_image_alt' => $this->cover_image_alt,
            'gallery' => array_values(array_filter(array_map(
                fn (?string $path): ?string => $this->publicMediaUrl($path),
                $this->gallery ?? [],
            ))),
            'is_featured' => $this->is_featured,
            'service' => new ServiceResource($this->whenLoaded('serviceOffering')),
            'system' => new SystemSummaryResource($this->whenLoaded('system')),
            'industries' => IndustryResource::collection($this->whenLoaded('industries')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }

    private function publicProjectUrl(): ?string
    {
        $url = $this->project_url;
        if (! is_string($url) || $url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return null;
        }

        $host = strtolower(rtrim($host, '.'));

        return $host === 'example.com' || str_ends_with($host, '.example.com')
            ? null
            : $url;
    }
}
