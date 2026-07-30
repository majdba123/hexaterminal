<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\TrustPageResource;
use App\Models\TrustPage;

/**
 * Fail-closed public read surface for Trust Pages. Every response passes
 * through TrustPage::filterReadyForPublication() -- publication alone
 * (is_published/status) is not sufficient; the page must also have real
 * content and hold every approval its page_type requires (see
 * TrustPage::isReadyForPublication()).
 */
class TrustPageController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $pages = $this->rememberList('trust-pages', 'all', function () {
            $candidates = TrustPage::published()
                ->with(['seo', 'claims', 'reviewer'])
                ->orderBy('sort_order')
                ->get();

            return TrustPage::filterReadyForPublication($candidates);
        });

        return response()->json(['data' => TrustPageResource::collection($pages)]);
    }

    public function show(string $slug)
    {
        $page = $this->rememberShow('trust-pages', $slug, function () use ($slug) {
            $page = TrustPage::published()
                ->with(['seo', 'claims', 'reviewer'])
                ->where('slug', $slug)
                ->first();

            if (! $page || ! $page->isReadyForPublication()) {
                return null;
            }

            return $page;
        });

        abort_if(! $page, 404);

        return response()->json(['data' => new TrustPageResource($page)]);
    }
}
