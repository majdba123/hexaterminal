<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsPublicClaims;
use App\Http\Resources\V1\Public\Concerns\ResolvesPublicMediaUrls;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeamMember
 */
class TeamMemberResource extends JsonResource
{
    use EmbedsPublicClaims;
    use ResolvesPublicMediaUrls;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'position' => $this->position,
            'bio' => $this->bio,
            'specialization' => $this->specialization,
            'expertise' => $this->expertise,
            'languages' => $this->languages,
            'location' => $this->location,
            'photo' => $this->publicMediaUrl($this->photo),
            'photo_alt' => $this->photo_alt,
            'github_url' => $this->github_url,
            'linkedin_url' => $this->linkedin_url,
            'is_founder' => $this->is_founder,
            'person_jsonld_eligible' => $this->isPersonJsonLdEligible(),
            'claims' => $this->whenLoaded('claims', fn () => $this->publicClaims(), []),
        ];
    }
}
