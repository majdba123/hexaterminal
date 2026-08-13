<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 50);
        $page = max((int) $request->input('page', 1), 1);

        $paginated = $this->rememberList('services', "page:{$page}:per_page:{$perPage}", function () use ($perPage, $page) {
            return Service::published()
                ->with('seo')
                ->orderedForPublicListing()
                ->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json([
            'data' => ServiceResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $service = $this->rememberShow('services', $slug, function () use ($slug) {
            return Service::published()->with('seo')->where('slug', $slug)->first();
        });

        abort_if(! $service, 404);

        return response()->json(['data' => new ServiceResource($service)]);
    }
}
