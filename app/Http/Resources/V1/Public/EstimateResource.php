<?php

namespace App\Http\Resources\V1\Public;

use App\Models\CostEstimate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CostEstimate
 *
 * Public, result-only view of a cost estimate. Deliberately excludes every
 * internal concern: no base (pre-conversion) formula amounts, no rule ids,
 * no margin, no lead notes, no lead PII. Cost-driver labels are localized
 * strings; the raw answers are the user's own non-PII selections.
 */
class EstimateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'public_uuid' => $this->public_uuid,
            'currency' => $this->currency,
            'amount_min' => $this->amount_min,
            'amount_max' => $this->amount_max,
            'timeline_weeks_min' => $this->timeline_weeks_min,
            'timeline_weeks_max' => $this->timeline_weeks_max,
            'complexity' => $this->complexity,
            'confidence' => $this->confidence,
            'cost_drivers' => $this->localizeDrivers($this->cost_drivers ?? [], $locale),
            'assumptions' => $this->localizeList($this->assumptions ?? [], $locale),
            'answers' => $this->answers ?? [],
            'recommended_engagement_model' => $this->whenLoaded('recommendedEngagementModel', function () {
                return $this->recommendedEngagementModel ? [
                    'slug' => $this->recommendedEngagementModel->slug,
                    'title' => $this->recommendedEngagementModel->getTranslation('title', app()->getLocale()),
                ] : null;
            }),
            'status' => $this->status,
            'has_lead' => $this->contact_lead_id !== null,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  list<array{key?:string,label?:array<string,string>,weight?:string}>  $drivers
     * @return list<array{key:string,label:string,weight:string}>
     */
    private function localizeDrivers(array $drivers, string $locale): array
    {
        return array_map(fn ($d) => [
            'key' => $d['key'] ?? '',
            'label' => $d['label'][$locale] ?? ($d['label']['en'] ?? ''),
            'weight' => $d['weight'] ?? 'low',
        ], $drivers);
    }

    /**
     * @param  list<array<string,string>>  $list
     * @return list<string>
     */
    private function localizeList(array $list, string $locale): array
    {
        return array_map(fn ($item) => $item[$locale] ?? ($item['en'] ?? ''), $list);
    }
}
