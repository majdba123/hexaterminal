<?php

namespace Tests\Feature\Api\V1;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePublicListingOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_services_list_keeps_core_tracks_in_business_order_then_uses_cms_sort_order(): void
    {
        $this->makeService('ecommerce-business-websites', 30);
        $this->makeService('future-offering', 5);
        $this->makeService('web-platforms-mobile-applications', 99);
        $this->makeService('custom-erp-crm-systems', 50);
        $this->makeService('another-offering', 10);
        $this->makeService('unpublished-core', 0, false);

        $response = $this->getJson('/api/v1/public/services')->assertOk();

        $this->assertSame(
            [
                'custom-erp-crm-systems',
                'web-platforms-mobile-applications',
                'ecommerce-business-websites',
                'future-offering',
                'another-offering',
            ],
            collect($response->json('data'))->pluck('slug')->all(),
        );
    }

    private function makeService(string $slug, int $sortOrder, bool $published = true): Service
    {
        return Service::create([
            'slug' => $slug,
            'name' => ['en' => 'CMS service', 'ar' => 'خدمة من نظام إدارة المحتوى'],
            'tagline' => ['en' => 'CMS tagline', 'ar' => 'وصف مختصر من نظام إدارة المحتوى'],
            'summary' => ['en' => 'CMS summary', 'ar' => 'ملخص من نظام إدارة المحتوى'],
            'is_published' => $published,
            'sort_order' => $sortOrder,
        ]);
    }
}
