<?php

namespace App\Console\Commands;

use App\Services\ContentCompletenessReport;
use Illuminate\Console\Command;

/**
 * Founder-facing content readiness report:
 *
 *   php artisan hexa:content-report               table to the console
 *   php artisan hexa:content-report --json        machine-readable
 *   php artisan hexa:content-report --export=f.csv  CSV export
 *
 * Read-only. Exit code 0 always -- this informs, it does not gate.
 */
class ContentReport extends Command
{
    protected $signature = 'hexa:content-report {--json : Output JSON} {--export= : Write CSV to the given path}';

    protected $description = 'Report missing translations, SEO metadata, media, and broken relations across all CMS content';

    public function handle(ContentCompletenessReport $report): int
    {
        $result = $report->build();

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if ($path = $this->option('export')) {
            $handle = fopen($path, 'w');
            fputcsv($handle, ['type', 'slug', 'status', 'problem']);
            foreach ($result['findings'] as $finding) {
                fputcsv($handle, $finding);
            }
            fclose($handle);
            $this->info('Exported '.count($result['findings'])." findings to {$path}");

            return self::SUCCESS;
        }

        if ($result['findings'] === []) {
            $this->info('No content completeness issues found.');
        } else {
            $this->table(['Type', 'Slug', 'Status', 'Problem'], $result['findings']);
        }

        $this->newLine();
        foreach ($result['totals'] as $key => $value) {
            $this->line(str_pad(str_replace('_', ' ', $key).':', 26).$value);
        }

        return self::SUCCESS;
    }
}
