<?php

use Database\Seeders\HexaPortfolioSeeder;
use Database\Seeders\IndustriesSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // Re-apply the public founder profile so Yusuf's photo points to the
        // bundled Next.js asset, then publish/update the expanded industry
        // catalog and its relationships without resetting CMS data.
        app(HexaPortfolioSeeder::class)->run();
        app(IndustriesSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally non-destructive: production content remains CMS-owned.
    }
};
