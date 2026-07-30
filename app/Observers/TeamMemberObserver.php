<?php

namespace App\Observers;

class TeamMemberObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'team';
    }

    protected function extraListSuffixes(): array
    {
        return ['all'];
    }
}
