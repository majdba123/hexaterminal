<?php

namespace Tests\Feature\Deployment;

use Tests\TestCase;

class ProductionFreshSeedSafetyTest extends TestCase
{
    public function test_push_deploys_cannot_enable_destructive_fresh_seeding_from_a_secret(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/deploy-production.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString("workflow_dispatch:\n    inputs:\n      allow_fresh_seed:", $workflow);
        $this->assertStringContainsString(
            "github.event_name == 'workflow_dispatch' && inputs.allow_fresh_seed || 'false'",
            $workflow,
        );
        $this->assertStringNotContainsString('secrets.ALLOW_FRESH_SEED', $workflow);
        $this->assertStringContainsString(
            'tests/Feature/Deployment/ProductionFreshSeedSafetyTest.php',
            $workflow,
        );
    }
}
