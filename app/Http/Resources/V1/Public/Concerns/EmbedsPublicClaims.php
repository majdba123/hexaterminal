<?php

namespace App\Http\Resources\V1\Public\Concerns;

use App\Http\Resources\V1\Public\PublicClaimResource;

/**
 * Embeds only claims that pass PublicClaim::isPublic() (verified, approved,
 * non-confidential, non-expired) under a `claims` key. Callers should
 * eager-load the `claims` relation to avoid N+1 queries; this trait applies
 * the fail-closed filter regardless of whether eager-loading happened.
 */
trait EmbedsPublicClaims
{
    protected function publicClaims(): array
    {
        return $this->claims
            ->filter(fn ($claim) => $claim->isPublic())
            ->values()
            ->map(fn ($claim) => (new PublicClaimResource($claim))->resolve())
            ->all();
    }
}
