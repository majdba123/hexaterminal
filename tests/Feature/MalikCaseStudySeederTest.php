<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Service;
use App\Models\System;
use Database\Seeders\MalikCaseStudySeeder;
use Database\Seeders\ServicesSeeder;
use Database\Seeders\SystemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MalikCaseStudySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_packaged_malik_case_study_with_public_media_and_correct_relations_idempotently(): void
    {
        Storage::fake('public');

        $this->seed([
            ServicesSeeder::class,
            SystemsSeeder::class,
            MalikCaseStudySeeder::class,
        ]);

        $this->assertDatabaseCount('case_studies', 1);

        $service = Service::query()->where('slug', 'ecommerce-business-websites')->firstOrFail();
        $system = System::query()->where('slug', 'malik-group')->firstOrFail();
        $caseStudy = CaseStudy::query()
            ->with(['serviceOffering', 'system', 'industries'])
            ->where('slug', 'malik-group-furniture-catalog')
            ->firstOrFail();

        $this->assertSame(['Laravel', 'Blade'], $system->tech_stack);
        $this->assertSame($service->id, $caseStudy->service_offering_id);
        $this->assertSame($system->id, $caseStudy->system_id);
        $this->assertSame(CaseStudy::CLASSIFICATION_ECOMMERCE_BUSINESS_WEBSITE, $caseStudy->project_classification);
        $this->assertSame('Malik Group', $caseStudy->client_name);
        $this->assertNull($caseStudy->project_url);
        $this->assertNull($caseStudy->video_url);
        $this->assertTrue($caseStudy->is_featured);
        $this->assertTrue($caseStudy->is_published);
        $this->assertNotNull($caseStudy->published_at);
        $this->assertCount(0, $caseStudy->industries);
        $this->assertSame('case-studies/malik-group/malik-group-case-study-cover.png', $caseStudy->cover_image);
        $this->assertSame([
            'case-studies/malik-group/gallery/01-shop-by-room.png',
            'case-studies/malik-group/gallery/02-full-catalog.png',
            'case-studies/malik-group/gallery/03-category-page.png',
            'case-studies/malik-group/gallery/04-product-detail.png',
        ], $caseStudy->gallery);

        Storage::disk('public')->assertExists($caseStudy->cover_image);
        foreach ($caseStudy->gallery as $image) {
            Storage::disk('public')->assertExists($image);
        }

        $this->getJson('/api/v1/public/case-studies/malik-group-furniture-catalog?locale=en')
            ->assertOk()
            ->assertJsonPath('data.slug', 'malik-group-furniture-catalog')
            ->assertJsonPath('data.title', 'Turning Social Media Product Enquiries into a Structured Furniture Catalog')
            ->assertJsonPath('data.service.slug', 'ecommerce-business-websites')
            ->assertJsonPath('data.system.slug', 'malik-group')
            ->assertJsonPath('data.cover_image', url('/storage/case-studies/malik-group/malik-group-case-study-cover.png'))
            ->assertJsonCount(4, 'data.gallery')
            ->assertJsonPath('data.gallery.0', url('/storage/case-studies/malik-group/gallery/01-shop-by-room.png'))
            ->assertJsonPath('data.gallery.3', url('/storage/case-studies/malik-group/gallery/04-product-detail.png'));

        $this->getJson('/api/v1/public/case-studies/malik-group-furniture-catalog?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.title', 'تحويل استفسارات السوشال ميديا إلى كتالوج أثاث رقمي منظم')
            ->assertJsonPath('data.summary', 'كان Malik Group يصل إلى العملاء عبر منصات التواصل الاجتماعي، لكن استفسارات المنتجات كانت تعتمد على الشرح اليدوي المتكرر وإرسال الصور لكل عميل. بنينا كتالوج أثاث باستخدام Laravel وBlade يمنح العميل طريقة منظمة لاكتشاف المنتجات، وتصفح التصنيفات، وتصفية الكتالوج، ومراجعة تفاصيل المنتج وصوره، ثم الانتقال مباشرة إلى WhatsApp لاستفسار أكثر تحديداً.');

        $this->getJson('/api/v1/public/services/ecommerce-business-websites')
            ->assertOk()
            ->assertJsonPath('data.related_case_studies.0.slug', 'malik-group-furniture-catalog')
            ->assertJsonPath('data.related_case_studies.0.cover_image', url('/storage/case-studies/malik-group/malik-group-case-study-cover.png'));

        $this->getJson('/api/v1/public/systems/malik-group')
            ->assertOk()
            ->assertJsonPath('data.case_studies.0.slug', 'malik-group-furniture-catalog')
            ->assertJsonPath('data.case_studies.0.cover_image', url('/storage/case-studies/malik-group/malik-group-case-study-cover.png'))
            ->assertJsonPath('data.tech_stack', ['Laravel', 'Blade']);

        $this->seed(MalikCaseStudySeeder::class);

        $this->assertDatabaseCount('case_studies', 1);
        $this->assertSame(['malik-group-furniture-catalog'], CaseStudy::query()->pluck('slug')->all());
        $this->assertSame(['Laravel', 'Blade'], $system->fresh()->tech_stack);
        $this->assertCount(5, Storage::disk('public')->allFiles('case-studies/malik-group'));
    }
}
