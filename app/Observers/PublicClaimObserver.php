<?php

namespace App\Observers;

use App\Models\PublicClaim;
use App\Models\TeamMember;
use App\Models\TrustPage;
use Illuminate\Support\Facades\Cache;

/**
 * Claims are embedded inside their claimable's public API response, so a
 * claim change must invalidate that owner's cache -- not a `public_claims`
 * cache of its own (claims have no standalone public endpoint).
 */
class PublicClaimObserver
{
    private const LOCALES = ['en', 'ar'];

    private const RESOURCE_BY_TYPE = [
        TrustPage::class => 'trust-pages',
        TeamMember::class => 'team',
    ];

    public function saved(PublicClaim $claim): void
    {
        $this->clear($claim);
    }

    public function deleted(PublicClaim $claim): void
    {
        $this->clear($claim);
    }

    private function clear(PublicClaim $claim): void
    {
        $resource = self::RESOURCE_BY_TYPE[$claim->claimable_type] ?? null;
        if (! $resource) {
            return;
        }

        $owner = $claim->claimable()->withoutGlobalScopes()->first();
        if (! $owner || ! isset($owner->slug)) {
            return;
        }

        foreach (self::LOCALES as $locale) {
            Cache::forget("api:v1:public:{$resource}:show:{$locale}:{$owner->slug}");
            Cache::forget("api:v1:public:{$resource}:list:{$locale}:all");
        }
    }
}
