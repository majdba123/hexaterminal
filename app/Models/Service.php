<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

/**
 * A commercial service offering (SaaS platforms, CRM/ERP systems, AI
 * integrations, backend/API engineering, automation, etc). Deliberately
 * distinct from the legacy `App\Models\Services` model, which still
 * backs the old Blade frontend/admin until cutover.
 */
class Service extends Model
{
    use HasAutoSlug, HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    protected $table = 'service_offerings';

    protected $fillable = [
        'slug', 'name', 'tagline', 'summary', 'description', 'icon',
        'cover_image', 'cover_image_alt', 'features', 'tech_stack', 'is_published',
        'published_at', 'sort_order',
    ];

    public array $translatable = ['name', 'tagline', 'summary', 'description', 'cover_image_alt', 'features'];

    protected $casts = [
        'features' => 'array',
        'tech_stack' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * The approved business tracks shown in the homepage Core Services area.
     * These are identifiers only; all display content remains CMS-managed.
     */
    public const CORE_SERVICE_SLUGS = [
        'custom-erp-crm-systems',
        'web-platforms-mobile-applications',
        'ecommerce-business-websites',
    ];

    /**
     * @param Builder<Service> $query
     * @return Builder<Service>
     */
    public function scopeCoreServices(Builder $query): Builder
    {
        $slugs = self::CORE_SERVICE_SLUGS;
        $order = implode(' ', array_map(
            fn (string $slug, int $position) => "WHEN '{$slug}' THEN {$position}",
            $slugs,
            array_keys($slugs),
        ));

        return $query
            ->whereIn('slug', $slugs)
            ->orderByRaw("CASE slug {$order} ELSE ".count($slugs).' END');
    }

    /**
     * Keeps the approved business tracks first on public listings while still
     * allowing future CMS offerings to follow their editorial sort order.
     *
     * @param Builder<Service> $query
     * @return Builder<Service>
     */
    public function scopeOrderedForPublicListing(Builder $query): Builder
    {
        $when = implode(' ', array_fill(0, count(self::CORE_SERVICE_SLUGS), 'WHEN ? THEN ?'));
        $bindings = [];

        foreach (self::CORE_SERVICE_SLUGS as $position => $slug) {
            $bindings[] = $slug;
            $bindings[] = $position;
        }

        return $query
            ->orderByRaw('CASE slug '.$when.' ELSE ? END', [...$bindings, count(self::CORE_SERVICE_SLUGS)])
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<CaseStudy, $this>
     */
    public function caseStudies(): HasMany
    {
        return $this->hasMany(CaseStudy::class, 'service_offering_id');
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
