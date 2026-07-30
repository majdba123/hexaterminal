<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsPublicClaims;
use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Models\TrustPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TrustPage
 */
class TrustPageResource extends JsonResource
{
    use EmbedsPublicClaims;
    use EmbedsSeoMeta;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'page_type' => $this->page_type,
            'title' => $this->title,
            'summary' => $this->summary,
            'sections' => $this->sections,
            'faqs' => $this->faqs,
            'cta' => $this->cta,
            'show_in_nav' => $this->show_in_nav,
            'show_in_footer' => $this->show_in_footer,
            'noindex' => $this->noindex,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'next_review_at' => $this->next_review_at?->toIso8601String(),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer?->name),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
            'claims' => $this->whenLoaded('claims', fn () => $this->publicClaims(), []),
        ];
    }
}
