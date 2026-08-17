<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

/**
 * Unified catalog entity for everything Hexa Terminal builds -- SaaS
 * products, business systems, client systems, internal platforms, AI
 * systems -- differentiated by `type` rather than separate overlapping
 * models (SaaSProduct/BusinessSystem/ClientSystem etc).
 */
class System extends Model
{
    use HasAutoSlug, HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    public const TYPE_SAAS_PRODUCT = 'saas_product';

    public const TYPE_BUSINESS_SYSTEM = 'business_system';

    public const TYPE_CLIENT_SYSTEM = 'client_system';

    public const TYPE_INTERNAL_PRODUCT = 'internal_product';

    public const TYPE_PLATFORM = 'platform';

    public const TYPE_AI_SYSTEM = 'ai_system';

    protected $fillable = [
        'slug', 'type', 'category', 'name', 'tagline', 'short_description',
        'full_description', 'problem', 'solution', 'features',
        'business_outcomes', 'target_audience', 'tech_stack', 'cover_image', 'cover_image_alt',
        'gallery', 'demo_url', 'live_url', 'is_featured', 'is_published',
        'published_at', 'sort_order',
    ];

    public array $translatable = [
        'name', 'tagline', 'short_description', 'full_description',
        'problem', 'solution', 'features', 'business_outcomes', 'target_audience',
        'cover_image_alt',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @return BelongsToMany<Industry, $this>
     */
    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class, 'industry_system');
    }

    /**
     * @return HasMany<CaseStudy, $this>
     */
    public function caseStudies(): HasMany
    {
        return $this->hasMany(CaseStudy::class);
    }

    /**
     * @return HasMany<SystemUseCase, $this>
     */
    public function useCases(): HasMany
    {
        return $this->hasMany(SystemUseCase::class)->orderBy('sort_order');
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
