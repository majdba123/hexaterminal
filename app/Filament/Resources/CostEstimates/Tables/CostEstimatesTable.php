<?php

namespace App\Filament\Resources\CostEstimates\Tables;

use App\Models\CostEstimate;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CostEstimatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_uuid')->label('Ref')->formatStateUsing(fn (string $state): string => substr($state, 0, 8))->searchable(),
                TextColumn::make('currency')->badge(),
                TextColumn::make('amount_min')->label('Band')
                    ->formatStateUsing(fn ($state, CostEstimate $r): string => number_format($r->amount_min).'–'.number_format($r->amount_max)),
                TextColumn::make('complexity')->badge()->sortable(),
                TextColumn::make('confidence')->badge()->toggleable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'converted' => 'success',
                        'discovery_requested', 'proposal_requested', 'lead_created' => 'info',
                        'spam', 'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('contactLead.name')->label('Lead')->placeholder('Anonymous')->toggleable(),
                TextColumn::make('contactLead.score')->label('Score')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(array_combine(CostEstimate::STATUSES, CostEstimate::STATUSES)),
                SelectFilter::make('complexity')->options([
                    'standard' => 'standard', 'advanced' => 'advanced', 'complex' => 'complex', 'enterprise' => 'enterprise',
                ]),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'AED' => 'AED', 'SAR' => 'SAR']),
            ])
            ->recordActions([
                EditAction::make()->label('View / triage'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): StreamedResponse {
                            /** @var Collection<int, CostEstimate> $records */
                            return response()->streamDownload(function () use ($records) {
                                $out = fopen('php://output', 'w');
                                fputcsv($out, ['ref', 'currency', 'amount_min', 'amount_max', 'weeks_min', 'weeks_max', 'complexity', 'confidence', 'status', 'lead_email', 'lead_score', 'created_at']);
                                foreach ($records as $e) {
                                    fputcsv($out, [
                                        substr($e->public_uuid, 0, 8), $e->currency, $e->amount_min, $e->amount_max,
                                        $e->timeline_weeks_min, $e->timeline_weeks_max, $e->complexity, $e->confidence,
                                        $e->status, $e->contactLead?->email, $e->contactLead?->score,
                                        $e->created_at?->toDateTimeString(),
                                    ]);
                                }
                                fclose($out);
                            }, 'cost-estimates-'.now()->format('Ymd-His').'.csv');
                        }),
                ]),
            ]);
    }
}
