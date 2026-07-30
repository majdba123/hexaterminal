<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 */
class ArticleTag extends Model
{
    use HasAutoSlug, HasFactory, HasTranslations;

    protected $fillable = ['slug', 'name'];

    public array $translatable = ['name'];

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
