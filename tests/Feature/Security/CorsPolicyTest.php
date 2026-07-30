<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CORS is an authorization-adjacent but distinct control: it governs which
 * browser origins may read a cross-origin response, not whether a request is
 * authenticated. These tests prove the allow-list behaves correctly and that
 * the CMS (session-based, not in config('cors.paths')) is never opened
 * cross-origin by CORS misconfiguration.
 */
class CorsPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_origin_is_granted_cors_access(): void
    {
        config(['cors.allowed_origins' => ['https://app.hexaterminal.com']]);

        $res = $this->withHeaders(['Origin' => 'https://app.hexaterminal.com'])
            ->getJson('/api/v1/public/services');

        $res->assertOk();
        $this->assertSame('https://app.hexaterminal.com', $res->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_unapproved_origin_is_not_granted_cors_access(): void
    {
        config(['cors.allowed_origins' => ['https://app.hexaterminal.com']]);

        $res = $this->withHeaders(['Origin' => 'https://evil.example.com'])
            ->getJson('/api/v1/public/services');

        // The request still succeeds server-side (CORS is enforced by the
        // browser, not the server) but must not carry an ACAO header that
        // would let a browser expose the response to that origin.
        $res->assertOk();
        $this->assertNotSame('https://evil.example.com', $res->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_wildcard_origin_is_absent_when_an_explicit_allow_list_is_configured(): void
    {
        config(['cors.allowed_origins' => ['https://app.hexaterminal.com']]);

        $res = $this->withHeaders(['Origin' => 'https://app.hexaterminal.com'])
            ->getJson('/api/v1/public/services');

        $this->assertNotSame('*', $res->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_credentialed_requests_never_use_wildcard_origin(): void
    {
        config([
            'cors.allowed_origins' => ['https://app.hexaterminal.com'],
            'cors.supports_credentials' => true,
        ]);

        $res = $this->withHeaders(['Origin' => 'https://app.hexaterminal.com'])
            ->getJson('/api/v1/public/services');

        // Access-Control-Allow-Credentials: true is only valid paired with a
        // specific origin, never "*" -- browsers reject the wildcard
        // combination outright, but assert our config never produces it.
        if ($res->headers->get('Access-Control-Allow-Credentials') === 'true') {
            $this->assertNotSame('*', $res->headers->get('Access-Control-Allow-Origin'));
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_preflight_request_is_handled_correctly(): void
    {
        config(['cors.allowed_origins' => ['https://app.hexaterminal.com']]);

        $res = $this->withHeaders([
            'Origin' => 'https://app.hexaterminal.com',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/public/services');

        $res->assertSuccessful();
        $this->assertSame('https://app.hexaterminal.com', $res->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_public_read_endpoint_remains_usable_with_no_origin_header(): void
    {
        // Same-origin / server-to-server requests carry no Origin header at
        // all and must not be blocked by CORS policy (CORS is a browser-only
        // concept; the endpoint itself has no auth requirement).
        $this->getJson('/api/v1/public/services')->assertOk();
    }

    public function test_cms_routes_are_not_covered_by_cors_policy(): void
    {
        // config('cors.paths') is ['api/*', 'sanctum/csrf-cookie'] -- /cms is
        // deliberately outside it, so no ACAO header is ever emitted for the
        // session-authenticated CMS regardless of the Origin sent.
        $res = $this->withHeaders(['Origin' => 'https://evil.example.com'])->get('/cms');

        $this->assertNull($res->headers->get('Access-Control-Allow-Origin'));
    }
}
