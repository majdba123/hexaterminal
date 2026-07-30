<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A question inside one estimator version. Options are stored inline as a
 * JSON list of {key, label:{en,ar}} value objects rather than a separate
 * table -- options have no independent lifecycle, relations, or identity
 * beyond their question, so a dedicated table would be needless explosion
 * (see docs/architecture/pricing-estimator-architecture.md).
 *
 * Branching is deterministic and data-driven, never code: `show_if` is a
 * simple {"question": key, "in": [option_keys]} object evaluated by the
 * frontend and re-validated server-side. No eval, ever.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimator_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimator_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->unsignedInteger('step')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('type')->default('single_select'); // single_select|multi_select
            $table->json('prompt');
            $table->json('help_text')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('show_if')->nullable();
            $table->json('options'); // [{key, label:{en,ar}}]
            $table->timestamps();

            $table->unique(['estimator_version_id', 'key']);
            $table->index(['estimator_version_id', 'step', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimator_questions');
    }
};
