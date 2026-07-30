<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A unified "System" catalog rather than separate overlapping
     * SaaSProduct/BusinessSystem/ClientSystem models -- differentiated
     * by the `type` enum instead.
     */
    public function up(): void
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('type', [
                'saas_product',
                'business_system',
                'client_system',
                'internal_product',
                'platform',
                'ai_system',
            ]);
            $table->string('category')->nullable();
            $table->json('name');
            $table->json('tagline')->nullable();
            $table->json('short_description')->nullable();
            $table->json('full_description')->nullable();
            $table->json('problem')->nullable();
            $table->json('solution')->nullable();
            $table->json('features')->nullable();
            $table->json('business_outcomes')->nullable();
            $table->json('target_audience')->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('live_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'is_featured', 'sort_order']);
            $table->index('type');
        });

        Schema::create('industry_system', function (Blueprint $table) {
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->primary(['system_id', 'industry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_system');
        Schema::dropIfExists('systems');
    }
};
