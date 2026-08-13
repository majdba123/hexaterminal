<?php

namespace Tests\Feature\Api\V1;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCoreServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_only_published_official_service_tracks_in_business_order(): void
    {
        $this->makeService('ecommerce-business-websites', 1);
        $this->makeService('other-published-service', 0);
        $this->makeService('web-platforms-mobile-applications', 99);
        $this->makeService('custom-erp-crm-systems', 50);
        Service::create([
            'slug' => 'unpublished-core-service',
            'name' => ['en' => 'Unpublished core service'],
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/v1/public/home')->assertOk();

        $this->assertSame(Service::CORE_SERVICE_SLUGS, collect($response->json('data.services'))->pluck('slug')->all());
        $response
            ->assertJsonPath('data.services.0.name', 'Custom ERP & CRM Systems')
            ->assertJsonPath('data.services.0.summary', 'English summary')
            ->assertJsonPath('data.services.0.seo.title', 'ERP SEO title');
    }

    public function test_home_core_services_use_the_requested_locale(): void
    {
        $this->makeService('custom-erp-crm-systems', 0);

        $this->getJson('/api/v1/public/home?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.services.0.slug', 'custom-erp-crm-systems')
            ->assertJsonPath('data.services.0.name', 'أنظمة ERP وCRM مخصصة')
            ->assertJsonPath('data.services.0.summary', 'ملخص عربي');
    }

    public function test_home_is_stable_when_no_official_service_records_exist(): void
    {
        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonCount(0, 'data.services');
    }

    private function makeService(string $slug, int $sortOrder): Service
    {
        $service = Service::create([
            'slug' => $slug,
            'name' => [
                'en' => 'Custom ERP & CRM Systems',
                'ar' => 'أنظمة ERP وCRM مخصصة',
            ],
            'summary' => [
                'en' => 'English summary',
                'ar' => 'ملخص عربي',
            ],
            'description' => [
                'en' => 'English description',
                'ar' => 'وصف عربي',
            ],
            'is_published' => true,
            'sort_order' => $sortOrder,
        ]);
        $service->seo()->create([
            'title' => ['en' => 'ERP SEO title', 'ar' => 'عنوان تحسين البحث'],
            'description' => ['en' => 'SEO description', 'ar' => 'وصف تحسين البحث'],
        ]);

        return $service;
    }
}
