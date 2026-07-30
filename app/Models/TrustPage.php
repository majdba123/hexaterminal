<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

/**
 * One coherent Trust Page model covering every governance-sensitive public
 * page type (security, process, accessibility, technology, responsible_ai,
 * engineering_standards, support, code_ip_ownership, data_privacy,
 * company_delivery) instead of separate hardcoded page systems.
 *
 * Public exposure is fail-closed: callers must combine the generic
 * Publishable::scopePublished() (SQL-level draft/published filter) with
 * filterReadyForPublication() (English content + every approval this
 * page_type needs) on the resulting collection -- see
 * App\Http\Controllers\Api\V1\Public\TrustPageController. A page missing
 * required approvals stays effectively unpublished no matter what
 * `is_published` says.
 */
class TrustPage extends Model
{
    use HasAutoSlug, HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    public const TYPES = [
        'security', 'process', 'accessibility', 'technology', 'responsible_ai',
        'engineering_standards', 'support', 'code_ip_ownership', 'data_privacy',
        'company_delivery',
    ];

    /** page_types whose publication requires founder sign-off. */
    public const TYPES_REQUIRING_FOUNDER_APPROVAL = [
        'security', 'data_privacy', 'company_delivery', 'code_ip_ownership',
    ];

    /** page_types whose publication requires legal sign-off. */
    public const TYPES_REQUIRING_LEGAL_APPROVAL = [
        'data_privacy', 'code_ip_ownership', 'company_delivery',
    ];

    /** page_types whose publication requires security sign-off. */
    public const TYPES_REQUIRING_SECURITY_APPROVAL = [
        'security',
    ];

    protected $fillable = [
        'slug', 'page_type', 'title', 'summary', 'sections', 'faqs', 'cta',
        'content_owner_id', 'reviewer_id',
        'founder_approved', 'legal_approved', 'security_approved',
        'is_published', 'published_at', 'noindex', 'show_in_nav',
        'show_in_footer', 'sort_order', 'reviewed_at', 'next_review_at',
    ];

    public array $translatable = ['title', 'summary', 'sections', 'faqs', 'cta'];

    protected $casts = [
        'founder_approved' => 'boolean',
        'legal_approved' => 'boolean',
        'security_approved' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'noindex' => 'boolean',
        'show_in_nav' => 'boolean',
        'show_in_footer' => 'boolean',
        'reviewed_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'title';
    }

    /**
     * Apply the full fail-closed contract to an already-published
     * collection: real English content + every approval this page_type
     * requires. This is the only method public controllers should trust.
     *
     * @param  Collection<int, self>  $pages
     * @return Collection<int, self>
     */
    public static function filterReadyForPublication($pages)
    {
        return $pages->filter(fn (self $page) => $page->isReadyForPublication())->values();
    }

    public function isReadyForPublication(): bool
    {
        if (blank($this->getTranslation('title', 'en', false))) {
            return false;
        }

        $sections = $this->getTranslation('sections', 'en', false);
        if (blank($sections)) {
            return false;
        }

        if (in_array($this->page_type, self::TYPES_REQUIRING_FOUNDER_APPROVAL, true) && ! $this->founder_approved) {
            return false;
        }

        if (in_array($this->page_type, self::TYPES_REQUIRING_LEGAL_APPROVAL, true) && ! $this->legal_approved) {
            return false;
        }

        if (in_array($this->page_type, self::TYPES_REQUIRING_SECURITY_APPROVAL, true) && ! $this->security_approved) {
            return false;
        }

        return true;
    }

    /** @return BelongsTo<User, $this> */
    public function contentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /** @return MorphMany<PublicClaim, $this> */
    public function claims(): MorphMany
    {
        return $this->morphMany(PublicClaim::class, 'claimable');
    }
}
