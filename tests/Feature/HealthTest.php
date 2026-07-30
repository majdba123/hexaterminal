<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_liveness_returns_ok_without_touching_dependencies(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('service', 'hexa-terminal-api')
            ->assertJsonStructure(['status', 'service', 'environment', 'version', 'time']);
    }

    public function test_readiness_reports_dependency_checks_and_passes_when_healthy(): void
    {
        $this->getJson('/api/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.cache', true);
    }

    public function test_health_endpoints_never_leak_secrets(): void
    {
        $body = $this->getJson('/api/health/ready')->getContent();

        // No credentials, connection strings, or app key material.
        $this->assertStringNotContainsString('password', strtolower($body));
        $this->assertStringNotContainsString('secret', strtolower($body));
        $this->assertStringNotContainsString(config('app.key'), $body);
    }

    public function test_health_endpoints_are_not_rate_limited(): void
    {
        // The throttle:api limiter is 60/min; health probes must bypass it.
        for ($i = 0; $i < 65; $i++) {
            $this->getJson('/api/health')->assertOk();
        }
    }
}
