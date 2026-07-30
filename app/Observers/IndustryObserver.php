<?php

namespace App\Observers;

class IndustryObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'industries';
    }

    protected function extraListSuffixes(): array
    {
        return ['all'];
    }
}
