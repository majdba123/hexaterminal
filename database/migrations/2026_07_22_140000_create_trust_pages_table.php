<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trust Page infrastructure -- one coherent model for every governance-
 * sensitive public page (security, process, accessibility, technology,
 * responsible-ai, engineering-standards, support, code/IP ownership, data
 * privacy, company/delivery) instead of seven hardcoded page systems.
 *
 * Public exposure is fail-closed: App\Models\TrustPage::scopePubliclyVisible
 * additionally requires non-empty EN content and, for `page_type`s in
 * TrustPage::TYPES_REQUIRING_FOUNDER_APPROVAL /
 * TYPES_REQUIRING_LEGAL_APPROVAL / TYPES_REQUIRING_SECURITY_APPROVAL, the
 * matching approval flag. Editorial workflow columns (status, created_by,
 * updated_by, approved_by, published_by, approved_at) mirror the pattern in
 * 2026_07_16_100100_add_editorial_workflow_to_content_tables.php, inlined
 * here since this is a brand-new table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('page_type', 40);
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('sections')->nullable();
            $table->json('faqs')->nullable();
            $table->json('cta')->nullable();

            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('founder_approved')->default(false);
            $table->boolean('legal_approved')->default(false);
            $table->boolean('security_approved')->default(false);

            $table->boolean('is_published')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('noindex')->default(true);
            $table->boolean('show_in_nav')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['page_type']);
            $table->index(['is_published', 'sort_order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_pages');
    }
};
