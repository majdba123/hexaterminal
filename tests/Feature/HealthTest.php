<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_liveness_returns_ok_without_touching_dependencies(): void
    {
        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('service', 'hexaterminal-backend')
            ->assertJsonStructure(['status', 'service']);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_readiness_reports_dependency_checks_and_passes_when_healthy(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('service', 'hexaterminal-backend')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.cache', 'ok');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_readiness_returns_service_unavailable_when_database_check_fails(): void
    {
        DB::shouldReceive('connection')
            ->once()
            ->andThrow(new RuntimeException('database unavailable'));

        $response = $this->getJson('/api/health/ready');

        $response
            ->assertStatus(503)
            ->assertJsonPath('status', 'not_ready')
            ->assertJsonPath('service', 'hexaterminal-backend')
            ->assertJsonPath('checks.database', 'failed')
            ->assertJsonPath('checks.cache', 'ok');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_health_endpoints_never_leak_secrets(): void
    {
        $body = $this->getJson('/api/health/ready')->getContent() ?? '';

        // No credentials, connection strings, or app key material.
        $this->assertStringNotContainsString('password', strtolower($body));
        $this->assertStringNotContainsString('secret', strtolower($body));
        $appKey = config('app.key');
        if (is_string($appKey) && $appKey !== '') {
            $this->assertStringNotContainsString($appKey, $body);
        }
    }

    public function test_health_endpoints_are_not_rate_limited(): void
    {
        // The throttle:api limiter is 60/min; health probes must bypass it.
        for ($i = 0; $i < 65; $i++) {
            $this->getJson('/api/health')->assertOk();
        }
    }
}
