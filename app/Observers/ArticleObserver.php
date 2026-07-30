<?php

namespace App\Observers;

class ArticleObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'articles';
    }
}
