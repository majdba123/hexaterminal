<?php

namespace Tests\Feature\Api\V1;

use App\Models\CompanySetting;
use Database\Seeders\CompanySettingsSeeder;
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

    public function test_company_settings_seeder_creates_one_localized_row_and_keeps_internal_fields_private(): void
    {
        $this->seed(CompanySettingsSeeder::class);

        $this->assertDatabaseCount('company_settings', 1);

        $settings = CompanySetting::current();
        $this->assertSame('HexaTerminal', $settings->getTranslation('company_name', 'en'));
        $this->assertSame('HexaTerminal', $settings->getTranslation('company_name', 'ar'));
        $this->assertSame('Software systems built around real business needs.', $settings->getTranslation('tagline', 'en'));
        $this->assertSame('أنظمة برمجية مبنية حول احتياجات الأعمال الحقيقية.', $settings->getTranslation('tagline', 'ar'));
        $this->assertSame(
            'HexaTerminal is a software development company that builds custom ERP and CRM systems, web platforms, mobile applications, e-commerce solutions, and business websites.',
            $settings->getTranslation('description', 'en'),
        );
        $this->assertSame(
            'HexaTerminal شركة برمجيات تبني أنظمة ERP وCRM مخصصة، ومنصات ويب، وتطبيقات جوال، وحلول تجارة إلكترونية، ومواقع أعمال.',
            $settings->getTranslation('description', 'ar'),
        );
        $this->assertSame(
            'Custom software, platforms, and digital systems for growing businesses.',
            $settings->getTranslation('footer_note', 'en'),
        );
        $this->assertSame(
            'برمجيات مخصصة ومنصات وأنظمة رقمية للأعمال النامية.',
            $settings->getTranslation('footer_note', 'ar'),
        );
        $this->assertSame('majdbayer77@gmail.com', $settings->email);
        $this->assertSame('+963935027218', $settings->phone);
        $this->assertSame('majdbayer77@gmail.com', $settings->lead_recipients);
        $this->assertNull($settings->whatsapp);
        $this->assertNull($settings->booking_url);
        $this->assertNull($settings->default_og_image);
        $this->assertNull($settings->analytics_provider);
        $this->assertNull($settings->analytics_site_id);
        $this->assertSame([], $settings->social_links);
        $this->assertSame([], $settings->getTranslations('address'));

        $response = $this->getJson('/api/v1/public/settings')->assertOk();
        $response->assertJsonPath('data.company_name', 'HexaTerminal');
        $response->assertJsonPath('data.tagline', 'Software systems built around real business needs.');
        $response->assertJsonPath('data.email', 'majdbayer77@gmail.com');
        $response->assertJsonPath('data.phone', '+963935027218');
        $response->assertJsonPath('data.social_links', []);
        $response->assertJsonMissingPath('data.lead_recipients');
        $response->assertJsonMissingPath('data.analytics_provider');
        $response->assertJsonMissingPath('data.analytics_site_id');

        $this->seed(CompanySettingsSeeder::class);

        $this->assertDatabaseCount('company_settings', 1);
    }
}
