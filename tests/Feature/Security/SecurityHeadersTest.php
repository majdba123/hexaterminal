<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Proves the documented security-header baseline (config/security.php) is
 * applied to Laravel-origin responses, that CSP is Report-Only by default, and
 * that HSTS is correctly gated to secure production requests only.
 */
class SecurityHeadersTest extends TestCase
{
    public function test_baseline_headers_present_on_api_responses(): void
    {
        $res = $this->getJson('/api/health');

        $res->assertOk();
        $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $res->headers->get('Referrer-Policy'));
        $this->assertSame('SAMEORIGIN', $res->headers->get('X-Frame-Options'));
        $this->assertNotNull($res->headers->get('Permissions-Policy'));
        $this->assertSame('same-origin', $res->headers->get('Cross-Origin-Opener-Policy'));
    }

    public function test_csp_is_report_only_by_default(): void
    {
        $res = $this->getJson('/api/health');

        $this->assertNotNull($res->headers->get('Content-Security-Policy-Report-Only'));
        $this->assertNull($res->headers->get('Content-Security-Policy'));

        $csp = $res->headers->get('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        // Must never be a wildcard default policy.
        $this->assertStringNotContainsString('default-src *', $csp);
    }

    public function test_csp_can_be_enforced_via_config(): void
    {
        config(['security.csp.enforce' => true]);

        $res = $this->getJson('/api/health');

        $this->assertNotNull($res->headers->get('Content-Security-Policy'));
        $this->assertNull($res->headers->get('Content-Security-Policy-Report-Only'));
    }

    public function test_hsts_absent_on_non_secure_request(): void
    {
        config(['security.hsts.enabled' => true]);

        // Test requests are HTTP and env is "testing" -> HSTS must not appear.
        $res = $this->getJson('/api/health');

        $this->assertNull($res->headers->get('Strict-Transport-Security'));
    }
}
