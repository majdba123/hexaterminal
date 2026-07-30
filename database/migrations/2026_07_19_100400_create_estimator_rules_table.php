<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The deterministic cost contributions for one estimator version. Each row
 * is a plain, declarative record -- never stored code. The engine applies
 * them in a fixed order (base/add first, then multiply) so identical
 * answers against the same version always produce identical output.
 *
 * effect:
 *   base    -> establishes the starting band (question_key/option_key may be
 *              null for an always-on base, or matched to a "what are you
 *              building" answer)
 *   add     -> adds amount_min/amount_max (and weeks) when the answer matches
 *   multiply-> scales the running band by `factor` when the answer matches
 *
 * label is the human-readable cost driver shown in the estimate breakdown.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimator_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimator_version_id')->constrained()->cascadeOnDelete();
            $table->string('driver');            // grouping key, e.g. "base","integrations"
            $table->string('question_key')->nullable();
            $table->string('option_key')->nullable();
            $table->string('effect');            // base|add|multiply
            $table->unsignedInteger('amount_min')->nullable(); // base currency
            $table->unsignedInteger('amount_max')->nullable();
            $table->decimal('factor', 5, 3)->nullable();       // for multiply
            $table->unsignedInteger('weeks_min')->nullable();
            $table->unsignedInteger('weeks_max')->nullable();
            $table->integer('complexity_weight')->default(0);
            $table->json('label')->nullable();   // translatable driver label
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['estimator_version_id', 'effect', 'sort_order']);
            $table->index(
                ['estimator_version_id', 'question_key', 'option_key'],
                'est_rules_version_question_option_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimator_rules');
    }
};
