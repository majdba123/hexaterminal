<?php

namespace Tests\Feature\Api\V1;

use App\Models\CompanySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_endpoint_returns_public_fields_only(): void
    {
        CompanySetting::current()->update([
            'company_name' => ['en' => 'Hexa Terminal'],
            'email' => 'hello@hexaterminal.com',
            'lead_recipients' => 'internal-team@hexaterminal.com',
            'analytics_site_id' => 'secret-site-id',
        ]);

        $response = $this->getJson('/api/v1/public/settings')->assertOk();

        $this->assertSame('Hexa Terminal', $response->json('data.company_name'));
        $this->assertSame('hello@hexaterminal.com', $response->json('data.email'));
        // Operational fields must never be exposed publicly.
        $response->assertJsonMissingPath('data.lead_recipients');
        $response->assertJsonMissingPath('data.analytics_site_id');
        $response->assertJsonMissingPath('data.analytics_provider');
    }

    public function test_settings_endpoint_works_with_no_row_yet(): void
    {
        $this->getJson('/api/v1/public/settings')
            ->assertOk()
            ->assertJsonPath('data.email', null);
    }
}
