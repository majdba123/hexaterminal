<?php

use Database\Seeders\HexaPortfolioSeeder;
use Database\Seeders\RakezContentSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // Re-apply the portfolio package so systems created by the first
        // production migration receive the canonical is_published flag.
        app(HexaPortfolioSeeder::class)->run();
        app(RakezContentSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally non-destructive. These records are CMS-managed after
        // publication and a rollback must never delete editorial changes.
    }
};
