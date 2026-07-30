<?php

namespace App\Filament\Resources\TrustPages\Tables;

use App\Models\TrustPage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TrustPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page_type')->badge()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('founder_approved')->boolean(),
                IconColumn::make('legal_approved')->boolean(),
                IconColumn::make('security_approved')->boolean(),
                TextColumn::make('next_review_at')->dateTime()->sortable(),
                TextColumn::make('sort_order')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('page_type')->options(
                    array_combine(TrustPage::TYPES, TrustPage::TYPES)
                ),
                TernaryFilter::make('is_published'),
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
