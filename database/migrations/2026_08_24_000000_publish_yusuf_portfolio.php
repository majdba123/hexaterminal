<?php

use Database\Seeders\HexaPortfolioSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(HexaPortfolioSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally non-destructive: published CMS content may be edited
        // after deployment and must never be deleted by a rollback.
    }
};
