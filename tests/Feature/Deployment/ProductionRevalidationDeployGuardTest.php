<?php

namespace Tests\Feature\Deployment;

use Tests\TestCase;

class ProductionRevalidationDeployGuardTest extends TestCase
{
    public function test_production_deploy_validates_revalidation_wiring_without_logging_secrets(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/deploy-production.yml'));
        $syncScript = file_get_contents(base_path('deploy/production/sync-revalidation-config.php'));

        $this->assertIsString($workflow);
        $this->assertIsString($syncScript);
        $this->assertStringContainsString('config("revalidation.enabled")', $workflow);
        $this->assertStringContainsString('php deploy/production/sync-revalidation-config.php', $workflow);
        $this->assertStringContainsString('REVALIDATION_SECRET', $syncScript);
        $this->assertStringContainsString('https://hexaterminal.com/api/revalidate', $workflow);
        $this->assertStringContainsString('REVALIDATE_SECRET', $syncScript);
        $this->assertStringContainsString("if (\$secret === '')", $syncScript);
        $this->assertStringContainsString('hash_equals($secret, (string) $frontend["REVALIDATE_SECRET"])', $workflow);
        $this->assertStringContainsString('Invalid production revalidation configuration:', $workflow);
        $this->assertStringContainsString('backend disabled', $workflow);
        $this->assertStringContainsString('backend URL is incorrect', $workflow);
        $this->assertStringContainsString('backend secret is missing', $workflow);
        $this->assertStringContainsString('frontend secret is missing', $workflow);
        $this->assertStringContainsString('backend and frontend secrets differ', $workflow);
        $this->assertStringContainsString(
            'tests/Feature/Deployment/ProductionRevalidationDeployGuardTest.php',
            $workflow,
        );
    }
}
