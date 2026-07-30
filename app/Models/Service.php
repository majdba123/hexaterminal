<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public array $translatable = ['name', 'tagline', 'summary', 'description', 'cover_image_alt'];

    protected $casts = [
        'features' => 'array',
        'tech_stack' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

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
