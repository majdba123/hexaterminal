<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

/**
 * An approval-gated numeric price band in a single currency, attached to an
 * EngagementModel or Service. A number is publishable only when
 * approved_for_publication = true AND the effective_date has arrived --
 * scopeApprovedForDisplay() is the single source of that truth, and the
 * public API/frontend must never bypass it. No live FX: each band is a
 * founder-approved amount in its own currency.
 */
class PricingProfile extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'priceable_type', 'priceable_id', 'currency', 'min_amount', 'max_amount',
        'price_unit', 'billing_model', 'display_label', 'assumptions', 'exclusions',
        'disclaimer', 'approved_for_publication', 'approved_by', 'approved_at',
        'effective_date', 'review_date', 'sort_order',
    ];

    public array $translatable = ['display_label', 'assumptions', 'exclusions', 'disclaimer'];

    protected $casts = [
        'min_amount' => 'integer',
        'max_amount' => 'integer',
        'approved_for_publication' => 'boolean',
        'approved_at' => 'datetime',
        'effective_date' => 'date',
        'review_date' => 'date',
    ];

    protected $attributes = [
        'approved_for_publication' => false,
    ];

    /** Fail-closed: approved AND in effect (no future/absent effective date). */
    public function scopeApprovedForDisplay(Builder $query): Builder
    {
        return $query->where('approved_for_publication', true)
            ->whereNotNull('approved_at')
            ->where(function (Builder $q) {
                $q->whereNull('effective_date')->orWhere('effective_date', '<=', now());
            });
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
