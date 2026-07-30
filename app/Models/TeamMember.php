<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class TeamMember extends Model
{
    use HasAutoSlug, HasFactory, HasTranslations;

    protected $fillable = [
        'slug', 'first_name', 'last_name', 'position', 'bio',
        'specialization', 'expertise', 'languages', 'location',
        'email', 'phone', 'photo', 'photo_alt', 'github_url',
        'linkedin_url', 'cv_file', 'is_published', 'publication_consent',
        'is_founder', 'seo_eligible', 'person_jsonld_eligible',
        'reviewed_at', 'next_review_at', 'sort_order',
    ];

    public array $translatable = ['position', 'bio', 'photo_alt'];

    protected $casts = [
        'is_published' => 'boolean',
        'publication_consent' => 'boolean',
        'is_founder' => 'boolean',
        'seo_eligible' => 'boolean',
        'person_jsonld_eligible' => 'boolean',
        'expertise' => 'array',
        'languages' => 'array',
        'reviewed_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'first_name';
    }

    protected function slugSourceText(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Fail-closed public scope: a member is public only when BOTH
     * is_published AND publication_consent are true. Incomplete/
     * non-consenting members stay hidden regardless of is_published.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('publication_consent', true);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Person JSON-LD may only be emitted when the member opted in, is
     * flagged eligible, and actually has a name + bio to describe --
     * never inferred from email, Git history, or job titles alone.
     */
    public function isPersonJsonLdEligible(): bool
    {
        return $this->publication_consent
            && $this->person_jsonld_eligible
            && filled($this->full_name)
            && filled($this->getTranslation('bio', 'en', false));
    }

    /** @return MorphMany<PublicClaim, $this> */
    public function claims(): MorphMany
    {
        return $this->morphMany(PublicClaim::class, 'claimable');
    }
}
