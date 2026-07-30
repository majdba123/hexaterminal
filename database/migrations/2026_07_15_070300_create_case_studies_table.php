<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidates the legacy Projects + Imag_Progect (images) +
     * Fetures_Project (features) tables into one outcome-focused entity.
     * Legacy tables are kept as-is; a data-migration command copies
     * existing rows into this table (see docs/migration/legacy-to-nextjs.md).
     */
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('context')->nullable();
            $table->json('problem')->nullable();
            $table->json('constraints')->nullable();
            $table->json('solution')->nullable();
            $table->json('architecture')->nullable();
            $table->json('outcomes')->nullable();
            $table->json('evidence')->nullable();
            $table->json('features')->nullable();
            $table->string('client_name')->nullable();
            $table->string('project_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->foreignId('service_offering_id')->nullable()->constrained('service_offerings')->nullOnDelete();
            $table->foreignId('system_id')->nullable()->constrained('systems')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Traceability back to the legacy `projects` row this was migrated from, if any.
            $table->unsignedBigInteger('legacy_project_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['is_published', 'is_featured', 'sort_order']);
        });

        Schema::create('case_study_industry', function (Blueprint $table) {
            $table->foreignId('case_study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->primary(['case_study_id', 'industry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_study_industry');
        Schema::dropIfExists('case_studies');
    }
};
