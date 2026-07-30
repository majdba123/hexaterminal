<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement models are the commercial "shapes" a project can take
 * (Discovery Sprint, MVP, Custom System, Dedicated Team, Modernization,
 * Support). They carry the marketing/structure content and a pricing
 * DISPLAY mode; the actual numbers (if any) live in approval-gated
 * pricing_profiles, so a model can exist and be published while its
 * price stays hidden until a founder approves it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_models', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('buyer_fit')->nullable();       // "suitable buyer"
            $table->json('typical_scope')->nullable();
            $table->json('deliverables')->nullable();     // translatable list
            $table->json('included_items')->nullable();
            $table->json('excluded_items')->nullable();
            $table->json('indicative_duration')->nullable(); // e.g. "4-6 weeks"
            $table->string('cta_label')->nullable();
            $table->string('cta_intent')->default('request_quote'); // maps to ContactLead intent

            // How this model's price is presented, independent of whether a
            // number is approved yet: hidden|request_quote|starting_from|
            // indicative_range|fixed_package.
            $table->string('pricing_display_mode')->default('request_quote');
            $table->string('billing_model')->default('fixed_project');

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_models');
    }
};
