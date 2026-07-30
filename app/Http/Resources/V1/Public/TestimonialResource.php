<?php

namespace App\Http\Resources\V1\Public;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Testimonial
 */
class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'author_name' => $this->author_name,
            'author_title' => $this->author_title,
            'company' => $this->company,
            'company_logo' => $this->company_logo,
            'content' => $this->content,
            'rating' => $this->rating,
            'given_at' => $this->given_at?->toDateString(),
        ];
    }
}
