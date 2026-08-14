<?php

namespace Tests\Feature\Api\V1;

use App\Models\CaseStudy;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_detail_exposes_cms_content_and_only_published_related_work(): void
    {
        $service = Service::create([
            'slug' => 'workflow-systems',
            'name' => ['en' => 'Workflow Systems', 'ar' => 'Arabic workflow systems'],
            'tagline' => ['en' => 'CMS tagline'],
            'summary' => ['en' => 'CMS summary'],
            'description' => ['en' => "CMS description\nwith structure."],
            'features' => ['CMS capability one', 'CMS capability two'],
            'tech_stack' => ['CMS technology'],
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ]);

        CaseStudy::create([
            'slug' => 'visible-related-work',
            'title' => ['en' => 'Visible related work'],
            'service_offering_id' => $service->id,
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'sort_order' => 2,
        ]);
        CaseStudy::create([
            'slug' => 'draft-related-work',
            'title' => ['en' => 'Draft related work'],
            'service_offering_id' => $service->id,
            'is_published' => false,
            'sort_order' => 1,
        ]);
        CaseStudy::create([
            'slug' => 'other-service-work',
            'title' => ['en' => 'Other service work'],
            'is_published' => true,
        ]);

        $this->getJson('/api/v1/public/services/workflow-systems')
            ->assertOk()
            ->assertJsonPath('data.name', 'Workflow Systems')
            ->assertJsonPath('data.tagline', 'CMS tagline')
            ->assertJsonPath('data.summary', 'CMS summary')
            ->assertJsonPath('data.description', "CMS description\nwith structure.")
            ->assertJsonPath('data.features.0', 'CMS capability one')
            ->assertJsonPath('data.tech_stack.0', 'CMS technology')
            ->assertJsonPath('data.related_case_studies.0.slug', 'visible-related-work')
            ->assertJsonCount(1, 'data.related_case_studies');
    }

    public function test_service_detail_keeps_optional_collections_empty_and_hides_missing_or_unpublished_services(): void
    {
        Service::create([
            'slug' => 'minimal-service',
            'name' => ['en' => 'Minimal service'],
            'is_published' => true,
        ]);
        Service::create([
            'slug' => 'draft-service',
            'name' => ['en' => 'Draft service'],
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/services/minimal-service')
            ->assertOk()
            ->assertJsonPath('data.features', [])
            ->assertJsonPath('data.tech_stack', [])
            ->assertJsonPath('data.related_case_studies', []);

        $this->getJson('/api/v1/public/services/draft-service')->assertNotFound();
        $this->getJson('/api/v1/public/services/missing-service')->assertNotFound();
    }
}
