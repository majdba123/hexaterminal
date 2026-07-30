<?php

namespace App\Filament\Resources\CostEstimates\Schemas;

use App\Models\CostEstimate;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostEstimateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Result')
                    ->columns(2)
                    ->components([
                        Placeholder::make('band')
                            ->label('Estimated band')
                            ->content(fn (CostEstimate $r): string => "{$r->currency} ".number_format($r->amount_min).'–'.number_format($r->amount_max)),
                        Placeholder::make('timeline')
                            ->label('Timeline (weeks)')
                            ->content(fn (CostEstimate $r): string => "{$r->timeline_weeks_min}–{$r->timeline_weeks_max}"),
                        Placeholder::make('complexity')->content(fn (CostEstimate $r): string => (string) $r->complexity),
                        Placeholder::make('confidence')->content(fn (CostEstimate $r): string => (string) $r->confidence),
                        Placeholder::make('drivers')
                            ->label('Cost drivers')
                            ->columnSpanFull()
                            ->content(fn (CostEstimate $r): string => collect($r->cost_drivers ?? [])
                                ->map(fn ($d) => ($d['label']['en'] ?? $d['key'] ?? '').' ('.($d['weight'] ?? '').')')
                                ->implode(', ')),
                        Placeholder::make('answers')
                            ->label('Answers')
                            ->columnSpanFull()
                            ->content(fn (CostEstimate $r): string => collect($r->answers ?? [])
                                ->map(fn ($v, $k) => $k.': '.(is_array($v) ? implode('/', $v) : $v))
                                ->implode(' · ')),
                    ]),
                Section::make('Attribution & lead')
                    ->columns(2)
                    ->components([
                        Placeholder::make('recommended')
                            ->label('Recommended model')
                            ->content(fn (CostEstimate $r): string => $r->recommendedEngagementModel?->getTranslation('title', 'en') ?? '—'),
                        Placeholder::make('lead')
                            ->label('Linked lead')
                            ->content(fn (CostEstimate $r): string => $r->contactLead
                                ? $r->contactLead->name.' <'.$r->contactLead->email.'> · score '.$r->contactLead->score
                                : 'Anonymous (no lead)'),
                        Placeholder::make('utm')
                            ->label('UTM source')
                            ->content(fn (CostEstimate $r): string => $r->contactLead?->utm['source'] ?? '—'),
                        Placeholder::make('created')
                            ->label('Created / expires')
                            ->content(fn (CostEstimate $r): string => ($r->created_at?->toDateTimeString() ?? '—').' / '.($r->expires_at?->toDateString() ?? '—')),
                    ]),
                Section::make('Triage')
                    ->components([
                        Select::make('status')
                            ->options(array_combine(CostEstimate::STATUSES, CostEstimate::STATUSES))
                            ->required(),
                    ]),
            ]);
    }
}
