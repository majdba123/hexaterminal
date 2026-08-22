<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Service;
use App\Models\System;
use App\Models\SystemUseCase;
use Database\Seeders\ServicesSeeder;
use Database\Seeders\SystemsSeeder;
use Database\Seeders\VetoraCaseStudySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VetoraCaseStudySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_moves_vetora_to_one_case_study_and_clears_system_use_cases_idempotently(): void
    {
        Storage::fake('public');

        $this->seed([
            ServicesSeeder::class,
            SystemsSeeder::class,
        ]);

        $system = System::query()->where('slug', 'vetora')->firstOrFail();

        SystemUseCase::create([
            'system_id' => $system->id,
            'slug' => 'legacy-vetora-use-case',
            'title' => ['en' => 'Legacy Vetora Use Case', 'ar' => 'حالة استخدام قديمة لفيتورا'],
            'summary' => ['en' => 'Legacy summary', 'ar' => 'ملخص قديم'],
            'workflow' => ['en' => 'Legacy workflow', 'ar' => 'تدفق قديم'],
            'outcome' => ['en' => 'Legacy outcome', 'ar' => 'نتيجة قديمة'],
            'is_published' => true,
            'published_at' => now(),
            'sort_order' => 1,
            'status' => 'published',
        ]);

        $this->seed(VetoraCaseStudySeeder::class);

        $service = Service::query()->where('slug', 'web-platforms-mobile-applications')->firstOrFail();
        $caseStudy = CaseStudy::query()
            ->with(['serviceOffering', 'system', 'industries'])
            ->where('slug', 'vetora-specialized-marketplace-operations')
            ->firstOrFail();

        $this->assertDatabaseCount('case_studies', 1);
        $this->assertSame($service->id, $caseStudy->service_offering_id);
        $this->assertSame($system->id, $caseStudy->system_id);
        $this->assertSame(CaseStudy::CLASSIFICATION_WEB_MOBILE_PLATFORM, $caseStudy->project_classification);
        $this->assertNull($caseStudy->client_name);
        $this->assertNull($caseStudy->project_url);
        $this->assertNull($caseStudy->video_url);
        $this->assertTrue($caseStudy->is_featured);
        $this->assertTrue($caseStudy->is_published);
        $this->assertNotNull($caseStudy->published_at);
        $this->assertCount(0, $caseStudy->industries);
        $this->assertSame('systems/vetora-cover-public.png', $caseStudy->cover_image);
        $this->assertSame([], $caseStudy->gallery);
        $this->assertSame(0, SystemUseCase::query()->where('system_id', $system->id)->count());

        Storage::disk('public')->assertExists($caseStudy->cover_image);

        $this->getJson('/api/v1/public/case-studies/vetora-specialized-marketplace-operations?locale=en')
            ->assertOk()
            ->assertJsonPath('data.slug', 'vetora-specialized-marketplace-operations')
            ->assertJsonPath('data.title', 'Connecting Professional Buyers, Suppliers, Syndicates, and Platform Operations in One Specialized Marketplace')
            ->assertJsonPath('data.service.slug', 'web-platforms-mobile-applications')
            ->assertJsonPath('data.system.slug', 'vetora')
            ->assertJsonPath('data.cover_image', url('/storage/systems/vetora-cover-public.png'))
            ->assertJsonPath('data.gallery', []);

        $this->getJson('/api/v1/public/case-studies/vetora-specialized-marketplace-operations?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.title', 'ربط المشترين المهنيين والموردين والنقابات وعمليات المنصة ضمن سوق متخصص واحد')
            ->assertJsonPath('data.summary', 'فيتورا هي منصة سوق متخصصة تساعد الأطباء البيطريين والمهندسين الزراعيين على الوصول إلى المستلزمات المناسبة، مع توفير مساحات تشغيل مخصصة للبائعين والموظفين والنقابات والإدارة داخل المنتج نفسه.');

        $this->getJson('/api/v1/public/systems/vetora?locale=en')
            ->assertOk()
            ->assertJsonPath('data.use_cases', [])
            ->assertJsonPath('data.case_studies.0.slug', 'vetora-specialized-marketplace-operations')
            ->assertJsonPath('data.case_studies.0.cover_image', url('/storage/systems/vetora-cover-public.png'));

        $this->seed(VetoraCaseStudySeeder::class);

        $this->assertDatabaseCount('case_studies', 1);
        $this->assertSame(['vetora-specialized-marketplace-operations'], CaseStudy::query()->pluck('slug')->all());
        $this->assertSame(0, SystemUseCase::query()->where('system_id', $system->id)->count());
    }
}
