<?php

namespace App\Filament\Widgets;

use App\Models\ContactLead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Actionable lead-pipeline snapshot. No revenue/conversion-rate claims --
 * we don't have reliable revenue data to back them.
 */
class MarketingOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $total = ContactLead::activePipeline()->count();
        $new = ContactLead::status(ContactLead::STATUS_NEW)->count();
        $qualified = ContactLead::status(ContactLead::STATUS_QUALIFIED)->count();
        $overdue = ContactLead::overdueFollowUp()->count();
        $spamRate = ContactLead::count() > 0
            ? round(ContactLead::status(ContactLead::STATUS_SPAM)->count() / ContactLead::count() * 100, 1)
            : 0.0;

        $topSource = ContactLead::activePipeline()
            ->whereNotNull('source_page')
            ->selectRaw('source_page, COUNT(*) as total')
            ->groupBy('source_page')
            ->orderByDesc('total')
            ->first();

        $topSourcePage = $topSource !== null ? (string) $topSource->getAttribute('source_page') : '—';
        $topSourceDescription = $topSource !== null ? $topSource->getAttribute('total').' leads' : 'No data yet';

        return [
            Stat::make('Active leads', (string) $total)->color('primary'),
            Stat::make('New (unreviewed)', (string) $new)->color('warning'),
            Stat::make('Qualified', (string) $qualified)->color('success'),
            Stat::make('Overdue follow-ups', (string) $overdue)
                ->color($overdue > 0 ? 'danger' : 'success'),
            Stat::make('Spam rate', $spamRate.'%')->color('gray'),
            Stat::make('Top landing page', $topSourcePage)
                ->description($topSourceDescription),
        ];
    }
}
