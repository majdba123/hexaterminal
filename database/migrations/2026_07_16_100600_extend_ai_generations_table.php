<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Completes AiGeneration provenance for the real generator: target locale,
 * latency, the identifier of the system prompt used, and an error category.
 * `status` widens from enum to string to admit the 'generating' state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();

            $table->string('locale', 5)->nullable()->after('field');
            $table->unsignedInteger('latency_ms')->nullable()->after('estimated_cost_usd');
            $table->string('system_prompt_id', 100)->nullable()->after('prompt_version');
            $table->string('error_category', 50)->nullable()->after('failure_reason');
        });
    }

    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->dropColumn(['locale', 'latency_ms', 'system_prompt_id', 'error_category']);
        });
    }
};
