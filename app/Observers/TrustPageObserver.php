<?php

namespace App\Observers;

class TrustPageObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'trust-pages';
    }

    protected function extraListSuffixes(): array
    {
        return ['all'];
    }
}
