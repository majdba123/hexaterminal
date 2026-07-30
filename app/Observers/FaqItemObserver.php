<?php

namespace App\Observers;

class FaqItemObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'faqs';
    }

    protected function extraListSuffixes(): array
    {
        return ['all'];
    }
}
