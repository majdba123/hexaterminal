<?php

namespace Tests\Feature\Pricing;

use App\Models\EngagementModel;
use App\Models\EstimatorQuestion;
use App\Models\EstimatorRule;
use App\Models\EstimatorVersion;
use App\Services\Estimator\EstimatorEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatorEngineTest extends TestCase
{
    use RefreshDatabase;

    private function makeVersion(int $floor = 4000, int $ceiling = 400000): EstimatorVersion
    {
        $version = EstimatorVersion::create([
            'key' => 'test', 'label' => 'Test', 'status' => 'active', 'is_active' => true,
            'base_currency' => 'USD', 'currency_rates' => ['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75],
            'floor_min' => $floor, 'ceiling_max' => $ceiling,
        ]);

        EstimatorQuestion::create([
            'estimator_version_id' => $version->id, 'key' => 'build', 'step' => 1, 'type' => 'single_select',
            'prompt' => ['en' => 'Build?'], 'options' => [
                ['key' => 'saas', 'label' => ['en' => 'SaaS']],
                ['key' => 'api', 'label' => ['en' => 'API']],
            ],
        ]);
        EstimatorQuestion::create([
            'estimator_version_id' => $version->id, 'key' => 'stage', 'step' => 2, 'type' => 'single_select',
            'prompt' => ['en' => 'Stage?'], 'options' => [
                ['key' => 'idea', 'label' => ['en' => 'Idea']],
                ['key' => 'production', 'label' => ['en' => 'Production']],
            ],
        ]);
        // migration only shows when stage=production
        EstimatorQuestion::create([
            'estimator_version_id' => $version->id, 'key' => 'migration', 'step' => 3, 'type' => 'single_select',
            'prompt' => ['en' => 'Migration?'], 'show_if' => ['question' => 'stage', 'in' => ['production']],
            'options' => [['key' => 'complex_legacy', 'label' => ['en' => 'Complex']]],
        ]);

        EstimatorRule::create([
            'estimator_version_id' => $version->id, 'driver' => 'base', 'question_key' => 'build',
            'option_key' => 'saas', 'effect' => 'base', 'amount_min' => 20000, 'amount_max' => 40000,
            'weeks_min' => 8, 'weeks_max' => 14, 'complexity_weight' => 3, 'sort_order' => 1,
        ]);
        EstimatorRule::create([
            'estimator_version_id' => $version->id, 'driver' => 'base', 'question_key' => 'build',
            'option_key' => 'api', 'effect' => 'base', 'amount_min' => 5000, 'amount_max' => 9000,
            'weeks_min' => 3, 'weeks_max' => 6, 'complexity_weight' => 1, 'sort_order' => 2,
        ]);
        EstimatorRule::create([
            'estimator_version_id' => $version->id, 'driver' => 'migration_legacy', 'question_key' => 'migration',
            'option_key' => 'complex_legacy', 'effect' => 'add', 'amount_min' => 9000, 'amount_max' => 20000,
            'weeks_min' => 2, 'weeks_max' => 3, 'complexity_weight' => 2, 'sort_order' => 3,
            'label' => ['en' => 'Complex migration'],
        ]);

        return $version;
    }

    public function test_identical_inputs_produce_identical_output(): void
    {
        $version = $this->makeVersion();
        $engine = new EstimatorEngine;
        $answers = ['build' => 'saas', 'stage' => 'production', 'migration' => 'complex_legacy'];

        $a = $engine->compute($version, $answers, 'USD');
        $b = $engine->compute($version, $answers, 'USD');

        $this->assertSame($a->toArray(), $b->toArray());
    }

    public function test_rounds_to_honest_bands_never_exact(): void
    {
        $version = $this->makeVersion();
        $result = (new EstimatorEngine)->compute($version, ['build' => 'saas', 'stage' => 'idea'], 'USD');

        // Bands are multiples of a rounding step -- no false precision.
        $this->assertSame(0, $result->amountMin % 500);
        $this->assertSame(0, $result->amountMax % 500);
        $this->assertGreaterThan($result->amountMin, $result->amountMax);
    }

    public function test_floor_guardrail_is_enforced(): void
    {
        $version = $this->makeVersion(floor: 15000);
        // API base is 5000-9000, below the 15000 floor.
        $result = (new EstimatorEngine)->compute($version, ['build' => 'api', 'stage' => 'idea'], 'USD');

        $this->assertGreaterThanOrEqual(15000, $result->amountMin);
    }

    public function test_hidden_branch_answers_are_ignored(): void
    {
        $version = $this->makeVersion();
        $engine = new EstimatorEngine;

        // stage=idea hides the migration question; a smuggled migration answer
        // must NOT add its cost.
        $withoutBranch = $engine->compute($version, ['build' => 'saas', 'stage' => 'idea'], 'USD');
        $smuggled = $engine->compute($version, ['build' => 'saas', 'stage' => 'idea', 'migration' => 'complex_legacy'], 'USD');

        $this->assertSame($withoutBranch->amountMax, $smuggled->amountMax);
    }

    public function test_currency_uses_fixed_pegs_not_exact_conversion(): void
    {
        $version = $this->makeVersion();
        $engine = new EstimatorEngine;
        $usd = $engine->compute($version, ['build' => 'saas', 'stage' => 'idea'], 'USD');
        $aed = $engine->compute($version, ['build' => 'saas', 'stage' => 'idea'], 'AED');

        // AED band is materially larger (pegged ~3.67x) and still rounded.
        $this->assertGreaterThan($usd->amountMax, $aed->amountMax);
        $this->assertSame(0, $aed->amountMax % 500);
    }

    public function test_recommends_discovery_for_idea_stage(): void
    {
        EngagementModel::create(['slug' => 'discovery-and-architecture-sprint', 'title' => ['en' => 'Discovery'], 'is_published' => true]);
        $version = $this->makeVersion();

        $result = (new EstimatorEngine)->compute($version, ['build' => 'saas', 'stage' => 'idea'], 'USD');

        $this->assertSame('discovery-and-architecture-sprint', $result->recommendedEngagementModelSlug);
    }

    public function test_cost_drivers_carry_labels_not_raw_formula(): void
    {
        $version = $this->makeVersion();
        $result = (new EstimatorEngine)->compute($version, ['build' => 'saas', 'stage' => 'production', 'migration' => 'complex_legacy'], 'USD');

        $driver = collect($result->costDrivers)->firstWhere('key', 'migration_legacy');
        $this->assertNotNull($driver);
        $this->assertArrayHasKey('label', $driver);
        $this->assertArrayHasKey('weight', $driver);
        // No amount/factor leaked into the public driver.
        $this->assertArrayNotHasKey('amount_max', $driver);
    }
}
