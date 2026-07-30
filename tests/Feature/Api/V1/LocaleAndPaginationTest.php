<?php

namespace Tests\Feature\Api\V1;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_query_param_returns_arabic_translation(): void
    {
        Service::create([
            'slug' => 'crm-systems',
            'name' => ['en' => 'CRM Systems', 'ar' => 'أنظمة إدارة علاقات العملاء'],
            'is_published' => true,
        ]);

        $en = $this->getJson('/api/v1/public/services/crm-systems')->assertOk();
        $this->assertSame('CRM Systems', $en->json('data.name'));

        $ar = $this->getJson('/api/v1/public/services/crm-systems?locale=ar')->assertOk();
        $this->assertSame('أنظمة إدارة علاقات العملاء', $ar->json('data.name'));
    }

    public function test_invalid_locale_param_is_ignored_not_errored(): void
    {
        Service::create(['slug' => 'x', 'name' => ['en' => 'X'], 'is_published' => true]);

        $this->getJson('/api/v1/public/services/x?locale=fr')->assertOk();
    }

    public function test_services_index_pagination_meta(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Service::create(['slug' => "svc-{$i}", 'name' => ['en' => "Service {$i}"], 'is_published' => true, 'sort_order' => $i]);
        }

        $response = $this->getJson('/api/v1/public/services?per_page=2&page=2')->assertOk();

        $this->assertSame(2, $response->json('meta.current_page'));
        $this->assertSame(3, $response->json('meta.last_page'));
        $this->assertSame(5, $response->json('meta.total'));
        $this->assertCount(2, $response->json('data'));
    }
}
