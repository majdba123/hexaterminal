<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Provenance/audit trail for AI-SEO assistant suggestions (Stage 14).
     * No AI output ever reaches the public site without a row here
     * moving from 'generated' to 'approved' by a human reviewer.
     */
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('model');
            $table->string('prompt_version');
            $table->string('target_type');
            $table->string('target_id');
            $table->string('field')->nullable();
            $table->string('input_hash', 64);
            $table->longText('output')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('estimated_cost_usd', 8, 4)->nullable();
            $table->enum('status', ['pending', 'generated', 'reviewed', 'approved', 'rejected', 'failed'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
