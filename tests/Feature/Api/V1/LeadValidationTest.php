<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class LeadValidationTest extends TestCase
{
    public function test_start_project_requires_a_summary(): void
    {
        $response = $this->postJson('/api/v1/public/leads', [
            'intent' => 'start_project',
            'name' => 'Validation Tester',
            'email' => 'validation@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonValidationErrors(['summary']);
    }

    public function test_start_project_rejects_a_summary_shorter_than_ten_characters(): void
    {
        $response = $this->postJson('/api/v1/public/leads', [
            'intent' => 'start_project',
            'name' => 'Validation Tester',
            'email' => 'validation@example.com',
            'summary' => 'short',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonValidationErrors(['summary']);
    }

    public function test_general_contact_uses_the_same_summary_contract(): void
    {
        $response = $this->postJson('/api/v1/public/leads', [
            'intent' => 'general_contact',
            'name' => 'Validation Tester',
            'email' => 'validation@example.com',
            'summary' => 'short',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['summary']);
    }
}
