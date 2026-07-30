<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\CaseStudyResource;
use App\Models\CaseStudy;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 50);
        $page = max((int) $request->input('page', 1), 1);
        $featured = $request->boolean('featured');

        $suffix = "page:{$page}:per_page:{$perPage}:featured:".($featured ? '1' : '0');

        $paginated = $this->rememberList('case-studies', $suffix, function () use ($perPage, $page, $featured) {
            $query = CaseStudy::published()
                ->with(['seo', 'serviceOffering', 'system', 'industries'])
                ->orderBy('sort_order');

            if ($featured) {
                $query->featured();
            }

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json([
            'data' => CaseStudyResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $caseStudy = $this->rememberShow('case-studies', $slug, function () use ($slug) {
            return CaseStudy::published()
                ->with(['seo', 'serviceOffering', 'system', 'industries'])
                ->where('slug', $slug)->first();
        });

        abort_if(! $caseStudy, 404);

        return response()->json(['data' => new CaseStudyResource($caseStudy)]);
    }
}
