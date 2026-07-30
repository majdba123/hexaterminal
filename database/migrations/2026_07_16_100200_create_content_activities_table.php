<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight audit trail: who did what to which record, when. Written by
 * App\Models\Concerns\HasEditorialWorkflow (content types), the ContactLead
 * model (status transitions), and the AI SEO approval flow. Deliberately
 * append-only and value-light (attribute NAMES, not contents) so it can
 * never leak drafts or lead PII into the log table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_activities', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40); // created|updated|status_changed|deleted|ai_approved|...
            $table->json('details')->nullable();
            $table->timestamp('created_at');
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_activities');
    }
};
