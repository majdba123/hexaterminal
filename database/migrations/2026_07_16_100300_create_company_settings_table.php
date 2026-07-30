<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-row company settings managed in Filament (no more company constants
 * scattered across translations/env). Secrets stay environment-only -- this
 * table holds public/marketing facts and operational configuration such as
 * lead recipients and the chosen analytics provider (IDs only, no keys).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->json('company_name')->nullable();
            $table->json('tagline')->nullable();
            $table->json('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('address')->nullable();
            $table->json('social_links')->nullable();   // {linkedin, x, github, ...}
            $table->string('booking_url')->nullable();
            $table->text('lead_recipients')->nullable(); // comma-separated internal emails
            $table->string('default_og_image')->nullable();
            $table->string('analytics_provider', 20)->nullable(); // plausible|umami|null
            $table->string('analytics_site_id')->nullable();
            $table->json('footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
