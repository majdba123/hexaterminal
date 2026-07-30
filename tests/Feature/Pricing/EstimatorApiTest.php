<?php

namespace Tests\Feature\Pricing;

use App\Models\ContactLead;
use App\Models\CostEstimate;
use App\Models\EstimatorQuestion;
use App\Models\EstimatorRule;
use App\Models\EstimatorVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatorApiTest extends TestCase
{
    use RefreshDatabase;

    private function activeVersion(): EstimatorVersion
    {
        $version = EstimatorVersion::create([
            'key' => 'v1', 'label' => 'V1', 'status' => 'active', 'is_active' => true,
            'base_currency' => 'USD', 'currency_rates' => ['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75],
            'floor_min' => 4000, 'ceiling_max' => 400000,
        ]);
        EstimatorQuestion::create([
            'estimator_version_id' => $version->id, 'key' => 'build', 'step' => 1, 'type' => 'single_select',
            'prompt' => ['en' => 'Build?'], 'options' => [['key' => 'saas', 'label' => ['en' => 'SaaS']]],
        ]);
        EstimatorRule::create([
            'estimator_version_id' => $version->id, 'driver' => 'base', 'question_key' => 'build',
            'option_key' => 'saas', 'effect' => 'base', 'amount_min' => 20000, 'amount_max' => 40000,
            'weeks_min' => 8, 'weeks_max' => 14, 'complexity_weight' => 3, 'sort_order' => 1,
        ]);

        return $version;
    }

    public function test_config_returns_active_version_questions(): void
    {
        $this->activeVersion();
        $res = $this->getJson('/api/v1/public/estimator?locale=en');

        $res->assertOk();
        $this->assertTrue($res->json('data.available'));
        $this->assertSame('v1', $res->json('data.version'));
        $this->assertCount(1, $res->json('data.questions'));
    }

    public function test_config_reports_unavailable_without_active_version(): void
    {
        $res = $this->getJson('/api/v1/public/estimator?locale=en');
        $this->assertFalse($res->json('data.available'));
    }

    public function test_estimate_is_created_without_an_email(): void
    {
        $this->activeVersion();
        $res = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ]);

        $res->assertCreated();
        $this->assertNotEmpty($res->json('data.public_uuid'));
        $this->assertGreaterThan(0, $res->json('data.amount_min'));
        $this->assertArrayHasKey('amount_max', $res->json('data'));
        // No lead was required or created.
        $this->assertSame(0, ContactLead::count());
        $this->assertFalse($res->json('data.has_lead'));
    }

    public function test_result_excludes_internal_fields(): void
    {
        $this->activeVersion();
        $create = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ]);
        $data = $create->json('data');

        $this->assertArrayNotHasKey('base_amount_min', $data);
        $this->assertArrayNotHasKey('estimator_version_id', $data);
        $this->assertArrayNotHasKey('session_id', $data);
    }

    public function test_estimate_creation_rejected_without_active_version(): void
    {
        $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'answers' => ['build' => 'saas'],
        ])->assertStatus(503);
    }

    public function test_estimate_can_be_retrieved_by_uuid(): void
    {
        $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $this->getJson("/api/v1/public/estimates/{$uuid}?locale=en")
            ->assertOk()
            ->assertJsonPath('data.public_uuid', $uuid);
    }

    public function test_unknown_estimate_is_404(): void
    {
        $this->getJson('/api/v1/public/estimates/00000000-0000-0000-0000-000000000000?locale=en')
            ->assertNotFound();
    }

    public function test_expired_estimate_returns_410(): void
    {
        $version = $this->activeVersion();
        $estimate = CostEstimate::create([
            'estimator_version_id' => $version->id, 'locale' => 'en', 'currency' => 'USD',
            'answers' => ['build' => 'saas'], 'base_amount_min' => 20000, 'base_amount_max' => 40000,
            'amount_min' => 20000, 'amount_max' => 40000, 'timeline_weeks_min' => 8, 'timeline_weeks_max' => 14,
            'complexity' => 'advanced', 'confidence' => 'medium', 'cost_drivers' => [],
            'expires_at' => now()->subDay(),
        ]);

        $this->getJson("/api/v1/public/estimates/{$estimate->public_uuid}?locale=en")->assertStatus(410);
    }

    public function test_submitting_contact_links_lead_with_estimate_signals(): void
    {
        $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $res = $this->postJson("/api/v1/public/estimates/{$uuid}/lead", [
            'name' => 'Buyer', 'email' => 'buyer@example.com', 'company' => 'Acme',
            'requested_action' => 'book_call', 'utm' => ['source' => 'linkedin'],
        ]);

        $res->assertCreated();
        $lead = ContactLead::first();
        $this->assertSame('cost_estimate', $lead->intent);
        $this->assertSame('linkedin', $lead->utm['source']);
        $factors = array_column($lead->score_breakdown, 'factor');
        $this->assertContains('completed_estimate', $factors);

        $estimate = CostEstimate::where('public_uuid', $uuid)->first();
        $this->assertSame($lead->id, $estimate->contact_lead_id);
        $this->assertSame('discovery_requested', $estimate->status);
    }

    public function test_lead_submission_never_auto_rejects(): void
    {
        $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $this->postJson("/api/v1/public/estimates/{$uuid}/lead", [
            'name' => 'Buyer', 'email' => 'low@example.com',
        ])->assertCreated();

        // A lead is always created; scoring only orders, never rejects.
        $this->assertSame(1, ContactLead::count());
        $this->assertNotSame('spam', ContactLead::first()->status);
    }

    public function test_duplicate_submission_is_idempotent(): void
    {
        $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $payload = ['name' => 'Buyer', 'email' => 'dup@example.com'];
        $this->postJson("/api/v1/public/estimates/{$uuid}/lead", $payload)->assertCreated();
        $this->postJson("/api/v1/public/estimates/{$uuid}/lead", $payload)->assertCreated();

        $this->assertSame(1, ContactLead::count());
    }

    public function test_historical_estimate_keeps_its_version_after_a_new_one_activates(): void
    {
        $v1 = $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $estimate = CostEstimate::where('public_uuid', $uuid)->first();
        $this->assertSame($v1->id, $estimate->estimator_version_id);

        // Activate a new version.
        $v2 = EstimatorVersion::create([
            'key' => 'v2', 'label' => 'V2', 'status' => 'draft', 'is_active' => false,
            'base_currency' => 'USD', 'floor_min' => 4000, 'ceiling_max' => 400000,
        ]);
        $v2->activate();

        $estimate->refresh();
        $this->assertSame($v1->id, $estimate->estimator_version_id);
        $v1->refresh();
        $this->assertFalse($v1->is_active);
        $this->assertSame('archived', $v1->status);
    }

    public function test_honeypot_silently_succeeds_without_creating_a_lead(): void
    {
        $this->activeVersion();
        $uuid = $this->postJson('/api/v1/public/estimates', [
            'currency' => 'USD', 'locale' => 'en', 'answers' => ['build' => 'saas'],
        ])->json('data.public_uuid');

        $this->postJson("/api/v1/public/estimates/{$uuid}/lead", [
            'name' => 'Bot', 'email' => 'bot@example.com', 'website' => 'http://spam.example',
        ])->assertCreated();

        $this->assertSame(0, ContactLead::count());
    }
}
