<?php

namespace Tests\Feature\Api\V1;

use App\Models\Article;
use App\Models\Service;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_published_matches_across_types(): void
    {
        Service::create(['slug' => 'crm-systems', 'name' => ['en' => 'CRM Systems'], 'summary' => ['en' => 'Manage customers'], 'is_published' => true]);
        System::create(['slug' => 'crm-platform', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'CRM Platform'], 'is_published' => true]);
        Article::create(['slug' => 'crm-guide', 'title' => ['en' => 'A CRM Guide'], 'is_published' => true]);

        $response = $this->getJson('/api/v1/public/search?q=crm')->assertOk();

        $this->assertNotEmpty($response->json('data.results.services'));
        $this->assertNotEmpty($response->json('data.results.systems'));
        $this->assertNotEmpty($response->json('data.results.articles'));
        // Paths must be locale-less (the frontend Link component adds the locale itself).
        $this->assertSame('/services/crm-systems', $response->json('data.results.services.0.path'));
    }

    public function test_search_excludes_unpublished_content(): void
    {
        Service::create(['slug' => 'hidden-crm', 'name' => ['en' => 'Hidden CRM'], 'is_published' => false]);

        $response = $this->getJson('/api/v1/public/search?q=crm')->assertOk();

        $this->assertEmpty($response->json('data.results.services', []));
    }

    public function test_search_requires_a_minimum_query_length(): void
    {
        Service::create(['slug' => 'a-service', 'name' => ['en' => 'A'], 'is_published' => true]);

        $response = $this->getJson('/api/v1/public/search?q=a')->assertOk();

        $this->assertEmpty($response->json('data.results'));
    }

    public function test_search_rejects_overly_long_queries(): void
    {
        $response = $this->getJson('/api/v1/public/search?q='.str_repeat('a', 101))->assertOk();

        $this->assertEmpty($response->json('data.results'));
    }
}
