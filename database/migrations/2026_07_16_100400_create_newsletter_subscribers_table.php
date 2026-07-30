<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal newsletter-interest layer -- NOT an email marketing platform.
 * Status supports a future double-opt-in flow (pending -> active) but the
 * current public endpoint records single-opt-in 'active' with a consent
 * timestamp. Campaigns are out of scope; export feeds a future provider.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('en');
            $table->string('status', 20)->default('active'); // pending|active|unsubscribed
            $table->timestamp('consent_at')->nullable();
            $table->string('source_page')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
