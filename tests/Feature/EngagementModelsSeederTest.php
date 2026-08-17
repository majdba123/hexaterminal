<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\EngagementModel;
use App\Models\PricingProfile;
use App\Models\Service;
use App\Models\TeamMember;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EngagementModelsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementModelsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_models_seeder_creates_the_four_approved_localized_models(): void
    {
        $this->seed(EngagementModelsSeeder::class);

        $this->assertDatabaseCount('engagement_models', 4);
        $this->assertSame(
            ['discovery-sprint', 'fixed-scope-project', 'milestone-based-delivery', 'ongoing-support'],
            EngagementModel::orderBy('sort_order')->pluck('slug')->all()
        );
        $this->assertSame(
            ['discovery_sprint', 'fixed_project', 'milestone_based', 'support_plan'],
            EngagementModel::orderBy('sort_order')->pluck('billing_model')->all()
        );
        $this->assertSame(
            array_fill(0, 4, 'request_quote'),
            EngagementModel::orderBy('sort_order')->pluck('pricing_display_mode')->all()
        );
        $this->assertSame([1, 2, 3, 4], EngagementModel::orderBy('sort_order')->pluck('sort_order')->all());

        $discovery = EngagementModel::where('slug', 'discovery-sprint')->firstOrFail();
        $this->assertSame('Discovery Sprint', $discovery->getTranslation('title', 'en'));
        $this->assertSame('مرحلة الاكتشاف وتحديد النطاق', $discovery->getTranslation('title', 'ar'));
        $this->assertSame(
            [
                'Custom ERP & CRM projects',
                'Complex platforms',
                'Existing systems that need major changes',
                'Projects with unclear or evolving requirements',
            ],
            $discovery->getTranslation('buyer_fit', 'en')
        );
        $this->assertSame(
            [
                'أنظمة ERP وCRM المخصصة',
                'المنصات المعقدة',
                'الأنظمة الحالية التي تحتاج تغييرات كبيرة',
                'المشاريع ذات المتطلبات غير المكتملة أو المتغيرة',
            ],
            $discovery->getTranslation('buyer_fit', 'ar')
        );
        $this->assertSame(
            'Best for projects where the business problem is clear, but the workflows, requirements, integrations, or technical scope still need to be defined. During discovery, we review the current process, users, data, dependencies, risks, and required integrations. The goal is to turn an unclear request into a structured implementation scope.',
            $discovery->getTranslation('typical_scope', 'en')
        );
        $this->assertNull($discovery->cta_label);
        $this->assertSame('request_quote', $discovery->cta_intent);
        $this->assertSame('', $discovery->indicative_duration);
        $this->assertFalse($discovery->is_featured);
        $this->assertTrue($discovery->is_published);
        $this->assertSame(0, PricingProfile::count());
    }

    public function test_engagement_models_seeder_is_idempotent(): void
    {
        $this->seed(EngagementModelsSeeder::class);
        $this->seed(EngagementModelsSeeder::class);

        $this->assertDatabaseCount('engagement_models', 4);
        $this->assertSame(0, PricingProfile::count());
    }

    public function test_database_seeder_preserves_existing_approved_seed_data_and_adds_engagement_models(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertDatabaseCount('company_settings', 1);
        $this->assertDatabaseCount('team_members', 1);
        $this->assertDatabaseCount('engagement_models', 4);
        $this->assertDatabaseCount('pricing_profiles', 0);
        $this->assertDatabaseCount('systems', 1);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('case_studies', 1);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('faqs', 10);

        $this->assertSame(3, Service::count());
        $this->assertSame(1, CompanySetting::count());
        $this->assertSame(1, TeamMember::count());

        $this->getJson('/api/v1/public/pricing?locale=ar')
            ->assertOk()
            ->assertJsonCount(4, 'data.engagement_models')
            ->assertJsonPath('data.engagement_models.0.title', 'مرحلة الاكتشاف وتحديد النطاق')
            ->assertJsonPath('data.engagement_models.1.slug', 'fixed-scope-project')
            ->assertJsonPath('data.engagement_models.3.slug', 'ongoing-support');
    }
}
