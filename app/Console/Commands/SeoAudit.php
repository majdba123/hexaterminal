<?php

namespace App\Console\Commands;

use App\Services\SeoAuditReport;
use Illuminate\Console\Command;

/**
 * SEO health audit across all governed content types:
 *
 *   php artisan hexa:seo-audit                table + scores to the console
 *   php artisan hexa:seo-audit --json         machine-readable
 *   php artisan hexa:seo-audit --export=f.csv CSV export
 *
 * Exits non-zero (1) only when real blockers exist (missing title/
 * description on published content, invalid canonical, duplicate SEO
 * title/description, noindex-but-in-sitemap contradictions, empty
 * indexable pages, expired approved public claims). Cosmetic warnings
 * (length, missing OG image) never fail the gate -- see
 * docs/architecture/final-remaining-gap-inventory.md.
 */
class SeoAudit extends Command
{
    protected $signature = 'hexa:seo-audit {--json : Output JSON} {--export= : Write CSV to the given path}';

    protected $description = 'Audit SEO health (titles, descriptions, canonicals, duplicates, noindex/sitemap contradictions) across all content types';

    public function handle(SeoAuditReport $report): int
    {
        $result = $report->build();

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $result['blocker_count'] > 0 ? self::FAILURE : self::SUCCESS;
        }

        if ($path = $this->option('export')) {
            $handle = fopen($path, 'w');
            fputcsv($handle, ['type', 'slug', 'severity', 'check', 'detail']);
            foreach ($result['findings'] as $finding) {
                fputcsv($handle, $finding);
            }
            fclose($handle);
            $this->info('Exported '.count($result['findings'])." findings to {$path}");

            return $result['blocker_count'] > 0 ? self::FAILURE : self::SUCCESS;
        }

        if ($result['findings'] === []) {
            $this->info('No SEO issues found.');
        } else {
            $this->table(['Type', 'Slug', 'Severity', 'Check', 'Detail'], $result['findings']);
        }

        $this->newLine();
        $this->line('Category scores:');
        foreach ($result['category_scores'] as $type => $score) {
            $this->line('  '.str_pad($type.':', 16).$score.'/100');
        }
        $this->newLine();
        $this->line('Overall score: '.$result['overall_score'].'/100');
        $this->line('Blockers: '.$result['blocker_count']);

        return $result['blocker_count'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
