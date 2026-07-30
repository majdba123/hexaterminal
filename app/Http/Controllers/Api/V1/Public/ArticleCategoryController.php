<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Builder;

/**
 * Categories that actually have published articles (no empty taxonomy
 * pages), with per-category published counts for the Insights filter UI.
 */
class ArticleCategoryController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $categories = $this->rememberList('article-categories', 'all', function () {
            return ArticleCategory::query()
                ->whereHas('articles', function ($query) {
                    /** @var Builder<Article> $query */
                    return $query->published();
                })
                ->withCount(['articles as published_count' => function ($query) {
                    /** @var Builder<Article> $query */
                    return $query->published();
                }])
                ->orderBy('sort_order')
                ->get();
        });

        return response()->json([
            'data' => $categories->map(fn (ArticleCategory $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'description' => $category->description,
                'published_count' => (int) $category->getAttribute('published_count'),
            ]),
        ]);
    }
}
