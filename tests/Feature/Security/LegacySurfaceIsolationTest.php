<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fail-closed isolation of the legacy public web, admin, and API surfaces.
 * The middleware reads config('legacy.*') at request time, so each test
 * toggles the flag directly -- no separate process or app rebuild needed.
 */
class LegacySurfaceIsolationTest extends TestCase
{
    use RefreshDatabase;

    // ── Legacy API ────────────────────────────────────────────────────

    public function test_legacy_api_returns_controlled_json_404_when_disabled(): void
    {
        config(['legacy.api' => false]);

        $res = $this->getJson('/api/services/index');

        $res->assertStatus(404)
            ->assertJson(['message' => 'This endpoint has been retired.']);
    }

    public function test_legacy_api_write_fails_closed_when_disabled(): void
    {
        config(['legacy.api' => false]);

        $this->postJson('/api/review/store', ['name' => 'x', 'content' => 'x', 'rating' => 5])
            ->assertStatus(404);
    }

    public function test_legacy_api_works_and_is_noindex_when_enabled(): void
    {
        config(['legacy.api' => true]);

        $res = $this->getJson('/api/services/index');

        $res->assertOk();
        $this->assertSame('noindex, nofollow', $res->headers->get('X-Robots-Tag'));
    }

    // ── Versioned API and health remain unaffected ────────────────────

    public function test_versioned_public_api_is_unaffected_by_legacy_api_flag(): void
    {
        config(['legacy.api' => false]);

        $this->getJson('/api/v1/public/services')->assertOk();
    }

    public function test_health_endpoints_are_unaffected_by_legacy_api_flag(): void
    {
        config(['legacy.api' => false]);

        $this->getJson('/api/health')->assertOk();
    }

    // ── Legacy public web ─────────────────────────────────────────────

    public function test_legacy_public_home_returns_404_when_disabled(): void
    {
        config(['legacy.public_web' => false]);

        $this->get('/')->assertNotFound();
    }

    public function test_legacy_public_home_is_noindex_when_enabled(): void
    {
        config(['legacy.public_web' => true]);

        $res = $this->get('/');

        // The page renders (2xx) or redirects internally; either way it must
        // carry the noindex tag so it can never compete with the Next.js page.
        $this->assertSame('noindex, nofollow', $res->headers->get('X-Robots-Tag'));
    }

    // ── Legacy admin ──────────────────────────────────────────────────

    public function test_legacy_admin_login_unreachable_when_disabled(): void
    {
        config(['legacy.admin' => false]);

        $this->get('/admin')->assertNotFound();
        $this->post('/admin/login', [])->assertNotFound();
    }

    public function test_legacy_admin_login_reachable_and_noindex_when_enabled(): void
    {
        config(['legacy.admin' => true]);

        $res = $this->get('/admin');

        $this->assertContains($res->getStatusCode(), [200, 302]);
        $this->assertSame('noindex, nofollow', $res->headers->get('X-Robots-Tag'));
    }

    public function test_filament_cms_remains_available_when_legacy_admin_disabled(): void
    {
        config(['legacy.admin' => false]);

        // /cms is registered by CmsPanelProvider, not the legacy surface, so it
        // must still respond (redirect to its own login for a guest).
        $res = $this->get('/cms');

        $this->assertContains($res->getStatusCode(), [200, 302]);
        $this->assertNotSame(404, $res->getStatusCode());
    }
}
