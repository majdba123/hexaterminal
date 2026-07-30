<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single secure preview link for one CMS record. Never store or expose
 * the plain token -- only `token_hash` (see PreviewTokenService::mint()).
 * Public/API-facing code MUST use scopeActive() before resolving a token;
 * an expired or revoked row must behave exactly like "not found".
 */
class ContentPreview extends Model
{
    protected $fillable = [
        'previewable_type', 'previewable_id', 'locale', 'token_hash',
        'created_by', 'expires_at', 'revoked_at', 'last_accessed_at', 'access_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    /** @return MorphTo<Model, $this> */
    public function previewable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && $this->expires_at->isFuture();
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public function recordAccess(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }
}
