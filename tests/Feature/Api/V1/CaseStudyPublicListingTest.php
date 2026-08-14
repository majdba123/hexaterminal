<?php

namespace Tests\Feature\Api\V1;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStudyPublicListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_case_studies_use_cms_sort_order_and_expose_context_relations(): void
    {
        $service = Service::create(['slug' => 'workflow-services', 'name' => ['en' => 'CMS service'], 'is_published' => true]);
        $system = System::create(['slug' => 'workflow-system', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'CMS system'], 'is_published' => true]);
        $industry = Industry::create(['slug' => 'operations', 'name' => ['en' => 'CMS industry'], 'is_published' => true]);

        $later = $this->makeCaseStudy('later-case-study', 20, $service, $system);
        $later->industries()->attach($industry);
        $this->makeCaseStudy('first-case-study', 5, $service, $system);
        $this->makeCaseStudy('draft-case-study', 0, $service, $system, false);

        $this->getJson('/api/v1/public/case-studies')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'first-case-study')
            ->assertJsonPath('data.1.slug', 'later-case-study')
            ->assertJsonPath('data.1.project_classification', CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM)
            ->assertJsonPath('data.1.service.slug', 'workflow-services')
            ->assertJsonPath('data.1.system.slug', 'workflow-system')
            ->assertJsonPath('data.1.industries.0.slug', 'operations')
            ->assertJsonCount(2, 'data');
    }

    private function makeCaseStudy(
        string $slug,
        int $sortOrder,
        Service $service,
        System $system,
        bool $published = true,
    ): CaseStudy {
        return CaseStudy::create([
            'slug' => $slug,
            'title' => ['en' => 'CMS case study'],
            'summary' => ['en' => 'CMS summary'],
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
            'service_offering_id' => $service->id,
            'system_id' => $system->id,
            'is_published' => $published,
            'sort_order' => $sortOrder,
        ]);
    }
}
