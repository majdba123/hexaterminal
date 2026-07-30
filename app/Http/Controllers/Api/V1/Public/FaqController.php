<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\FaqResource;
use App\Models\FaqItem;

class FaqController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $faqs = $this->rememberList('faqs', 'all', function () {
            return FaqItem::published()->orderBy('sort_order')->get();
        });

        return response()->json(['data' => FaqResource::collection($faqs)]);
    }
}
