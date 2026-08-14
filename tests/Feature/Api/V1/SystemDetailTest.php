<?php

namespace Tests\Feature\Api\V1;

use App\Models\CaseStudy;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_detail_exposes_cms_content_and_published_related_work_in_cms_order(): void
    {
        $system = System::create([
            'slug' => 'workflow-platform',
            'type' => System::TYPE_PLATFORM,
            'category' => 'CMS category',
            'name' => ['en' => 'CMS system'],
            'tagline' => ['en' => 'CMS tagline'],
            'short_description' => ['en' => 'CMS short description'],
            'full_description' => ['en' => "CMS full description\nwith structure."],
            'features' => ['en' => "CMS capability one\nCMS capability two"],
            'target_audience' => ['en' => 'CMS audience'],
            'tech_stack' => ['CMS technology'],
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ]);

        CaseStudy::create([
            'slug' => 'later-related-work',
            'title' => ['en' => 'Later related work'],
            'system_id' => $system->id,
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'sort_order' => 20,
        ]);
        CaseStudy::create([
            'slug' => 'first-related-work',
            'title' => ['en' => 'First related work'],
            'system_id' => $system->id,
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'sort_order' => 5,
        ]);
        CaseStudy::create([
            'slug' => 'draft-related-work',
            'title' => ['en' => 'Draft related work'],
            'system_id' => $system->id,
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/systems/workflow-platform')
            ->assertOk()
            ->assertJsonPath('data.name', 'CMS system')
            ->assertJsonPath('data.tagline', 'CMS tagline')
            ->assertJsonPath('data.short_description', 'CMS short description')
            ->assertJsonPath('data.full_description', "CMS full description\nwith structure.")
            ->assertJsonPath('data.features', "CMS capability one\nCMS capability two")
            ->assertJsonPath('data.target_audience', 'CMS audience')
            ->assertJsonPath('data.tech_stack.0', 'CMS technology')
            ->assertJsonPath('data.case_studies.0.slug', 'first-related-work')
            ->assertJsonPath('data.case_studies.1.slug', 'later-related-work')
            ->assertJsonCount(2, 'data.case_studies');
    }

    public function test_system_detail_keeps_optional_data_empty_and_hides_missing_or_unpublished_records(): void
    {
        System::create([
            'slug' => 'minimal-system',
            'type' => System::TYPE_PLATFORM,
            'name' => ['en' => 'Minimal system'],
            'is_published' => true,
        ]);
        System::create([
            'slug' => 'draft-system',
            'type' => System::TYPE_PLATFORM,
            'name' => ['en' => 'Draft system'],
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/systems/minimal-system')
            ->assertOk()
            ->assertJsonPath('data.tech_stack', [])
            ->assertJsonPath('data.case_studies', []);

        $this->getJson('/api/v1/public/systems/draft-system')->assertNotFound();
        $this->getJson('/api/v1/public/systems/missing-system')->assertNotFound();
    }
}
