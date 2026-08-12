<?php

namespace Tests\Feature\Api\V1;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedContentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_index_and_show_exclude_unpublished_and_future_dated(): void
    {
        Service::create(['slug' => 'visible', 'name' => ['en' => 'Visible'], 'is_published' => true, 'published_at' => now()->subDay()]);
        Service::create(['slug' => 'draft', 'name' => ['en' => 'Draft'], 'is_published' => false]);
        Service::create(['slug' => 'scheduled', 'name' => ['en' => 'Scheduled'], 'is_published' => true, 'published_at' => now()->addDay()]);

        $index = $this->getJson('/api/v1/public/services')->assertOk();
        $slugs = collect($index->json('data'))->pluck('slug')->all();
        $this->assertSame(['visible'], $slugs);

        $this->getJson('/api/v1/public/services/visible')->assertOk();
        $this->getJson('/api/v1/public/services/draft')->assertNotFound();
        $this->getJson('/api/v1/public/services/scheduled')->assertNotFound();
    }

    public function test_systems_index_and_show_exclude_unpublished(): void
    {
        System::create(['slug' => 'visible', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'Visible'], 'is_published' => true]);
        System::create(['slug' => 'hidden', 'type' => System::TYPE_PLATFORM, 'name' => ['en' => 'Hidden'], 'is_published' => false]);

        $index = $this->getJson('/api/v1/public/systems')->assertOk();
        $this->assertSame(['visible'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/systems/hidden')->assertNotFound();
    }

    public function test_case_studies_index_and_show_exclude_unpublished(): void
    {
        CaseStudy::create(['slug' => 'visible', 'title' => ['en' => 'Visible'], 'is_published' => true]);
        CaseStudy::create(['slug' => 'hidden', 'title' => ['en' => 'Hidden'], 'is_published' => false]);

        $index = $this->getJson('/api/v1/public/case-studies')->assertOk();
        $this->assertSame(['visible'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/case-studies/hidden')->assertNotFound();
    }

    public function test_case_study_placeholder_project_url_is_not_publicly_clickable(): void
    {
        $caseStudy = CaseStudy::create([
            'slug' => 'placeholder-link',
            'title' => ['en' => 'Preserved case study'],
            'summary' => ['en' => 'Content remains publicly visible.'],
            'project_url' => 'https://portfolio.example.com/demo',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/case-studies/placeholder-link')
            ->assertOk()
            ->assertJsonPath('data.slug', 'placeholder-link')
            ->assertJsonPath('data.summary', 'Content remains publicly visible.')
            ->assertJsonPath('data.project_url', null);

        $this->assertSame('https://portfolio.example.com/demo', $caseStudy->fresh()->project_url);
    }

    public function test_case_study_real_project_url_remains_public(): void
    {
        CaseStudy::create([
            'slug' => 'real-link',
            'title' => ['en' => 'Real project'],
            'project_url' => 'https://hexaterminal.com/work',
            'is_published' => true,
        ]);

        $this->getJson('/api/v1/public/case-studies/real-link')
            ->assertOk()
            ->assertJsonPath('data.project_url', 'https://hexaterminal.com/work');
    }

    public function test_case_study_api_exposes_classification_and_preserves_relations(): void
    {
        $service = Service::create([
            'slug' => 'crm-systems',
            'name' => ['en' => 'CRM Systems'],
            'is_published' => true,
        ]);
        $system = System::create([
            'slug' => 'client-platform',
            'type' => System::TYPE_CLIENT_SYSTEM,
            'name' => ['en' => 'Client Platform'],
            'is_published' => true,
        ]);
        $industry = Industry::create([
            'slug' => 'logistics',
            'name' => ['en' => 'Logistics'],
            'is_published' => true,
        ]);
        $caseStudy = CaseStudy::create([
            'slug' => 'classified-case-study',
            'title' => ['en' => 'Classified case study'],
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
            'service_offering_id' => $service->id,
            'system_id' => $system->id,
            'is_featured' => true,
            'is_published' => true,
        ]);
        $caseStudy->industries()->attach($industry);

        $this->getJson('/api/v1/public/case-studies/classified-case-study')
            ->assertOk()
            ->assertJsonPath('data.project_classification', CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM)
            ->assertJsonPath('data.service.slug', 'crm-systems')
            ->assertJsonPath('data.system.slug', 'client-platform')
            ->assertJsonPath('data.industries.0.slug', 'logistics')
            ->assertJsonPath('data.is_featured', true);
    }

    public function test_case_study_api_exposes_null_classification(): void
    {
        CaseStudy::create([
            'slug' => 'unclassified-case-study',
            'title' => ['en' => 'Unclassified case study'],
            'is_published' => true,
        ]);

        $this->getJson('/api/v1/public/case-studies/unclassified-case-study')
            ->assertOk()
            ->assertJsonPath('data.project_classification', null);
    }

    public function test_industries_index_and_show_exclude_unpublished(): void
    {
        Industry::create(['slug' => 'visible', 'name' => ['en' => 'Visible'], 'is_published' => true]);
        Industry::create(['slug' => 'hidden', 'name' => ['en' => 'Hidden'], 'is_published' => false]);

        $index = $this->getJson('/api/v1/public/industries')->assertOk();
        $this->assertSame(['visible'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/industries/hidden')->assertNotFound();
    }

    public function test_articles_index_and_show_exclude_unpublished(): void
    {
        Article::create(['slug' => 'visible', 'title' => ['en' => 'Visible'], 'is_published' => true, 'published_at' => now()->subHour()]);
        Article::create(['slug' => 'hidden', 'title' => ['en' => 'Hidden'], 'is_published' => false]);

        $index = $this->getJson('/api/v1/public/articles')->assertOk();
        $this->assertSame(['visible'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/articles/hidden')->assertNotFound();
    }

    public function test_team_index_and_show_exclude_unpublished(): void
    {
        TeamMember::create(['slug' => 'visible', 'first_name' => 'Vis', 'is_published' => true, 'publication_consent' => true]);
        TeamMember::create(['slug' => 'hidden', 'first_name' => 'Hid', 'is_published' => false, 'publication_consent' => true]);

        $index = $this->getJson('/api/v1/public/team')->assertOk();
        $this->assertSame(['visible'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/team/hidden')->assertNotFound();
    }

    public function test_team_member_without_publication_consent_stays_hidden_even_if_published(): void
    {
        TeamMember::create(['slug' => 'no-consent', 'first_name' => 'NC', 'is_published' => true, 'publication_consent' => false]);

        $this->getJson('/api/v1/public/team')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/public/team/no-consent')->assertNotFound();
    }

    public function test_testimonials_exclude_unapproved(): void
    {
        Testimonial::create(['author_name' => 'Approved', 'content' => ['en' => 'Great'], 'rating' => 5, 'is_approved' => true]);
        Testimonial::create(['author_name' => 'Pending', 'content' => ['en' => 'Meh'], 'rating' => 3, 'is_approved' => false]);

        $index = $this->getJson('/api/v1/public/testimonials')->assertOk();
        $this->assertSame(['Approved'], collect($index->json('data'))->pluck('author_name')->all());
    }
}
