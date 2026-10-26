<?php

namespace Tests\Feature\Security;

use App\Services\PublicClientIp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProxyThrottleTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-proxy-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.public_proxy.secret' => self::SECRET]);
    }

    private function postLeadAs(?string $visitorIp, string $secret = self::SECRET, string $email = 'visitor@example.com')
    {
        $headers = [PublicClientIp::SECRET_HEADER => $secret];
        if ($visitorIp !== null) {
            $headers[PublicClientIp::CLIENT_IP_HEADER] = $visitorIp;
        }

        return $this->withHeaders($headers)->postJson('/api/v1/public/leads', [
            'name' => 'Visitor',
            'email' => $email,
            'summary' => 'A realistic project inquiry.',
        ]);
    }

    public function test_proxied_visitors_get_separate_buckets(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postLeadAs('203.0.113.10', email: "a{$i}@example.com")->assertCreated();
        }

        $this->postLeadAs('203.0.113.10', email: 'a5@example.com')->assertStatus(429);

        // A different visitor behind the same proxy is not locked out.
        $this->postLeadAs('203.0.113.20', email: 'b@example.com')->assertCreated();
    }

    public function test_forwarded_ip_is_ignored_without_the_proxy_secret(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postLeadAs('198.51.100.'.$i, secret: 'wrong', email: "s{$i}@example.com")->assertCreated();
        }

        // Rotating a spoofed header does not escape the caller's own bucket.
        $this->postLeadAs('198.51.100.99', secret: 'wrong', email: 's9@example.com')->assertStatus(429);
    }

    public function test_invalid_forwarded_ip_falls_back_to_connection_ip(): void
    {
        $request = request()->duplicate();
        $request->headers->set(PublicClientIp::SECRET_HEADER, self::SECRET);
        $request->headers->set(PublicClientIp::CLIENT_IP_HEADER, 'not-an-ip');

        $this->assertNull(PublicClientIp::forwarded($request));
        $this->assertSame((string) $request->ip(), PublicClientIp::resolve($request));
    }

    public function test_proxy_is_not_trusted_when_no_secret_is_configured(): void
    {
        config(['security.public_proxy.secret' => '']);

        $request = request()->duplicate();
        $request->headers->set(PublicClientIp::SECRET_HEADER, '');
        $request->headers->set(PublicClientIp::CLIENT_IP_HEADER, '203.0.113.10');

        $this->assertFalse(PublicClientIp::isTrustedProxy($request));
    }
}
