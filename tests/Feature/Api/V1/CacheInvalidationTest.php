<?php

namespace Tests\Feature\Api\V1;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_a_service_invalidates_its_cached_show_response(): void
    {
        $service = Service::create(['slug' => 'crm-systems', 'name' => ['en' => 'CRM Systems'], 'is_published' => true]);

        // Warm the cache.
        $first = $this->getJson('/api/v1/public/services/crm-systems')->assertOk();
        $this->assertSame('CRM Systems', $first->json('data.name'));

        // Edit via the Eloquent model -- this must fire the observer,
        // not go through the controller.
        $service->setTranslation('name', 'en', 'Custom CRM Systems');
        $service->save();

        $second = $this->getJson('/api/v1/public/services/crm-systems')->assertOk();
        $this->assertSame('Custom CRM Systems', $second->json('data.name'));
    }

    public function test_deleting_a_service_invalidates_its_cached_show_response(): void
    {
        $service = Service::create(['slug' => 'to-delete', 'name' => ['en' => 'To Delete'], 'is_published' => true]);

        $this->getJson('/api/v1/public/services/to-delete')->assertOk();

        $service->delete();

        $this->getJson('/api/v1/public/services/to-delete')->assertNotFound();
    }

    public function test_editing_a_service_invalidates_the_home_aggregate_cache(): void
    {
        $service = Service::create(['slug' => 'svc', 'name' => ['en' => 'Original'], 'is_published' => true]);

        $first = $this->getJson('/api/v1/public/home')->assertOk();
        $names = collect($first->json('data.services'))->pluck('name')->all();
        $this->assertContains('Original', $names);

        $service->setTranslation('name', 'en', 'Renamed');
        $service->save();

        $second = $this->getJson('/api/v1/public/home')->assertOk();
        $names = collect($second->json('data.services'))->pluck('name')->all();
        $this->assertContains('Renamed', $names);
        $this->assertNotContains('Original', $names);
    }
}
