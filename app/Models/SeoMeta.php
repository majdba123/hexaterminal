<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

/**
 * Per-page SEO overrides, attached polymorphically to any content model
 * via its seo() morphOne relation. A page renders fine without a row
 * here -- these are overrides, not requirements.
 */
class SeoMeta extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_type', 'seoable_id', 'title', 'description',
        'canonical_url', 'og_image', 'noindex', 'nofollow',
    ];

    public array $translatable = ['title', 'description'];

    protected $casts = [
        'noindex' => 'boolean',
        'nofollow' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
