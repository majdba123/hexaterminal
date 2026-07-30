<?php

namespace App\Filament\Widgets;

use App\Models\AiGeneration;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Services\ContentCompletenessReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Operational content-readiness snapshot. Every number here is something an
 * editor can act on directly -- no vanity metrics.
 */
class ContentQualityWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $report = app(ContentCompletenessReport::class)->build();
        $totals = $report['totals'];

        $draftCount = collect([Service::class, System::class, CaseStudy::class, Industry::class, Article::class])
            ->sum(fn ($class) => $class::where('status', 'draft')->count());

        $scheduledCount = collect([Service::class, System::class, CaseStudy::class, Industry::class, Article::class])
            ->sum(fn ($class) => $class::where('status', 'scheduled')->count());

        return [
            Stat::make('Content findings', (string) $totals['findings'])
                ->description('Missing fields, SEO, or media across all content')
                ->color($totals['findings'] > 0 ? 'warning' : 'success'),
            Stat::make('Drafts awaiting review', (string) $draftCount)
                ->color('gray'),
            Stat::make('Scheduled', (string) $scheduledCount)
                ->color('info'),
            Stat::make('Missing Arabic fields', (string) $totals['missing_arabic'])
                ->color($totals['missing_arabic'] > 0 ? 'danger' : 'success'),
            Stat::make('Missing English fields', (string) $totals['missing_english'])
                ->color($totals['missing_english'] > 0 ? 'danger' : 'success'),
            Stat::make('Failed AI generations', (string) AiGeneration::where('status', AiGeneration::STATUS_FAILED)->count())
                ->color('danger'),
        ];
    }
}
