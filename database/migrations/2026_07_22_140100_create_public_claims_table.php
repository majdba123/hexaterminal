<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Governed public claims (security/compliance/metrics/testimonials/etc)
 * attachable to any entity (TrustPage, TeamMember, Service, System, ...) via
 * a polymorphic (claimable_type, claimable_id) pair. Public rendering must
 * use App\Models\PublicClaim::scopeApprovedForPublication -- see that
 * model's docblock for the full fail-closed contract.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_claims', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('claimable');
            $table->string('locale', 5)->default('en');
            $table->string('category', 40);
            $table->text('claim_text');
            $table->text('evidence')->nullable();
            $table->string('verification_status', 20)->default('unverified');
            $table->boolean('confidential')->default(false);
            $table->boolean('approved_for_publication')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->foreignId('review_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category']);
            $table->index(['verification_status', 'approved_for_publication']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_claims');
    }
};
