<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContactLead;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadIntentAndAttributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_call_intent_requires_a_contact_channel(): void
    {
        $this->postJson('/api/v1/public/leads', [
            'intent' => 'book_call',
            'name' => 'Prospect',
            'email' => 'p@example.com',
        ])->assertStatus(422)->assertJsonValidationErrors('phone');

        $this->postJson('/api/v1/public/leads', [
            'intent' => 'book_call',
            'name' => 'Prospect',
            'email' => 'p2@example.com',
            'whatsapp' => '+123456789',
        ])->assertCreated();
    }

    public function test_general_contact_does_not_require_budget_or_timeline(): void
    {
        $this->postJson('/api/v1/public/leads', [
            'intent' => 'general_contact',
            'name' => 'Prospect',
            'email' => 'p3@example.com',
            'summary' => 'Just a general question about your services.',
        ])->assertCreated();
    }

    public function test_attribution_fields_are_persisted(): void
    {
        $this->postJson('/api/v1/public/leads', [
            'name' => 'Prospect', 'email' => 'p4@example.com', 'summary' => 'Real project inquiry text.',
            'landing_page' => '/en/services/crm', 'source_page' => '/en/start-a-project',
            'first_touch_at' => now()->subHour()->toIso8601String(),
            'utm' => ['source' => 'linkedin', 'medium' => 'social', 'campaign' => 'launch'],
        ])->assertCreated();

        $lead = ContactLead::where('email', 'p4@example.com')->firstOrFail();
        $this->assertSame('/en/services/crm', $lead->landing_page);
        $this->assertSame('linkedin', $lead->utm['source']);
        $this->assertNotNull($lead->first_touch_at);
    }

    public function test_requested_service_slug_resolves_to_internal_id(): void
    {
        $service = Service::create(['slug' => 'crm-implementation', 'name' => ['en' => 'CRM'], 'is_published' => true]);

        $this->postJson('/api/v1/public/leads', [
            'name' => 'Prospect', 'email' => 'p5@example.com', 'summary' => 'Interested in this service.',
            'requested_service_slug' => 'crm-implementation',
        ])->assertCreated();

        $lead = ContactLead::where('email', 'p5@example.com')->firstOrFail();
        $this->assertSame($service->id, $lead->requested_service_id);
    }

    public function test_lead_is_scored_deterministically_on_submission(): void
    {
        $this->postJson('/api/v1/public/leads', [
            'name' => 'Prospect', 'email' => 'p6@example.com', 'company' => 'Acme',
            'budget_range' => '$10k-$50k', 'timeline' => 'This quarter',
            'summary' => str_repeat('A detailed project summary. ', 10),
        ])->assertCreated();

        $lead = ContactLead::where('email', 'p6@example.com')->firstOrFail();
        $this->assertNotNull($lead->score);
        $this->assertGreaterThan(0, $lead->score);
        $this->assertNotEmpty($lead->score_breakdown);
    }

    public function test_duplicate_submission_within_the_replay_window_is_idempotent(): void
    {
        $payload = ['name' => 'Prospect', 'email' => 'p7@example.com', 'summary' => 'A real inquiry about services.'];

        $first = $this->postJson('/api/v1/public/leads', $payload)->assertCreated();
        $second = $this->postJson('/api/v1/public/leads', $payload)->assertCreated();

        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertSame(1, ContactLead::where('email', 'p7@example.com')->count());
    }
}
