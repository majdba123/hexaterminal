<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_store_is_rate_limited(): void
    {
        $payload = ['name' => 'Bot', 'content' => 'spam', 'rating' => 5];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/review/store', $payload)->assertCreated();
        }

        $this->postJson('/api/review/store', $payload)
            ->assertStatus(429);
    }

    public function test_contact_store_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '+100000000',
            'subject' => 'spam',
            'message' => 'spam body',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/contact_us/store', $payload)->assertCreated();
        }

        $this->postJson('/api/contact_us/store', $payload)
            ->assertStatus(429);
    }

    public function test_api_login_is_rate_limited(): void
    {
        $payload = ['email' => 'nobody@example.com', 'password' => 'wrong-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', $payload);
        }

        $this->postJson('/api/login', $payload)->assertStatus(429);
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $payload = ['email' => 'nobody@example.com', 'password' => 'wrong-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/admin/login', $payload);
        }

        $this->postJson('/admin/login', $payload)->assertStatus(429);
    }
}
