<?php

namespace App\Observers;

class SystemObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'systems';
    }
}
