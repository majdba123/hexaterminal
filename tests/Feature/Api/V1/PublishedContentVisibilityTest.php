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
