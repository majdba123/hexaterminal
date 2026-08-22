<?php

namespace Tests\Feature\Deployment;

use Tests\TestCase;

class ProductionMediaSmokeGuardTest extends TestCase
{
    public function test_production_deploy_checks_a_tls_validated_public_media_response(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/deploy-production.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString(
            'MEDIA_SMOKE_URL="https://api.hexaterminal.com/storage/service-offerings/custom-erp-crm-systems.png"',
            $workflow,
        );
        $this->assertStringContainsString('MEDIA_HEADERS="$(curl', $workflow);
        $this->assertStringContainsString('--head', $workflow);
        $this->assertStringContainsString('--fail', $workflow);
        $this->assertStringContainsString("^content-type: image/(png|jpeg|webp|avif|gif)", $workflow);
        $this->assertStringContainsString("^content-type: text/html", $workflow);
        $this->assertStringContainsString("^x-powered-by: next\\.js", $workflow);
        $this->assertStringNotContainsString('--insecure', $workflow);
        $this->assertStringContainsString(
            'tests/Feature/Deployment/ProductionMediaSmokeGuardTest.php',
            $workflow,
        );
    }
}
