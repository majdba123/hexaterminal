<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Moderated testimonial. Evolves the legacy `reviews` table's moderation
 * pattern (Review::scopeApproved) -- unapproved testimonials must never
 * be exposed through the public API.
 */
class Testimonial extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'author_name', 'author_title', 'company', 'company_logo',
        'content', 'rating', 'given_at', 'is_approved', 'is_featured',
        'legacy_review_id',
    ];

    public array $translatable = ['content'];

    protected $casts = [
        'rating' => 'integer',
        'given_at' => 'date',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $attributes = [
        'is_approved' => false,
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
