<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Services\ContentCompletenessReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentCompletenessReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_flags_missing_arabic_translation(): void
    {
        Service::create(['slug' => 'en-only', 'name' => ['en' => 'English Only'], 'summary' => ['en' => 'Summary'], 'is_published' => true]);

        $report = app(ContentCompletenessReport::class)->build();

        $this->assertGreaterThan(0, $report['totals']['missing_arabic']);
        $this->assertNotEmpty(array_filter(
            $report['findings'],
            fn ($f) => $f['slug'] === 'en-only' && str_contains($f['problem'], 'missing AR'),
        ));
    }

    public function test_fully_translated_and_seo_complete_record_has_no_findings_for_those_checks(): void
    {
        $service = Service::create([
            'slug' => 'complete', 'name' => ['en' => 'Complete', 'ar' => 'مكتمل'],
            'summary' => ['en' => 'Summary', 'ar' => 'ملخص'], 'cover_image' => '/img.jpg', 'is_published' => true,
        ]);
        $service->seo()->create([
            'title' => ['en' => 'Title', 'ar' => 'عنوان'],
            'description' => ['en' => 'Description', 'ar' => 'وصف'],
        ]);

        $report = app(ContentCompletenessReport::class)->build();
        $findingsForRecord = array_filter($report['findings'], fn ($f) => $f['slug'] === 'complete');

        $this->assertEmpty($findingsForRecord);
    }

    public function test_unpublished_content_is_flagged(): void
    {
        Service::create(['slug' => 'draft', 'name' => ['en' => 'Draft'], 'is_published' => false]);

        $report = app(ContentCompletenessReport::class)->build();

        $this->assertNotEmpty(array_filter(
            $report['findings'],
            fn ($f) => $f['slug'] === 'draft' && $f['problem'] === 'not published',
        ));
    }
}
