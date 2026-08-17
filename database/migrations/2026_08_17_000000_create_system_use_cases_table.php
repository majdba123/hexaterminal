<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_use_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->json('title');
            $table->json('actor')->nullable();
            $table->json('summary')->nullable();
            $table->json('workflow')->nullable();
            $table->json('outcome')->nullable();
            $table->string('image')->nullable();
            $table->json('image_alt')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['system_id', 'slug']);
            $table->index(['system_id', 'is_published', 'sort_order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_use_cases');
    }
};
