<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\EngagementModelResource;
use App\Http\Resources\V1\Public\FaqResource;
use App\Models\EngagementModel;
use App\Models\EstimatorVersion;
use App\Models\FaqItem;
use Illuminate\Http\Request;

/**
 * Public pricing page payload: published engagement models (with an
 * approved price band ONLY when one exists for the requested currency --
 * fail closed otherwise), published pricing FAQs, and whether the
 * estimator is available. No number is ever fabricated here.
 */
class PricingController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $currency = strtoupper((string) $request->query('currency', 'USD'));
        if (! in_array($currency, ['USD', 'AED', 'SAR'], true)) {
            $currency = 'USD';
        }

        $models = $this->rememberList('engagement_models', 'all', function () {
            return EngagementModel::published()->orderBy('sort_order')->get();
        });

        $faqs = $this->rememberList('pricing_faqs', 'all', function () {
            return FaqItem::published()->where('category', 'pricing')->orderBy('sort_order')->get();
        });

        return response()->json([
            'data' => [
                'engagement_models' => EngagementModelResource::collection($models),
                'faqs' => FaqResource::collection($faqs),
                'estimator_available' => EstimatorVersion::current() !== null,
                'currency' => $currency,
                'currencies' => ['USD', 'AED', 'SAR'],
            ],
        ]);
    }
}
