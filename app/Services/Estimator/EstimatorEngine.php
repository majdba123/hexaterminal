<?php

namespace App\Services\Estimator;

use App\Models\EngagementModel;
use App\Models\EstimatorQuestion;
use App\Models\EstimatorRule;
use App\Models\EstimatorVersion;
use Illuminate\Support\Collection;

/**
 * Deterministic, backend-authoritative estimator. Given a version and a set
 * of answers it always produces identical output -- no randomness, no eval,
 * no stored code, no AI. Rules are applied in a fixed order (base -> add ->
 * multiply), clamped to the version guardrails, and rounded to honest bands
 * to avoid false precision (USD 15,000-24,000, never 18,347.62).
 *
 * The calculation model is documented in
 * docs/architecture/pricing-estimator-architecture.md.
 */
class EstimatorEngine
{
    private const COMPLEXITY_LEVELS = ['standard', 'advanced', 'complex', 'enterprise'];

    private const CONFIDENCE_LEVELS = ['low', 'medium', 'high'];

    /** Recommendation is a pure function of the build + stage answers. */
    private const RECOMMENDATION_SLUGS = [
        'discovery' => 'discovery-and-architecture-sprint',
        'mvp' => 'mvp-focused-release',
        'custom' => 'custom-business-system',
        'dedicated' => 'dedicated-engineering-capacity',
        'modernization' => 'modernization-and-integration',
        'support' => 'support-and-continuous-improvement',
    ];

    /**
     * @param  array<string, string|list<string>>  $rawAnswers
     */
    public function compute(EstimatorVersion $version, array $rawAnswers, string $currency): EstimateResult
    {
        $currency = strtoupper($currency);
        $rates = $version->rates();
        if (! isset($rates[$currency])) {
            $currency = $version->base_currency;
        }

        $questions = $version->questions()->get();
        $answers = $this->filterVisibleAnswers($questions, $rawAnswers);

        $rules = $version->rules()->get();

        $min = 0;
        $max = 0;
        $weeksMin = 0;
        $weeksMax = 0;
        $complexityScore = 0;
        $drivers = [];

        // Pass 1: base + add (fixed order by sort_order within effect).
        foreach ($rules->whereIn('effect', ['base', 'add'])->sortBy('sort_order') as $rule) {
            if (! $this->ruleMatches($rule, $answers)) {
                continue;
            }
            $min += (int) $rule->amount_min;
            $max += (int) $rule->amount_max;
            $weeksMin += (int) $rule->weeks_min;
            $weeksMax += (int) $rule->weeks_max;
            $complexityScore += (int) $rule->complexity_weight;
            $this->recordDriver($drivers, $rule);
        }

        // Pass 2: multiply (accumulates; money only, never timeline).
        foreach ($rules->where('effect', 'multiply')->sortBy('sort_order') as $rule) {
            if (! $this->ruleMatches($rule, $answers) || $rule->factor === null) {
                continue;
            }
            $min = (int) round($min * $rule->factor);
            $max = (int) round($max * $rule->factor);
            $complexityScore += (int) $rule->complexity_weight;
            $this->recordDriver($drivers, $rule);
        }

        // Guardrails, ordering, honest rounding.
        $min = max($version->floor_min, min($min, $version->ceiling_max));
        $max = max($version->floor_min, min($max, $version->ceiling_max));
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }
        [$min, $max] = $this->roundBand($min, $max);

        $weeksMin = max(1, $weeksMin);
        $weeksMax = max($weeksMin + 1, $weeksMax);

        // Currency presentation (fixed USD pegs, not live FX).
        $rate = (float) $rates[$currency];
        [$curMin, $curMax] = $this->roundBand((int) round($min * $rate), (int) round($max * $rate));

        $complexity = $this->classifyComplexity($complexityScore);
        $confidence = $this->classifyConfidence($answers, $complexity);
        [$recId, $recSlug] = $this->recommendEngagementModel($answers);

        return new EstimateResult(
            versionId: $version->id,
            versionKey: $version->key,
            currency: $currency,
            baseAmountMin: $min,
            baseAmountMax: $max,
            amountMin: $curMin,
            amountMax: $curMax,
            timelineWeeksMin: $weeksMin,
            timelineWeeksMax: $weeksMax,
            complexity: $complexity,
            confidence: $confidence,
            costDrivers: array_values($drivers),
            assumptions: $this->assumptions(),
            answers: $answers,
            recommendedEngagementModelId: $recId,
            recommendedEngagementModelSlug: $recSlug,
        );
    }

    /**
     * Keep only answers for questions that are actually visible given the
     * branching conditions -- prevents a client from forcing hidden-question
     * rules by submitting answers it should never have reached.
     *
     * @param  Collection<int, EstimatorQuestion>  $questions
     * @param  array<string, string|list<string>>  $rawAnswers
     * @return array<string, string|list<string>>
     */
    private function filterVisibleAnswers($questions, array $rawAnswers): array
    {
        $byKey = $questions->keyBy('key');
        $visible = [];

        foreach ($questions as $question) {
            if ($this->isVisible($question, $rawAnswers, $byKey)) {
                $answer = $rawAnswers[$question->key] ?? null;
                if ($answer !== null && $answer !== '' && $answer !== []) {
                    $visible[$question->key] = $answer;
                }
            }
        }

        return $visible;
    }

    /**
     * @param  array<string, string|list<string>>  $answers
     * @param  Collection<string, EstimatorQuestion>  $byKey
     */
    private function isVisible(EstimatorQuestion $question, array $answers, $byKey): bool
    {
        $condition = $question->show_if;
        if (! is_array($condition) || ! isset($condition['question'], $condition['in'])) {
            return true;
        }

        // A branch only shows if the controlling question is itself visible.
        $parent = $byKey->get($condition['question']);
        if ($parent && ! $this->isVisible($parent, $answers, $byKey)) {
            return false;
        }

        $parentAnswer = $answers[$condition['question']] ?? null;
        $allowed = (array) $condition['in'];
        $given = is_array($parentAnswer) ? $parentAnswer : [$parentAnswer];

        return count(array_intersect($given, $allowed)) > 0;
    }

    /** @param array<string, string|list<string>> $answers */
    private function ruleMatches(EstimatorRule $rule, array $answers): bool
    {
        // Always-on base rule.
        if ($rule->question_key === null) {
            return true;
        }

        $answer = $answers[$rule->question_key] ?? null;
        if ($answer === null) {
            return false;
        }

        if ($rule->option_key === null) {
            return true; // any answer to this question triggers it
        }

        return is_array($answer)
            ? in_array($rule->option_key, $answer, true)
            : $answer === $rule->option_key;
    }

    /**
     * @param  array<string, array{key:string,label:array<string,string>,weight:string}>  $drivers
     *
     * @param-out array<string, array{key:string,label:array<string,string>,weight:string}> $drivers
     */
    private function recordDriver(array &$drivers, EstimatorRule $rule): void
    {
        if ($rule->driver === 'base') {
            return; // the base band is not shown as an add-on driver
        }
        $label = [
            'en' => (string) $rule->getTranslation('label', 'en'),
            'ar' => (string) $rule->getTranslation('label', 'ar'),
        ];
        if ($label['en'] === '' && $label['ar'] === '') {
            return; // unlabeled rules are not surfaced as drivers
        }

        $weight = $rule->effect === 'multiply'
            ? 'high'
            : ((int) $rule->amount_max >= 8000 ? 'high' : ((int) $rule->amount_max >= 3000 ? 'medium' : 'low'));

        // One entry per driver; keep the strongest weight seen.
        $existing = $drivers[$rule->driver]['weight'] ?? null;
        if ($existing === 'high' || ($existing === 'medium' && $weight === 'low')) {
            return;
        }

        $drivers[$rule->driver] = [
            'key' => $rule->driver,
            'label' => $label,
            'weight' => $weight,
        ];
    }

    /** @return array{0:int,1:int} */
    private function roundBand(int $min, int $max): array
    {
        $step = $max < 20000 ? 500 : ($max < 100000 ? 1000 : 5000);
        $min = intdiv($min, $step) * $step;
        $max = (int) (ceil($max / $step) * $step);
        if ($max <= $min) {
            $max = $min + $step;
        }

        return [$min, $max];
    }

    private function classifyComplexity(int $score): string
    {
        return match (true) {
            $score <= 2 => self::COMPLEXITY_LEVELS[0],
            $score <= 5 => self::COMPLEXITY_LEVELS[1],
            $score <= 9 => self::COMPLEXITY_LEVELS[2],
            default => self::COMPLEXITY_LEVELS[3],
        };
    }

    /** @param array<string, string|list<string>> $answers */
    private function classifyConfidence(array $answers, string $complexity): string
    {
        $index = 1; // medium
        $stage = $answers['stage'] ?? null;

        if ($stage === 'idea') {
            $index--;
        } elseif ($stage === 'production') {
            $index++;
        }
        if ($complexity === 'enterprise') {
            $index--;
        }

        $index = max(0, min(2, $index));

        return self::CONFIDENCE_LEVELS[$index];
    }

    /**
     * @param  array<string, string|list<string>>  $answers
     * @return array{0:?int,1:?string}
     */
    private function recommendEngagementModel(array $answers): array
    {
        $build = $answers['build'] ?? null;
        $stage = $answers['stage'] ?? null;

        $key = match (true) {
            $stage === 'idea' => 'discovery',
            $build === 'modernization' || $stage === 'production' => 'modernization',
            in_array($build, ['saas', 'crm_erp', 'custom'], true) && in_array($stage, ['documented', 'prototype'], true) => 'mvp',
            in_array($build, ['automation', 'api'], true) => 'custom',
            default => 'custom',
        };

        $slug = self::RECOMMENDATION_SLUGS[$key];
        $id = EngagementModel::query()->where('slug', $slug)->value('id');

        return [$id, $slug];
    }

    /**
     * Generic engineering methodology assumptions (not commercial promises,
     * not prices). Localized, static, and version-independent.
     *
     * @return list<array<string,string>>
     */
    private function assumptions(): array
    {
        return [
            [
                'en' => 'Scope is estimated from your answers; final scope is confirmed during a discovery conversation.',
                'ar' => 'يُقدَّر النطاق من إجاباتك؛ ويُؤكَّد النطاق النهائي خلال محادثة استكشافية.',
            ],
            [
                'en' => 'Third-party service costs (hosting, licenses, paid APIs) are excluded.',
                'ar' => 'تُستثنى تكاليف خدمات الأطراف الثالثة (الاستضافة، التراخيص، الواجهات المدفوعة).',
            ],
            [
                'en' => 'Taxes depend on your jurisdiction and are not included.',
                'ar' => 'تعتمد الضرائب على ولايتك القضائية وغير مشمولة.',
            ],
        ];
    }
}
