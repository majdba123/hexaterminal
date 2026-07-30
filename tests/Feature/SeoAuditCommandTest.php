<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behaviour of `php artisan hexa:seo-audit`: exits non-zero only when real
 * blockers exist (see App\Services\SeoAuditReport), never for cosmetic
 * warnings.
 */
class SeoAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_no_blockers_on_empty_database(): void
    {
        $this->artisan('hexa:seo-audit')->assertExitCode(0);
    }

    public function test_exits_non_zero_when_a_blocker_exists(): void
    {
        Service::create(['slug' => 'bare', 'name' => ['en' => 'Bare'], 'is_published' => true]);

        $this->artisan('hexa:seo-audit')->assertExitCode(1);
    }

    public function test_json_output_exits_non_zero_on_blockers(): void
    {
        Service::create(['slug' => 'bare', 'name' => ['en' => 'Bare'], 'is_published' => true]);

        $this->artisan('hexa:seo-audit --json')->assertExitCode(1);
    }

    public function test_export_writes_csv(): void
    {
        Service::create(['slug' => 'bare', 'name' => ['en' => 'Bare'], 'is_published' => true]);

        $path = storage_path('app/seo-audit-test.csv');
        $this->artisan('hexa:seo-audit', ['--export' => $path])->assertExitCode(1);

        $this->assertFileExists($path);
        $this->assertStringContainsString('missing_title', file_get_contents($path));
        unlink($path);
    }
}
