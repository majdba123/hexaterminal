<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Unified inbound lead. One model for every acquisition form, typed by
 * `intent` (start_project / request_quote / book_call / request_demo /
 * general_contact) with intent-conditional validation in the API layer.
 * Status transitions are audit-logged to content_activities. Internal
 * notes/qualification never leave the CMS (see LeadController -- none of
 * these fields are ever serialized publicly).
 */
class ContactLead extends Model
{
    use HasFactory;

    public const INTENTS = [
        'start_project', 'request_quote', 'book_call', 'request_demo',
        'general_contact', 'cost_estimate',
    ];

    public const STATUSES = [
        'new', 'reviewing', 'qualified', 'contacted', 'discovery_scheduled',
        'proposal', 'won', 'lost', 'spam', 'archived',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_DISCOVERY = 'discovery_scheduled';

    public const STATUS_PROPOSAL = 'proposal';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const STATUS_SPAM = 'spam';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'intent', 'name', 'email', 'phone', 'whatsapp', 'company', 'company_size',
        'role_title', 'country', 'project_type', 'industry', 'system_type',
        'budget_range', 'timeline', 'summary', 'pain_points',
        'preferred_contact_method', 'consent',
        'source_page', 'landing_page', 'referrer', 'utm', 'first_touch_at',
        'requested_service_id', 'requested_system_id',
        'locale', 'status', 'priority', 'notes', 'assigned_to', 'follow_up_at',
        'qualification_summary', 'score', 'score_breakdown', 'legacy_contact_id',
    ];

    protected $casts = [
        'utm' => 'array',
        'consent' => 'boolean',
        'first_touch_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'score_breakdown' => 'array',
    ];

    protected $attributes = [
        'intent' => 'start_project',
        'status' => self::STATUS_NEW,
        'priority' => 'normal',
    ];

    protected static function booted(): void
    {
        // Status transitions are the audit-relevant lead events.
        static::updated(function (self $lead) {
            if ($lead->wasChanged('status')) {
                ContentActivity::record($lead, 'status_changed', [
                    'from' => $lead->getOriginal('status'),
                    'to' => $lead->status,
                ]);
            }
        });
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /** Real pipeline (excludes spam + archived). */
    public function scopeActivePipeline(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_SPAM, self::STATUS_ARCHIVED]);
    }

    public function scopeOverdueFollowUp(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_SPAM, self::STATUS_ARCHIVED])
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now());
    }

    public function costEstimate(): HasOne
    {
        return $this->hasOne(CostEstimate::class, 'contact_lead_id');
    }

    public function requestedService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'requested_service_id');
    }

    public function requestedSystem(): BelongsTo
    {
        return $this->belongsTo(System::class, 'requested_system_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ContentActivity::class, 'subject')->latest('created_at');
    }
}
