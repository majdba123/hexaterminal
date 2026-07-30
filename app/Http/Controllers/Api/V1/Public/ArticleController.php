<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 12), 50);
        $page = max((int) $request->input('page', 1), 1);
        $category = (string) $request->input('category', '');
        $tag = (string) $request->input('tag', '');
        $featured = $request->boolean('featured');

        $suffix = "page:{$page}:per_page:{$perPage}:cat:{$category}:tag:{$tag}:feat:".($featured ? 1 : 0);

        $paginated = $this->rememberList('articles', $suffix, function () use ($perPage, $page, $category, $tag, $featured) {
            return Article::published()
                ->with(['seo', 'author', 'category', 'tags'])
                ->when($category !== '', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $category)))
                ->when($tag !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tag)))
                ->when($featured, fn ($q) => $q->where('is_featured', true))
                ->orderByDesc('published_at')
                ->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json([
            'data' => ArticleResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $article = $this->rememberShow('articles', $slug, function () use ($slug) {
            return Article::published()->with(['seo', 'author', 'category', 'tags'])->where('slug', $slug)->first();
        });

        abort_if(! $article, 404);

        return response()->json(['data' => new ArticleResource($article)]);
    }
}
