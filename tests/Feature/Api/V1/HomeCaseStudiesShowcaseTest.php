<?php

namespace Tests\Feature\Api\V1;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCaseStudiesShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_published_featured_case_studies_in_cms_sort_order_with_optional_relations(): void
    {
        $service = Service::create(['slug' => 'service', 'name' => ['en' => 'Service label'], 'is_published' => true]);
        $system = System::create(['slug' => 'system', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'System label'], 'is_published' => true]);
        $industry = Industry::create(['slug' => 'industry', 'name' => ['en' => 'Industry label'], 'is_published' => true]);

        $later = $this->makeCaseStudy('later-featured', 20, true);
        $later->update([
            'service_offering_id' => $service->id,
            'system_id' => $system->id,
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
        ]);
        $later->industries()->attach($industry);
        $this->makeCaseStudy('not-featured', 0, false);
        $this->makeCaseStudy('first-featured', 10, true);
        $this->makeCaseStudy('unpublished-featured', 1, true, false);

        $response = $this->getJson('/api/v1/public/home')->assertOk();

        $this->assertSame(
            ['first-featured', 'later-featured'],
            collect($response->json('data.featured_case_studies'))->pluck('slug')->all(),
        );
        $response
            ->assertJsonPath('data.featured_case_studies.1.project_classification', CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM)
            ->assertJsonPath('data.featured_case_studies.1.service.slug', 'service')
            ->assertJsonPath('data.featured_case_studies.1.system.slug', 'system')
            ->assertJsonPath('data.featured_case_studies.1.industries.0.slug', 'industry');
    }

    public function test_home_featured_case_studies_use_the_requested_locale_and_are_empty_when_none_qualify(): void
    {
        $caseStudy = $this->makeCaseStudy('featured-case-study', 0, true);

        $this->getJson('/api/v1/public/home?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.featured_case_studies.0.title', 'دراسة معاينة')
            ->assertJsonPath('data.featured_case_studies.0.summary', 'ملخص معاينة');

        $caseStudy->delete();

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonCount(0, 'data.featured_case_studies');
    }

    private function makeCaseStudy(string $slug, int $sortOrder, bool $featured, bool $published = true): CaseStudy
    {
        return CaseStudy::create([
            'slug' => $slug,
            'title' => ['en' => 'Preview case study', 'ar' => 'دراسة معاينة'],
            'summary' => ['en' => 'Preview summary', 'ar' => 'ملخص معاينة'],
            'is_featured' => $featured,
            'is_published' => $published,
            'sort_order' => $sortOrder,
        ]);
    }
}
