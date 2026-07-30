<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * New content-model table for commercial service offerings
     * (SaaS/CRM/ERP/AI/backend/automation etc). Deliberately not named
     * `services` to avoid colliding with the legacy `services` table
     * (which has a real FK from `projects.service_id` and stays
     * untouched until the legacy frontend is retired).
     */
    public function up(): void
    {
        Schema::create('service_offerings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('tagline')->nullable();
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('features')->nullable();
            $table->json('tech_stack')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_offerings');
    }
};
