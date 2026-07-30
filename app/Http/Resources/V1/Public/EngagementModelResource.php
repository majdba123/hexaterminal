<?php

namespace App\Http\Resources\V1\Public;

use App\Models\EngagementModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EngagementModel
 *
 * Public engagement model. A price NUMBER appears only when an approved,
 * in-effect PricingProfile exists for the requested currency AND the
 * display mode shows numbers -- otherwise `pricing` is null and the
 * frontend renders honest "request a scoped estimate" guidance.
 */
class EngagementModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currency = strtoupper((string) $request->query('currency', 'USD'));
        $profile = $this->publicPricingProfile($currency);

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'buyer_fit' => $this->buyer_fit,
            'typical_scope' => $this->typical_scope,
            'deliverables' => $this->deliverables ?? [],
            'included_items' => $this->included_items ?? [],
            'excluded_items' => $this->excluded_items ?? [],
            'indicative_duration' => $this->indicative_duration,
            'cta_label' => $this->cta_label,
            'cta_intent' => $this->cta_intent,
            'pricing_display_mode' => $this->pricing_display_mode,
            'billing_model' => $this->billing_model,
            'is_featured' => (bool) $this->is_featured,
            'pricing' => $profile ? [
                'currency' => $profile->currency,
                'min_amount' => $profile->min_amount,
                'max_amount' => $profile->max_amount,
                'price_unit' => $profile->price_unit,
                'billing_model' => $profile->billing_model,
                'display_label' => $profile->display_label,
                'assumptions' => $profile->assumptions,
                'exclusions' => $profile->exclusions,
                'disclaimer' => $profile->disclaimer,
            ] : null,
        ];
    }
}
