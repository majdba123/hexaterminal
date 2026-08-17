<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\ResolvesPublicMediaUrls;
use App\Models\SystemUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SystemUseCase
 */
class SystemUseCaseResource extends JsonResource
{
    use ResolvesPublicMediaUrls;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'actor' => $this->actor,
            'summary' => $this->summary,
            'workflow' => $this->workflow,
            'outcome' => $this->outcome,
            'image' => $this->publicMediaUrl($this->image),
            'image_alt' => $this->image_alt,
            'sort_order' => $this->sort_order,
        ];
    }
}
