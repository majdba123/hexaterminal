<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\ServicesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_three_approved_localized_services_with_public_cover_images_idempotently(): void
    {
        Storage::fake('public');

        $this->seed(ServicesSeeder::class);

        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertSame(Service::CORE_SERVICE_SLUGS, Service::query()->orderBy('sort_order')->pluck('slug')->all());

        $erp = Service::query()->where('slug', 'custom-erp-crm-systems')->firstOrFail();
        $platform = Service::query()->where('slug', 'web-platforms-mobile-applications')->firstOrFail();

        $this->assertTrue($erp->is_published);
        $this->assertSame('published', $erp->status);
        $this->assertSame([], $erp->tech_stack);
        $this->assertSame('Custom ERP & CRM Systems', $erp->getTranslation('name', 'en'));
        $this->assertSame('أنظمة ERP وCRM مخصصة', $erp->getTranslation('name', 'ar'));
        $this->assertSame('Customer & Lead Management', $erp->getTranslation('features', 'en')[0]);
        $this->assertSame('منصات SaaS', $platform->getTranslation('features', 'ar')[0]);
        $this->assertSame('service-offerings/custom-erp-crm-systems.png', $erp->cover_image);
        $this->assertSame('Custom ERP and CRM system icon showing a business dashboard connected to CRM, workflows, operations, and integrations.', $erp->getTranslation('cover_image_alt', 'en'));

        foreach (Service::query()->get() as $service) {
            Storage::disk('public')->assertExists($service->cover_image);
        }

        $this->get('/api/storage/'.$erp->cover_image)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->getJson('/api/v1/public/services/custom-erp-crm-systems?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.name', 'أنظمة ERP وCRM مخصصة')
            ->assertJsonPath('data.features.0', 'إدارة العملاء والعملاء المحتملين')
            ->assertJsonPath('data.cover_image', 'service-offerings/custom-erp-crm-systems.png');

        $this->seed(ServicesSeeder::class);

        $this->assertDatabaseCount('service_offerings', 3);
        Storage::disk('public')->assertExists('service-offerings/custom-erp-crm-systems.png');
        Storage::disk('public')->assertExists('service-offerings/web-platforms-mobile-applications.png');
        Storage::disk('public')->assertExists('service-offerings/ecommerce-business-websites.png');
    }
}
