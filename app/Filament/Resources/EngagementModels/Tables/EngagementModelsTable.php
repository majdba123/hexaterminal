<?php

namespace App\Filament\Resources\EngagementModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EngagementModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('pricing_display_mode')->badge()
                    ->color(fn (string $state): string => in_array($state, ['hidden', 'request_quote'], true) ? 'gray' : 'info'),
                TextColumn::make('billing_model')->badge()->toggleable(),
                IconColumn::make('is_featured')->boolean(),
                IconColumn::make('is_published')->boolean(),
                TextColumn::make('sort_order')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_published'),
                SelectFilter::make('pricing_display_mode')
                    ->options([
                        'hidden' => 'hidden',
                        'request_quote' => 'request_quote',
                        'starting_from' => 'starting_from',
                        'indicative_range' => 'indicative_range',
                        'fixed_package' => 'fixed_package',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
