<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approval-gated numeric price bands, attached polymorphically to an
 * EngagementModel or a Service (priceable). One row per currency, so a
 * single engagement model can carry USD/AED/SAR bands. A price NUMBER is
 * only ever shown publicly when approved_for_publication = true AND the
 * effective_date has passed -- the public layer fails closed to
 * "request a scoped estimate" otherwise. No live FX: each band is a
 * founder-approved amount in its own currency, never a converted value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_profiles', function (Blueprint $table) {
            $table->id();
            $table->morphs('priceable'); // engagement_model | service

            $table->string('currency', 3); // USD | AED | SAR
            $table->unsignedInteger('min_amount')->nullable();
            $table->unsignedInteger('max_amount')->nullable();
            $table->string('price_unit')->default('project'); // project|month|sprint|day
            $table->string('billing_model')->default('fixed_project');
            $table->json('display_label')->nullable(); // translatable "Starting from"
            $table->json('assumptions')->nullable();    // translatable
            $table->json('exclusions')->nullable();     // translatable
            $table->json('disclaimer')->nullable();     // translatable

            // Founder-approval gate -- fail closed.
            $table->boolean('approved_for_publication')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['priceable_type', 'priceable_id', 'currency']);
            $table->index(['approved_for_publication', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_profiles');
    }
};
