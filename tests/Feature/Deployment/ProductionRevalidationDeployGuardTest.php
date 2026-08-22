<?php

namespace Tests\Feature\Deployment;

use Tests\TestCase;

class ProductionRevalidationDeployGuardTest extends TestCase
{
    public function test_production_deploy_validates_revalidation_wiring_without_logging_secrets(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/deploy-production.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString('config("revalidation.enabled")', $workflow);
        $this->assertStringContainsString('https://hexaterminal.com/api/revalidate', $workflow);
        $this->assertStringContainsString('REVALIDATE_SECRET', $workflow);
        $this->assertStringContainsString('hash_equals($secret, (string) $frontend["REVALIDATE_SECRET"])', $workflow);
        $this->assertStringContainsString('Invalid production revalidation configuration.', $workflow);
        $this->assertStringContainsString(
            'tests/Feature/Deployment/ProductionRevalidationDeployGuardTest.php',
            $workflow,
        );
    }
}
