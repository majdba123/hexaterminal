<?php

namespace App\Http\Resources\V1\Public;

use App\Http\Resources\V1\Public\Concerns\EmbedsSeoMeta;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    use EmbedsSeoMeta;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image' => $this->cover_image,
            'cover_image_alt' => $this->cover_image_alt,
            'og_image' => $this->og_image,
            'is_featured' => (bool) $this->is_featured,
            'reading_minutes' => $this->readingMinutes(app()->getLocale()),
            'category' => $this->whenLoaded('category', function () {
                /** @var ArticleCategory|null $category */
                $category = $this->category;

                return $category ? [
                    'slug' => $category->slug,
                    'name' => $category->name,
                ] : null;
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn (ArticleTag $tag) => [
                    'slug' => $tag->slug,
                    'name' => $tag->name,
                ])->values();
            }),
            'author' => $this->whenLoaded('author', function () {
                /** @var User $author */
                $author = $this->author;

                return ['name' => $author->name];
            }),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_content_at' => $this->updated_content_at?->toIso8601String(),
            'seo' => $this->seoMeta(),
        ];
    }
}
