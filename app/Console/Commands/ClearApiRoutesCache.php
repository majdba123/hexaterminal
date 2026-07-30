<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\LandingController;

class ClearApiRoutesCache extends Command
{
    protected $signature = 'api:clear-routes-cache';
    protected $description = 'Clear the API routes documentation cache';

    public function handle()
    {
        Cache::forget(LandingController::CACHE_KEY);
        $this->info('API routes cache cleared successfully!');
        return 0;
    }
}

