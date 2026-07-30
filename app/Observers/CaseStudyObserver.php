<?php

namespace App\Observers;

class CaseStudyObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'case-studies';
    }
}
