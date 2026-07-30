<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\IndustryResource;
use App\Models\Industry;

class IndustryController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $industries = $this->rememberList('industries', 'all', function () {
            return Industry::published()->with('seo')->orderBy('sort_order')->get();
        });

        return response()->json(['data' => IndustryResource::collection($industries)]);
    }

    public function show(string $slug)
    {
        $industry = $this->rememberShow('industries', $slug, function () use ($slug) {
            return Industry::published()->with('seo')->where('slug', $slug)->first();
        });

        abort_if(! $industry, 404);

        return response()->json(['data' => new IndustryResource($industry)]);
    }
}
