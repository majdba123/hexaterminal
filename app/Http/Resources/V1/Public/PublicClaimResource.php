<?php

namespace App\Http\Resources\V1\Public;

use App\Models\PublicClaim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PublicClaim
 */
class PublicClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'category' => $this->category,
            'locale' => $this->locale,
            'claim_text' => $this->claim_text,
        ];
    }
}
