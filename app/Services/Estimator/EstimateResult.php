<?php

namespace App\Services\Estimator;

/**
 * Immutable value object holding one deterministic estimator computation.
 * All money is integer whole units. `costDrivers` carry human labels and a
 * qualitative weight only -- never raw internal multipliers or margin.
 */
final class EstimateResult
{
    /**
     * @param  array<string, string|list<string>>  $answers
     * @param  list<array{key: string, label: array<string,string>, weight: string}>  $costDrivers
     * @param  list<array<string,string>>  $assumptions
     */
    public function __construct(
        public readonly int $versionId,
        public readonly string $versionKey,
        public readonly string $currency,
        public readonly int $baseAmountMin,
        public readonly int $baseAmountMax,
        public readonly int $amountMin,
        public readonly int $amountMax,
        public readonly int $timelineWeeksMin,
        public readonly int $timelineWeeksMax,
        public readonly string $complexity,
        public readonly string $confidence,
        public readonly array $costDrivers,
        public readonly array $assumptions,
        public readonly array $answers,
        public readonly ?int $recommendedEngagementModelId,
        public readonly ?string $recommendedEngagementModelSlug,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'version_id' => $this->versionId,
            'version_key' => $this->versionKey,
            'currency' => $this->currency,
            'base_amount_min' => $this->baseAmountMin,
            'base_amount_max' => $this->baseAmountMax,
            'amount_min' => $this->amountMin,
            'amount_max' => $this->amountMax,
            'timeline_weeks_min' => $this->timelineWeeksMin,
            'timeline_weeks_max' => $this->timelineWeeksMax,
            'complexity' => $this->complexity,
            'confidence' => $this->confidence,
            'cost_drivers' => $this->costDrivers,
            'assumptions' => $this->assumptions,
            'recommended_engagement_model_id' => $this->recommendedEngagementModelId,
            'recommended_engagement_model_slug' => $this->recommendedEngagementModelSlug,
        ];
    }
}
