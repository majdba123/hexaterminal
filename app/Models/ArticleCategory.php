<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 */
class ArticleCategory extends Model
{
    use HasAutoSlug, HasFactory, HasTranslations;

    protected $fillable = ['slug', 'name', 'description', 'sort_order'];

    public array $translatable = ['name', 'description'];

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
