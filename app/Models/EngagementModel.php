<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

/**
 * A commercial engagement shape (Discovery Sprint, MVP, Custom System,
 * Dedicated Team, Modernization, Support). Content + a pricing DISPLAY
 * mode; the numbers live in approval-gated PricingProfile rows so a model
 * can publish while its price stays hidden until founder approval.
 */
class EngagementModel extends Model
{
    use HasAutoSlug, HasFactory, HasTranslations;

    public const DISPLAY_MODES = [
        'hidden', 'request_quote', 'starting_from', 'indicative_range', 'fixed_package',
    ];

    public const BILLING_MODELS = [
        'fixed_project', 'milestone_based', 'monthly_retainer',
        'discovery_sprint', 'dedicated_team', 'support_plan',
    ];

    protected $fillable = [
        'slug', 'title', 'summary', 'buyer_fit', 'typical_scope', 'deliverables',
        'included_items', 'excluded_items', 'indicative_duration', 'cta_label',
        'cta_intent', 'pricing_display_mode', 'billing_model', 'is_featured',
        'is_published', 'sort_order',
    ];

    public array $translatable = [
        'title', 'summary', 'buyer_fit', 'typical_scope', 'deliverables',
        'included_items', 'excluded_items', 'indicative_duration',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'title';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** @return MorphMany<PricingProfile, $this> */
    public function pricingProfiles(): MorphMany
    {
        return $this->morphMany(PricingProfile::class, 'priceable');
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * The publishable price band for a currency, or null. Fails closed:
     * only an approved, in-effect profile is ever returned, and only when
     * the display mode actually shows a number.
     */
    public function publicPricingProfile(string $currency): ?PricingProfile
    {
        if (in_array($this->pricing_display_mode, ['hidden', 'request_quote'], true)) {
            return null;
        }

        return $this->pricingProfiles()
            ->approvedForDisplay()
            ->where('currency', $currency)
            ->first();
    }
}
