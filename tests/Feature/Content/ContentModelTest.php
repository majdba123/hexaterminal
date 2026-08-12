<?php

namespace Tests\Feature\Content;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\System;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_study_project_classification_schema_is_nullable(): void
    {
        $this->assertTrue(Schema::hasColumn('case_studies', 'project_classification'));

        $caseStudy = CaseStudy::create([
            'slug' => 'unclassified-project',
            'title' => ['en' => 'Unclassified project'],
            'is_published' => false,
        ]);

        $this->assertNull($caseStudy->fresh()->project_classification);
    }

    public function test_case_study_project_classification_can_be_created_and_updated(): void
    {
        $caseStudy = CaseStudy::create([
            'slug' => 'classified-project',
            'title' => ['en' => 'Classified project'],
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
            'is_published' => false,
        ]);

        $this->assertSame(
            CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
            $caseStudy->fresh()->project_classification,
        );

        $caseStudy->update([
            'project_classification' => CaseStudy::CLASSIFICATION_WEB_MOBILE_PLATFORM,
        ]);

        $this->assertSame(
            CaseStudy::CLASSIFICATION_WEB_MOBILE_PLATFORM,
            $caseStudy->fresh()->project_classification,
        );
    }

    public function test_translatable_fields_round_trip_en_and_ar(): void
    {
        $service = Service::create([
            'slug' => 'crm-systems',
            'name' => ['en' => 'CRM Systems', 'ar' => 'أنظمة إدارة علاقات العملاء'],
            'is_published' => true,
        ]);

        app()->setLocale('en');
        $this->assertSame('CRM Systems', $service->fresh()->name);

        app()->setLocale('ar');
        $this->assertSame('أنظمة إدارة علاقات العملاء', $service->fresh()->name);

        app()->setLocale('en');
    }

    public function test_untranslated_locale_falls_back_to_english(): void
    {
        $service = Service::create([
            'slug' => 'ai-workflows',
            'name' => ['en' => 'AI Workflows'],
            'is_published' => true,
        ]);

        app()->setLocale('ar');
        // spatie/laravel-translatable falls back to the app fallback_locale (en).
        $this->assertSame('AI Workflows', $service->fresh()->name);
        app()->setLocale('en');
    }

    public function test_published_scope_excludes_unpublished_and_future_dated(): void
    {
        Service::create(['slug' => 'visible', 'name' => ['en' => 'Visible'], 'is_published' => true, 'published_at' => now()->subDay()]);
        Service::create(['slug' => 'draft', 'name' => ['en' => 'Draft'], 'is_published' => false]);
        Service::create(['slug' => 'scheduled', 'name' => ['en' => 'Scheduled'], 'is_published' => true, 'published_at' => now()->addDay()]);

        $slugs = Service::published()->pluck('slug')->all();

        $this->assertSame(['visible'], $slugs);
    }

    public function test_testimonial_approved_scope_excludes_unapproved(): void
    {
        Testimonial::create(['author_name' => 'A', 'content' => ['en' => 'Great'], 'rating' => 5, 'is_approved' => true]);
        Testimonial::create(['author_name' => 'B', 'content' => ['en' => 'Pending'], 'rating' => 4, 'is_approved' => false]);

        $this->assertSame(['A'], Testimonial::approved()->pluck('author_name')->all());
    }

    public function test_auto_slug_generation_and_deduplication(): void
    {
        $first = Service::create(['name' => ['en' => 'Backend Engineering'], 'is_published' => true]);
        $second = Service::create(['name' => ['en' => 'Backend Engineering'], 'is_published' => true]);

        $this->assertSame('backend-engineering', $first->slug);
        $this->assertSame('backend-engineering-2', $second->slug);
    }

    public function test_explicit_slug_is_not_overwritten(): void
    {
        $service = Service::create(['slug' => 'custom-slug', 'name' => ['en' => 'Something Else'], 'is_published' => true]);

        $this->assertSame('custom-slug', $service->slug);
    }

    public function test_system_type_scope_filters_correctly(): void
    {
        System::create(['slug' => 'sys-1', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'SaaS One'], 'is_published' => true]);
        System::create(['slug' => 'sys-2', 'type' => System::TYPE_AI_SYSTEM, 'name' => ['en' => 'AI One'], 'is_published' => true]);

        $this->assertSame(['sys-1'], System::ofType(System::TYPE_SAAS_PRODUCT)->pluck('slug')->all());
    }

    public function test_seo_meta_attaches_polymorphically_to_a_case_study(): void
    {
        $caseStudy = CaseStudy::create(['title' => ['en' => 'Acme Rollout'], 'is_published' => true]);

        $seo = $caseStudy->seo()->create([
            'seoable_type' => CaseStudy::class,
            'seoable_id' => $caseStudy->id,
            'title' => ['en' => 'Custom SEO Title'],
            'noindex' => false,
        ]);

        $this->assertInstanceOf(SeoMeta::class, $caseStudy->fresh()->seo);
        $this->assertSame('Custom SEO Title', $caseStudy->fresh()->seo->title);
        $this->assertSame($caseStudy->id, $seo->seoable->id);
    }

    public function test_industry_system_many_to_many_relation(): void
    {
        $industry = Industry::create(['slug' => 'fintech', 'name' => ['en' => 'Fintech'], 'is_published' => true]);
        $system = System::create(['slug' => 'sys-fin', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'FinPlatform'], 'is_published' => true]);

        $system->industries()->attach($industry);

        $this->assertTrue($system->fresh()->industries->contains('slug', 'fintech'));
        $this->assertTrue($industry->fresh()->systems->contains('slug', 'sys-fin'));
    }
}
