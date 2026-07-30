<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Lightweight public content search -- database LIKE over the translatable
 * JSON columns (searches EN + AR in one pass; precision is adequate at this
 * content volume, deliberately no external search engine). Published-only
 * via the Publishable scope, so drafts/private systems can never surface.
 * Typed result groups; max 5 hits per type. Not server-cached (queries are
 * high-cardinality) but covered by the public-api rate limiter.
 */
class SearchController extends Controller
{
    private const PER_TYPE = 5;

    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $locale = app()->getLocale();

        if (mb_strlen($query) < 2 || mb_strlen($query) > 100) {
            return response()->json(['data' => ['query' => $query, 'results' => (object) []]]);
        }

        $results = [
            'services' => $this->hits(Service::published(), ['name', 'summary'], $query, fn (Service $m): array => [
                'slug' => $m->slug, 'label' => $m->getTranslation('name', $locale),
                'excerpt' => $m->getTranslation('summary', $locale, false), 'path' => "/services/{$m->slug}",
            ]),
            'systems' => $this->hits(System::published(), ['name', 'short_description'], $query, fn (System $m): array => [
                'slug' => $m->slug, 'label' => $m->getTranslation('name', $locale),
                'excerpt' => $m->getTranslation('short_description', $locale, false), 'path' => "/systems/{$m->slug}",
            ]),
            'case_studies' => $this->hits(CaseStudy::published(), ['title', 'summary'], $query, fn (CaseStudy $m): array => [
                'slug' => $m->slug, 'label' => $m->getTranslation('title', $locale),
                'excerpt' => $m->getTranslation('summary', $locale, false), 'path' => "/case-studies/{$m->slug}",
            ]),
            'industries' => $this->hits(Industry::published(), ['name', 'summary'], $query, fn (Industry $m): array => [
                'slug' => $m->slug, 'label' => $m->getTranslation('name', $locale),
                'excerpt' => $m->getTranslation('summary', $locale, false), 'path' => "/industries/{$m->slug}",
            ]),
            'articles' => $this->hits(Article::published(), ['title', 'excerpt'], $query, fn (Article $m): array => [
                'slug' => $m->slug, 'label' => $m->getTranslation('title', $locale),
                'excerpt' => $m->getTranslation('excerpt', $locale, false), 'path' => "/insights/{$m->slug}",
            ]),
        ];

        return response()->json([
            'data' => [
                'query' => $query,
                'results' => array_filter($results, fn ($group) => $group !== []),
            ],
        ]);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $builder
     * @param  list<string>  $columns
     * @param  \Closure(TModel): array{slug: string, label: mixed, excerpt: mixed, path: string}  $map
     * @return list<array{slug: string, label: mixed, excerpt: mixed, path: string}>
     */
    private function hits(Builder $builder, array $columns, string $query, \Closure $map): array
    {
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';

        $builder->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $like);
            }
        });

        return $builder->limit(self::PER_TYPE)->get()->map($map)->values()->all();
    }
}
