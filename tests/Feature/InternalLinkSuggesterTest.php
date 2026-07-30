<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Services\InternalLinkSuggester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalLinkSuggesterTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggests_related_records_via_real_relationships(): void
    {
        $industry = Industry::create(['slug' => 'fintech', 'name' => ['en' => 'Fintech'], 'is_published' => true]);
        $system = System::create(['slug' => 'ledger', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'Ledger'], 'is_published' => true]);
        $system->industries()->attach($industry);

        $suggestions = app(InternalLinkSuggester::class)->suggestFor($system);

        $paths = collect($suggestions)->pluck('path')->all();
        $this->assertContains('/en/industries/fintech', $paths);
    }

    public function test_never_suggests_a_self_link(): void
    {
        $system = System::create(['slug' => 'ledger', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'Ledger Platform'], 'is_published' => true]);

        $suggestions = app(InternalLinkSuggester::class)->suggestFor($system);

        $this->assertNotContains('/en/systems/ledger', collect($suggestions)->pluck('path')->all());
    }

    public function test_never_suggests_unpublished_content(): void
    {
        $system = System::create(['slug' => 'ledger', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'Ledger Platform Keywords'], 'is_published' => true]);
        Service::create(['slug' => 'draft-service', 'name' => ['en' => 'Ledger Platform Keywords Service'], 'is_published' => false]);

        $suggestions = app(InternalLinkSuggester::class)->suggestFor($system);

        $this->assertNotContains('/en/services/draft-service', collect($suggestions)->pluck('path')->all());
    }

    public function test_broken_related_links_detects_unpublished_relations(): void
    {
        $system = System::create(['slug' => 'ledger', 'type' => System::TYPE_SAAS_PRODUCT, 'name' => ['en' => 'Ledger'], 'is_published' => false]);
        $service = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);
        CaseStudy::create([
            'slug' => 'case-1', 'title' => ['en' => 'Case 1'], 'is_published' => true,
            'system_id' => $system->id, 'service_offering_id' => $service->id,
        ]);

        $broken = app(InternalLinkSuggester::class)->brokenRelatedLinks();

        $this->assertNotEmpty(array_filter($broken, fn ($b) => str_contains($b['target'], 'system:ledger')));
    }
}
