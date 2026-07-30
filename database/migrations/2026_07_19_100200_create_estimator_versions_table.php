<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A versioned, immutable-after-activation snapshot of the estimator's
 * questions and rules. Exactly one version may be active at a time; new
 * estimates always run against the active version, and every CostEstimate
 * records the version it used so historical results stay reproducible even
 * after a newer version is activated.
 *
 * currency_rates are FIXED pegs, not live FX: AED and SAR are both pegged
 * to the USD by their central banks (AED 3.6725, SAR 3.75), so presenting
 * an indicative USD estimate in those currencies is a stable, documented
 * constant -- never a volatile market rate. USD is the authoritative base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimator_versions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // e.g. "v1"
            $table->string('label');
            $table->string('status')->default('draft'); // draft|active|archived
            $table->boolean('is_active')->default(false);
            $table->string('base_currency', 3)->default('USD');
            $table->json('currency_rates')->nullable(); // {"USD":1,"AED":3.6725,"SAR":3.75}
            $table->unsignedInteger('floor_min')->default(0);   // guardrail, base currency
            $table->unsignedInteger('ceiling_max')->default(1000000);
            $table->text('notes')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimator_versions');
    }
};
