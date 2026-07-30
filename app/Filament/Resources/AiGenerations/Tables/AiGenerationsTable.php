<?php

namespace App\Filament\Resources\AiGenerations\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AiGenerationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('target_id')
                    ->searchable(),
                TextColumn::make('field')
                    ->searchable(),
                TextColumn::make('provider')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected', 'failed' => 'danger',
                        'generated', 'reviewed' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('locale')->toggleable(),
                TextColumn::make('estimated_cost_usd')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('latency_ms')->suffix('ms')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'generated' => 'Generated',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Review'),
            ]);
    }
}
