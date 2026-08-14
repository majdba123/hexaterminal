<?php

namespace Tests\Feature\Api\V1;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStudyDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_study_detail_exposes_cms_narrative_and_context_relationships(): void
    {
        $service = Service::create(['slug' => 'workflow-services', 'name' => ['en' => 'CMS service'], 'is_published' => true]);
        $system = System::create(['slug' => 'workflow-system', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'CMS system'], 'is_published' => true]);
        $industry = Industry::create(['slug' => 'operations', 'name' => ['en' => 'CMS industry'], 'is_published' => true]);
        $caseStudy = CaseStudy::create([
            'slug' => 'workflow-case-study',
            'title' => ['en' => 'CMS case study'],
            'summary' => ['en' => 'CMS summary'],
            'context' => ['en' => 'CMS context'],
            'problem' => ['en' => 'CMS problem'],
            'constraints' => ['en' => 'CMS constraints'],
            'solution' => ['en' => 'CMS solution'],
            'architecture' => ['en' => 'CMS architecture'],
            'outcomes' => ['en' => 'CMS qualitative outcome'],
            'features' => ['en' => "CMS capability one\nCMS capability two"],
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
            'service_offering_id' => $service->id,
            'system_id' => $system->id,
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ]);
        $caseStudy->industries()->attach($industry);

        $this->getJson('/api/v1/public/case-studies/workflow-case-study')
            ->assertOk()
            ->assertJsonPath('data.title', 'CMS case study')
            ->assertJsonPath('data.summary', 'CMS summary')
            ->assertJsonPath('data.context', 'CMS context')
            ->assertJsonPath('data.problem', 'CMS problem')
            ->assertJsonPath('data.solution', 'CMS solution')
            ->assertJsonPath('data.outcomes', 'CMS qualitative outcome')
            ->assertJsonPath('data.features', "CMS capability one\nCMS capability two")
            ->assertJsonPath('data.project_classification', CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM)
            ->assertJsonPath('data.service.slug', 'workflow-services')
            ->assertJsonPath('data.system.slug', 'workflow-system')
            ->assertJsonPath('data.industries.0.slug', 'operations');
    }

    public function test_case_study_detail_hides_missing_or_unpublished_records_and_allows_empty_optional_fields(): void
    {
        CaseStudy::create([
            'slug' => 'minimal-case-study',
            'title' => ['en' => 'Minimal case study'],
            'is_published' => true,
        ]);
        CaseStudy::create([
            'slug' => 'draft-case-study',
            'title' => ['en' => 'Draft case study'],
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/case-studies/minimal-case-study')
            ->assertOk()
            ->assertJsonPath('data.features', null)
            ->assertJsonPath('data.industries', []);

        $this->getJson('/api/v1/public/case-studies/draft-case-study')->assertNotFound();
        $this->getJson('/api/v1/public/case-studies/missing-case-study')->assertNotFound();
    }
}
