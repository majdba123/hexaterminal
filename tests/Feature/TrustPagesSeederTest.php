<?php

namespace Tests\Feature;

use App\Models\TrustPage;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TrustPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustPagesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_supported_localized_trust_pages_with_seo_and_required_approvals(): void
    {
        $this->seed(TrustPagesSeeder::class);

        $this->assertDatabaseCount('trust_pages', 6);
        $this->assertSame(
            ['security', 'engineering-standards', 'process', 'technology', 'responsible-ai', 'accessibility'],
            TrustPage::query()->orderBy('sort_order')->pluck('slug')->all()
        );

        $security = TrustPage::query()->where('slug', 'security')->firstOrFail();
        $this->assertSame('security', $security->page_type);
        $this->assertTrue($security->is_published);
        $this->assertTrue($security->founder_approved);
        $this->assertTrue($security->security_approved);
        $this->assertTrue($security->noindex);
        $this->assertSame('Security', $security->getTranslation('title', 'en'));
        $this->assertSame('الأمن', $security->getTranslation('title', 'ar'));
        $this->assertCount(4, $security->getTranslation('sections', 'en'));
        $this->assertCount(2, $security->getTranslation('faqs', 'en'));
        $this->assertSame('HexaTerminal Security Approach', $security->seo?->getTranslation('title', 'en'));
        $this->assertSame(
            'How HexaTerminal approaches access control, validation, deployment security, and sensitive data handling in custom software projects.',
            $security->seo?->getTranslation('description', 'en')
        );

        $this->assertNull(TrustPage::query()->where('slug', 'data-privacy')->first());

        $this->getJson('/api/v1/public/trust-pages?locale=en')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.slug', 'security')
            ->assertJsonPath('data.0.title', 'Security')
            ->assertJsonPath('data.0.page_type', 'security')
            ->assertJsonPath('data.0.seo.title', 'HexaTerminal Security Approach')
            ->assertJsonPath('data.2.slug', 'process');

        $this->getJson('/api/v1/public/trust-pages/responsible-ai?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.title', 'الذكاء الاصطناعي المسؤول')
            ->assertJsonPath('data.page_type', 'responsible_ai')
            ->assertJsonPath('data.sections.0.heading', 'استخدام الذكاء الاصطناعي عندما يضيف قيمة حقيقية');
    }

    public function test_trust_pages_seeder_is_idempotent_by_slug(): void
    {
        $this->seed(TrustPagesSeeder::class);
        $this->seed(TrustPagesSeeder::class);

        $this->assertDatabaseCount('trust_pages', 6);
        $this->assertSame(6, TrustPage::query()->count());
        $this->assertSame(6, TrustPage::query()->has('seo')->count());
    }

    public function test_database_seeder_includes_supported_trust_pages_without_touching_static_privacy_page_behavior(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('trust_pages', 6);

        $this->getJson('/api/v1/public/trust-pages/engineering-standards?locale=en')
            ->assertOk()
            ->assertJsonPath('data.title', 'Engineering Standards');

        $this->getJson('/api/v1/public/trust-pages/process?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.title', 'آلية العمل');
    }
}
