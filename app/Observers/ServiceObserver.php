<?php

namespace App\Observers;

class ServiceObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'services';
    }
}
