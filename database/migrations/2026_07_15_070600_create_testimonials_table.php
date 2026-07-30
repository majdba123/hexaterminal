<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evolves the legacy `reviews` table (kept as-is) into a proper
     * moderated testimonial entity for the new site.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->string('company')->nullable();
            $table->string('company_logo')->nullable();
            $table->json('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->date('given_at')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('legacy_review_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['is_approved', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
