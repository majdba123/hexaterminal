<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single computed estimate. Addressed publicly by a high-entropy
 * public_uuid (never a sequential id) so a result can be revisited at
 * /estimate/{uuid} without exposing enumeration or PII. Stores both the
 * authoritative base-currency band and the currency the user saw, so the
 * shared result is stable regardless of later rate edits. Links to a
 * ContactLead only if the user chose to submit contact details.
 *
 * No sensitive free text is stored here beyond the structured answers;
 * the optional project-context note lives on the ContactLead if submitted,
 * not in the anonymous estimate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_estimates', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->foreignId('estimator_version_id')->constrained()->restrictOnDelete();
            $table->string('locale', 5)->default('en');
            $table->string('currency', 3)->default('USD');
            $table->string('session_id', 64)->nullable(); // anonymous, non-PII

            $table->json('answers');

            // Authoritative base-currency band + the displayed currency band.
            $table->unsignedInteger('base_amount_min');
            $table->unsignedInteger('base_amount_max');
            $table->unsignedInteger('amount_min');
            $table->unsignedInteger('amount_max');
            $table->unsignedInteger('timeline_weeks_min');
            $table->unsignedInteger('timeline_weeks_max');
            $table->string('complexity');   // standard|advanced|complex|enterprise
            $table->string('confidence');   // low|medium|high
            $table->json('cost_drivers');
            $table->json('assumptions')->nullable();
            $table->foreignId('recommended_engagement_model_id')->nullable()
                ->constrained('engagement_models')->nullOnDelete();

            $table->foreignId('contact_lead_id')->nullable()
                ->constrained('contact_leads')->nullOnDelete();

            $table->string('status')->default('anonymous');
            // anonymous|lead_created|reviewing|discovery_requested|
            // proposal_requested|converted|expired|spam
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_estimates');
    }
};
