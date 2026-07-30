<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\RedirectResource;
use App\Models\Redirect;

/**
 * Locale-agnostic legacy -> new URL map, consumed by frontend/next.config.ts
 * at build time to generate real 301s for bookmarked/indexed legacy URLs
 * (routes/web.php: /project/{id}, /service/{id}, /team/{id}, /projects).
 * See docs/migration/legacy-to-nextjs.md.
 */
class RedirectController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $redirects = $this->rememberList('redirects', 'all', function () {
            return Redirect::active()->orderBy('from_path')->get();
        });

        return response()->json(['data' => RedirectResource::collection($redirects)]);
    }
}
