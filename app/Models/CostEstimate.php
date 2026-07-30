<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A computed estimate, addressed publicly by high-entropy public_uuid.
 * Stores the authoritative base-currency band plus the displayed-currency
 * band so a shared result stays stable. Links to a ContactLead only if the
 * user chose to submit contact details. Result pages are noindex and never
 * enter sitemap/search/RSS.
 */
class CostEstimate extends Model
{
    use HasFactory;

    public const STATUSES = [
        'anonymous', 'lead_created', 'reviewing', 'discovery_requested',
        'proposal_requested', 'converted', 'expired', 'spam',
    ];

    protected $fillable = [
        'public_uuid', 'estimator_version_id', 'locale', 'currency', 'session_id',
        'answers', 'base_amount_min', 'base_amount_max', 'amount_min', 'amount_max',
        'timeline_weeks_min', 'timeline_weeks_max', 'complexity', 'confidence',
        'cost_drivers', 'assumptions', 'recommended_engagement_model_id',
        'contact_lead_id', 'status', 'expires_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'cost_drivers' => 'array',
        'assumptions' => 'array',
        'base_amount_min' => 'integer',
        'base_amount_max' => 'integer',
        'amount_min' => 'integer',
        'amount_max' => 'integer',
        'timeline_weeks_min' => 'integer',
        'timeline_weeks_max' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'anonymous',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $estimate) {
            if (blank($estimate->public_uuid)) {
                $estimate->public_uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_uuid';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** @return BelongsTo<EstimatorVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(EstimatorVersion::class, 'estimator_version_id');
    }

    /** @return BelongsTo<EngagementModel, $this> */
    public function recommendedEngagementModel(): BelongsTo
    {
        return $this->belongsTo(EngagementModel::class, 'recommended_engagement_model_id');
    }

    /** @return BelongsTo<ContactLead, $this> */
    public function contactLead(): BelongsTo
    {
        return $this->belongsTo(ContactLead::class, 'contact_lead_id');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
