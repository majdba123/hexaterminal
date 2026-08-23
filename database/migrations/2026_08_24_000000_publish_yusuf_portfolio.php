<?php

use Database\Seeders\HexaPortfolioSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Production upgrades need the new CMS-safe portfolio content, but
        // schema-only test databases must remain empty so feature tests can
        // control their own fixtures deterministically.
        if (app()->environment('testing')) {
            return;
        }

        app(HexaPortfolioSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally non-destructive: published CMS content may be edited
        // after deployment and must never be deleted by a rollback.
    }
};
