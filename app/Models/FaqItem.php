<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * New translatable FAQ entity backing the `faqs` table. Named FaqItem
 * (not Faq) because `Faq.php` and the legacy `FAQ.php` collide on
 * case-insensitive filesystems (Windows/macOS default).
 */
class FaqItem extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'faqs';

    protected $fillable = [
        'question', 'answer', 'category', 'is_published', 'sort_order',
    ];

    public array $translatable = ['question', 'answer'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
