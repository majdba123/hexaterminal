<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\CompanySetting;
use App\Models\FaqItem;
use App\Models\Service;
use App\Models\Testimonial;
use Database\Seeders\FounderContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the launch-safety guarantees of FounderContentSeeder: it may
 * never leave placeholder/unverified content publicly visible, and any
 * new content it introduces must land in a draft/unpublished state
 * pending human review.
 */
class FounderContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_mistranslated_legacy_services_are_unpublished(): void
    {
        Service::create([
            'slug' => 'legacy-garbled',
            'name' => ['en' => 'ملخص عربي'],
            'is_published' => true,
        ]);

        (new FounderContentSeeder)->run();

        $service = Service::where('slug', 'legacy-garbled')->firstOrFail();
        $this->assertFalse($service->is_published);
        $this->assertSame('draft', $service->status);
    }

    public function test_service_pillars_are_created_unpublished(): void
    {
        (new FounderContentSeeder)->run();

        $pillars = Service::whereIn('slug', [
            'saas-platforms', 'crm-erp-systems', 'ai-enabled-workflows',
            'backend-api-engineering', 'business-automation', 'custom-operational-software',
        ])->get();

        $this->assertCount(6, $pillars);
        foreach ($pillars as $pillar) {
            $this->assertFalse($pillar->is_published, "{$pillar->slug} must not be published automatically");
            $this->assertSame('in_review', $pillar->status);
            $this->assertNotEmpty($pillar->getTranslation('name', 'en'));
            $this->assertNotEmpty($pillar->getTranslation('name', 'ar'));
        }
    }

    public function test_demo_case_studies_are_unpublished_not_deleted(): void
    {
        CaseStudy::create([
            'slug' => 'demo-project',
            'title' => ['en' => 'Demo Project'],
            'is_published' => true,
            'legacy_project_id' => 42,
        ]);

        (new FounderContentSeeder)->run();

        $caseStudy = CaseStudy::where('slug', 'demo-project')->firstOrFail();
        $this->assertFalse($caseStudy->is_published);
        $this->assertSame('draft', $caseStudy->status);
    }

    public function test_unverified_testimonials_are_unapproved(): void
    {
        Testimonial::create([
            'author_name' => 'Test Author',
            'content' => ['en' => 'Great work.'],
            'is_approved' => true,
            'legacy_review_id' => 7,
        ]);

        (new FounderContentSeeder)->run();

        $testimonial = Testimonial::where('legacy_review_id', 7)->firstOrFail();
        $this->assertFalse($testimonial->is_approved);
    }

    public function test_faqs_are_seeded_unpublished(): void
    {
        (new FounderContentSeeder)->run();

        $faqs = FaqItem::all();

        $this->assertCount(10, $faqs);
        foreach ($faqs as $faq) {
            $this->assertFalse($faq->is_published);
        }
    }

    public function test_company_settings_have_no_fabricated_contact_channels(): void
    {
        (new FounderContentSeeder)->run();

        $settings = CompanySetting::current();

        $this->assertSame('hello@hexaterminal.com', $settings->email);
        $this->assertNull($settings->phone);
        $this->assertNull($settings->whatsapp);
        $this->assertNull($settings->booking_url);
    }

    public function test_seeder_is_idempotent(): void
    {
        (new FounderContentSeeder)->run();
        (new FounderContentSeeder)->run();

        $this->assertSame(6, Service::whereIn('slug', [
            'saas-platforms', 'crm-erp-systems', 'ai-enabled-workflows',
            'backend-api-engineering', 'business-automation', 'custom-operational-software',
        ])->count());
        $this->assertSame(10, FaqItem::count());
    }
}
