<?php

namespace App\Console\Commands;

use App\Services\ServiceSeedImageSynchronizer;
use Illuminate\Console\Command;

class SyncServiceSeedImages extends Command
{
    protected $signature = 'hexa:sync-service-images';

    protected $description = 'Synchronize approved seeded service images to the public disk without changing database content';

    public function handle(ServiceSeedImageSynchronizer $images): int
    {
        foreach ($images->syncAll() as $path) {
            $this->line($path);
        }

        return self::SUCCESS;
    }
}
