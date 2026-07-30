<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    use HasAutoSlug, HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    protected $fillable = [
        'slug', 'title', 'excerpt', 'body', 'cover_image', 'cover_image_alt', 'og_image',
        'author_id', 'article_category_id', 'is_featured',
        'is_published', 'published_at', 'updated_content_at',
    ];

    public array $translatable = ['title', 'excerpt', 'body', 'cover_image_alt'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'updated_content_at' => 'datetime',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'title';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<ArticleCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /**
     * @return BelongsToMany<ArticleTag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class);
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Estimated reading time for the requested locale, from the body word
     * count at ~200 wpm. Unicode-aware split so Arabic counts sensibly.
     * Computed on demand, never stored.
     */
    public function readingMinutes(string $locale): int
    {
        $body = strip_tags((string) $this->getTranslation('body', $locale, false));
        $words = count(preg_split('/\s+/u', trim($body), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / 200));
    }
}
