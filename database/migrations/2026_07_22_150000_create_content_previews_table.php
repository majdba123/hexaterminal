<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secure CMS preview tokens for unpublished/unapproved content. Only the
 * SHA-256 hash of the high-entropy token is stored (see
 * App\Services\PreviewTokenService) -- the plain token exists only in the
 * one-time Filament-generated preview URL, never persisted or logged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_previews', function (Blueprint $table) {
            $table->id();
            $table->morphs('previewable');
            $table->string('locale', 5)->default('en');
            $table->string('token_hash')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamps();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_previews');
    }
};
