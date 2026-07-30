<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\SystemResource;
use App\Models\System;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 50);
        $page = max((int) $request->input('page', 1), 1);
        $type = $request->input('type');
        $featured = $request->boolean('featured');

        $suffix = "page:{$page}:per_page:{$perPage}:type:".($type ?: 'any').':featured:'.($featured ? '1' : '0');

        $paginated = $this->rememberList('systems', $suffix, function () use ($perPage, $page, $type, $featured) {
            $query = System::published()->with(['seo', 'industries'])->orderBy('sort_order');

            if ($type) {
                $query->ofType($type);
            }

            if ($featured) {
                $query->featured();
            }

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json([
            'data' => SystemResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $system = $this->rememberShow('systems', $slug, function () use ($slug) {
            return System::published()->with(['seo', 'industries', 'caseStudies' => fn ($q) => $q->published()])
                ->where('slug', $slug)->first();
        });

        abort_if(! $system, 404);

        return response()->json(['data' => new SystemResource($system)]);
    }
}
