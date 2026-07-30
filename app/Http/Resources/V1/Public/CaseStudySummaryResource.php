<?php

namespace App\Http\Resources\V1\Public;

use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A minimal CaseStudy reference for listings nested inside another
 * resource (e.g. System::case_studies) -- full narrative fields
 * (context/problem/solution/architecture/outcomes/evidence) belong on
 * the dedicated /case-studies/{slug} endpoint, not repeated in every
 * System response.
 *
 * @mixin CaseStudy
 */
class CaseStudySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'client_name' => $this->client_name,
            'cover_image' => $this->cover_image,
            'cover_image_alt' => $this->cover_image_alt,
            'is_featured' => $this->is_featured,
        ];
    }
}
