<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\WebsitePreviewSeeder;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Database\Seeders\DatabaseSeeder;
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

    public function test_database_seeder_bootstraps_only_admin_and_approved_services(): void
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
        $this->assertDatabaseCount('systems', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('team_members', 0);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('faqs', 0);
        $this->assertDatabaseCount('engagement_models', 0);

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonPath('data.services.0.slug', Service::CORE_SERVICE_SLUGS[0])
            ->assertJsonCount(3, 'data.services')
            ->assertJsonCount(0, 'data.featured_systems')
            ->assertJsonCount(0, 'data.featured_case_studies');

        $this->getJson('/api/v1/public/services/custom-erp-crm-systems?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.name', 'أنظمة ERP وCRM مخصصة')
            ->assertJsonPath('data.cover_image', url('/api/storage/service-offerings/custom-erp-crm-systems.png'));
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
