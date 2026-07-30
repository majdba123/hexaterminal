<?php

namespace App\Http\Resources\V1\Public;

use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FaqItem
 */
class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
        ];
    }
}
