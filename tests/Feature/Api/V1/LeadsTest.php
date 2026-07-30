<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContactLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_lead_is_created(): void
    {
        $response = $this->postJson('/api/v1/public/leads', [
            'name' => 'Prospect',
            'email' => 'prospect@example.com',
            'company' => 'Acme Inc',
            'project_type' => 'CRM',
            'summary' => 'We need a CRM.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_leads', ['email' => 'prospect@example.com', 'status' => ContactLead::STATUS_NEW]);
    }

    public function test_missing_required_fields_are_rejected(): void
    {
        $this->postJson('/api/v1/public/leads', ['name' => 'Only Name'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_honeypot_field_silently_rejects_without_saving(): void
    {
        $response = $this->postJson('/api/v1/public/leads', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('contact_leads', 0);
    }

    public function test_leads_endpoint_is_rate_limited(): void
    {
        // Summary present because the default start_project intent requires
        // one; duplicate submissions inside the window return 201 (replay
        // suppression) but still consume the throttle.
        $payload = ['name' => 'Bot', 'email' => 'bot@example.com', 'summary' => 'A realistic project inquiry.'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/public/leads', $payload)->assertCreated();
        }

        $this->postJson('/api/v1/public/leads', $payload)->assertStatus(429);
    }
}
