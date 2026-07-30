<?php

namespace Tests\Feature\Security;

use App\Models\EstimatorVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Focused replay/abuse gaps not already covered by
 * tests/Feature/Api/V1/LeadsTest.php (basic rate limit + honeypot) or
 * tests/Feature/Pricing/EstimatorApiTest.php (repeat lead-conversion
 * idempotency). Uses deterministic time control (Carbon::setTestNow), never
 * real sleeps.
 */
class ReplayAndAbuseTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_duplicate_lead_within_the_replay_window_is_suppressed_not_duplicated(): void
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $payload = [
            'name' => 'Prospect', 'email' => 'dupe@example.com',
            'summary' => 'We need a CRM system built for our sales team.',
        ];

        $this->postJson('/api/v1/public/leads', $payload)->assertCreated();

        Carbon::setTestNow('2026-01-01 12:05:00'); // +5 min, inside the 10-min window
        $this->postJson('/api/v1/public/leads', $payload)->assertCreated();

        $this->assertDatabaseCount('contact_leads', 1);
    }

    public function test_duplicate_lead_after_the_replay_window_creates_a_new_record(): void
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $payload = [
            'name' => 'Prospect', 'email' => 'dupe2@example.com',
            'summary' => 'We need a CRM system built for our sales team.',
        ];

        $this->postJson('/api/v1/public/leads', $payload)->assertCreated();

        Carbon::setTestNow('2026-01-01 12:11:00'); // +11 min, outside the 10-min window
        $this->postJson('/api/v1/public/leads', $payload)->assertCreated();

        $this->assertDatabaseCount('contact_leads', 2);
    }

    public function test_estimate_creation_endpoint_is_rate_limited(): void
    {
        EstimatorVersion::create([
            'key' => 'v1', 'label' => 'V1', 'status' => 'active', 'is_active' => true,
            'base_currency' => 'USD', 'currency_rates' => ['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75],
            'floor_min' => 4000, 'ceiling_max' => 400000,
        ]);

        $payload = ['currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas']];

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/v1/public/estimates', $payload);
        }

        $this->postJson('/api/v1/public/estimates', $payload)->assertStatus(429);
    }

    public function test_legacy_write_endpoint_stays_blocked_even_with_a_seemingly_valid_payload(): void
    {
        // Proves the legacy:api gate runs BEFORE any controller/spam logic --
        // disabling the surface blocks writes unconditionally, not merely
        // when spam heuristics (honeypot, Turnstile) happen to trigger.
        config(['legacy.api' => false]);

        $res = $this->postJson('/api/review/store', [
            'name' => 'Legit Looking', 'content' => 'A perfectly normal review.', 'rating' => 5,
        ]);

        $res->assertStatus(404);
        $this->assertDatabaseCount('reviews', 0);
    }
}
