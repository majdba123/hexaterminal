<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generates a unique slug from the model's slugSourceAttribute()
 * on creation, when one wasn't explicitly set. Slugs are always
 * derived from the English source text so URLs stay stable and Latin
 * regardless of which locale content was entered in.
 */
trait HasAutoSlug
{
    public static function bootHasAutoSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected function slugSourceText(): string
    {
        $value = $this->{$this->slugSourceAttribute()};

        if (is_array($value)) {
            return $value['en'] ?? reset($value) ?: '';
        }

        return (string) $value;
    }

    protected function generateUniqueSlug(): string
    {
        $base = Str::slug($this->slugSourceText()) ?: Str::random(8);
        $slug = $base;
        $suffix = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}
