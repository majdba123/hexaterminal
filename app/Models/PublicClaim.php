<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single governed public claim (security/certification/compliance/data
 * residency/encryption/backups/uptime/SLA/incident-response/accessibility/
 * AI-safety/code-ownership/legal-presence/market-presence/customer-outcome/
 * performance-metric/support-commitment/data-processor/subprocessor/
 * privacy/commercial-promise), attachable to any entity via polymorphic
 * `claimable`.
 *
 * FAIL CLOSED: every surface that renders claims publicly (API resources,
 * frontend body, metadata, JSON-LD, FAQs, social previews, search, sitemap
 * fields, AI/SEO prompts, internal-link suggestions, RSS) MUST filter
 * through scopeApprovedForPublication() (or the equivalent isPublic()
 * check). A claim is never exposed if it is unverified, unapproved,
 * confidential, rejected, or expired -- see isPublic().
 */
class PublicClaim extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'security', 'certification', 'compliance', 'data_residency', 'encryption',
        'backups', 'uptime', 'sla', 'incident_response', 'accessibility',
        'ai_safety', 'code_ownership', 'legal_presence', 'market_presence',
        'customer_outcome', 'performance_metric', 'support_commitment',
        'data_processor', 'subprocessor', 'privacy', 'commercial_promise',
    ];

    public const VERIFICATION_STATUSES = ['unverified', 'pending', 'verified', 'rejected'];

    protected $fillable = [
        'claimable_type', 'claimable_id', 'locale', 'category', 'claim_text',
        'evidence', 'verification_status', 'confidential',
        'approved_for_publication', 'approved_by', 'approved_at',
        'expires_at', 'next_review_at', 'review_owner_id', 'internal_notes',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'confidential' => 'boolean',
        'approved_for_publication' => 'boolean',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    /** @return MorphTo<Model, $this> */
    public function claimable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_owner_id');
    }

    /**
     * The one query scope every public-facing consumer must use. Excludes
     * unverified, unapproved, confidential, rejected, and expired claims.
     */
    public function scopeApprovedForPublication(Builder $query): Builder
    {
        return $query
            ->where('verification_status', 'verified')
            ->where('approved_for_publication', true)
            ->where('confidential', false)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /** Instance-level mirror of scopeApprovedForPublication, for single records already fetched. */
    public function isPublic(): bool
    {
        return $this->verification_status === 'verified'
            && $this->approved_for_publication === true
            && $this->confidential === false
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
