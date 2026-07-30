<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

/**
 * Outcome-focused case study: context -> problem -> constraints ->
 * solution -> architecture -> outcomes -> evidence. Consolidates the
 * legacy Projects + Imag_Progect (images) + Fetures_Project (features)
 * tables -- see the data-migration command and
 * docs/migration/legacy-to-nextjs.md.
 */
class CaseStudy extends Model
{
    use HasAutoSlug, HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    protected $fillable = [
        'slug', 'title', 'summary', 'context', 'problem', 'constraints',
        'solution', 'architecture', 'outcomes', 'evidence', 'features',
        'client_name', 'project_url', 'video_url', 'cover_image', 'cover_image_alt', 'gallery',
        'service_offering_id', 'system_id', 'is_featured', 'is_published',
        'published_at', 'sort_order', 'legacy_project_id',
    ];

    public array $translatable = [
        'title', 'summary', 'context', 'problem', 'constraints',
        'solution', 'architecture', 'outcomes', 'evidence', 'features',
        'cover_image_alt',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'title';
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function serviceOffering(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_offering_id');
    }

    /**
     * @return BelongsTo<System, $this>
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    /**
     * @return BelongsToMany<Industry, $this>
     */
    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class, 'case_study_industry');
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
