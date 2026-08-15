<?php

namespace Tests\Feature\Security;

use App\Models\CaseStudy;
use App\Models\CompanySetting;
use App\Models\Industry;
use App\Models\PricingProfile;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\WebsitePreviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_seeder_creates_the_approved_cms_preview_dataset_without_trust_data(): void
    {
        $this->seed(WebsitePreviewSeeder::class);

        $this->assertDatabaseCount('systems', 3);
        $this->assertDatabaseCount('case_studies', 3);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertDatabaseCount('industries', 3);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('team_members', 0);
        $this->assertDatabaseCount('faqs', 0);
        $this->assertDatabaseCount('engagement_models', 0);

        $this->assertSame(Service::CORE_SERVICE_SLUGS, Service::published()->orderBy('sort_order')->pluck('slug')->all());
        $this->assertSame(CaseStudy::CLASSIFICATIONS, CaseStudy::published()->orderBy('sort_order')->pluck('project_classification')->all());
        $this->assertSame(3, System::published()->featured()->count());
        $this->assertSame(3, Industry::published()->count());

        $service = Service::where('slug', Service::CORE_SERVICE_SLUGS[0])->firstOrFail();
        $this->assertNotSame('', $service->getTranslation('name', 'ar'));
        $this->assertNotSame('', $service->getTranslation('description', 'ar'));

        $caseStudy = CaseStudy::published()->with(['serviceOffering', 'system', 'industries'])->firstOrFail();
        $this->assertNotNull($caseStudy->serviceOffering);
        $this->assertNotNull($caseStudy->system);
        $this->assertTrue($caseStudy->industries->isNotEmpty());
        $this->assertNull($caseStudy->client_name);
        $this->assertNull($caseStudy->project_url);
    }

    public function test_database_seeder_bootstraps_only_admin_approved_services_company_settings_team_engagement_models_and_faqs(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'bootstrap-admin@hexaterminal.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertDatabaseCount('company_settings', 1);
        $this->assertDatabaseCount('team_members', 1);
        $this->assertDatabaseCount('systems', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('faqs', 10);
        $this->assertDatabaseCount('engagement_models', 4);
        $this->assertDatabaseCount('pricing_profiles', 0);

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonPath('data.services.0.slug', Service::CORE_SERVICE_SLUGS[0])
            ->assertJsonCount(3, 'data.services')
            ->assertJsonCount(0, 'data.featured_systems')
            ->assertJsonCount(0, 'data.featured_case_studies');

        $this->getJson('/api/v1/public/services/custom-erp-crm-systems?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.name', 'أنظمة ERP وCRM مخصصة')
            ->assertJsonPath('data.cover_image', url('/storage/service-offerings/custom-erp-crm-systems.png'));

        $settings = CompanySetting::current();
        $this->assertSame('HexaTerminal', $settings->getTranslation('company_name', 'en'));
        $this->assertSame('HexaTerminal', $settings->getTranslation('company_name', 'ar'));
        $this->assertSame('Software systems built around real business needs.', $settings->getTranslation('tagline', 'en'));
        $this->assertSame('أنظمة برمجية مبنية حول احتياجات الأعمال الحقيقية.', $settings->getTranslation('tagline', 'ar'));
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

        $member = TeamMember::where('slug', 'majd-bayer')->firstOrFail();
        $this->assertSame('Majd', $member->first_name);
        $this->assertSame('Bayer', $member->last_name);
        $this->assertSame('Founder & Software Engineer', $member->getTranslation('position', 'en'));
        $this->assertSame('المؤسس ومهندس برمجيات', $member->getTranslation('position', 'ar'));
        $this->assertSame('team/majd-bayer.jpg', $member->photo);
        $this->assertTrue($member->is_published);
        $this->assertTrue($member->publication_consent);
        $this->assertTrue($member->is_founder);
        $this->assertTrue($member->seo_eligible);
        $this->assertTrue($member->person_jsonld_eligible);
        $this->assertSame('https://github.com/majdba123', $member->github_url);
        $this->assertNull($member->linkedin_url);

        $this->assertSame(
            ['discovery-sprint', 'fixed-scope-project', 'milestone-based-delivery', 'ongoing-support'],
            \App\Models\EngagementModel::published()->orderBy('sort_order')->pluck('slug')->all()
        );
        $this->assertSame(
            ['discovery_sprint', 'fixed_project', 'milestone_based', 'support_plan'],
            \App\Models\EngagementModel::published()->orderBy('sort_order')->pluck('billing_model')->all()
        );
        $this->assertSame(
            array_fill(0, 4, 'request_quote'),
            \App\Models\EngagementModel::published()->orderBy('sort_order')->pluck('pricing_display_mode')->all()
        );
        $this->assertSame([1, 2, 3, 4], \App\Models\EngagementModel::published()->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertSame(0, PricingProfile::count());

        $this->getJson('/api/v1/public/pricing?locale=en')
            ->assertOk()
            ->assertJsonCount(4, 'data.engagement_models')
            ->assertJsonPath('data.engagement_models.0.slug', 'discovery-sprint')
            ->assertJsonPath('data.engagement_models.0.pricing_display_mode', 'request_quote')
            ->assertJsonPath('data.engagement_models.0.pricing', null)
            ->assertJsonPath('data.estimator_available', false)
            ->assertJsonCount(0, 'data.faqs');

        $this->getJson('/api/v1/public/faqs?locale=en')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.question', 'How much does a custom software project cost?')
            ->assertJsonPath('data.0.category', null);
    }

    public function test_seeder_creates_no_user_when_credentials_missing(): void
    {
        config(['app.admin_email' => null, 'app.admin_password' => null]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_seeder_never_creates_the_old_default_account(): void
    {
        config(['app.admin_email' => null, 'app.admin_password' => null]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_seeder_rejects_short_passwords(): void
    {
        config(['app.admin_email' => 'boss@hexaterminal.test', 'app.admin_password' => 'short']);

        $this->expectException(\RuntimeException::class);

        (new UsersTableSeeder)->run();
    }

    public function test_seeder_creates_admin_from_config(): void
    {
        config([
            'app.admin_email' => 'boss@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseHas('users', ['email' => 'boss@hexaterminal.test', 'type' => 1]);
        $this->assertTrue(
            User::where('email', 'boss@hexaterminal.test')->first()->hasRole('admin')
        );
    }
}
