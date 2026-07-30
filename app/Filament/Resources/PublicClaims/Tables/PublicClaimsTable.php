<?php

namespace App\Filament\Resources\PublicClaims\Tables;

use App\Models\PublicClaim;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PublicClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('claim_text')->limit(60)->searchable(),
                TextColumn::make('claimable_type')->label('Entity type')->formatStateUsing(
                    fn (?string $state) => $state ? class_basename($state) : '—'
                ),
                TextColumn::make('verification_status')->badge(),
                IconColumn::make('confidential')->boolean(),
                IconColumn::make('approved_for_publication')->boolean(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('category')->options(
                    array_combine(PublicClaim::CATEGORIES, PublicClaim::CATEGORIES)
                ),
                SelectFilter::make('verification_status')->options(
                    array_combine(PublicClaim::VERIFICATION_STATUSES, PublicClaim::VERIFICATION_STATUSES)
                ),
                TernaryFilter::make('approved_for_publication'),
                TernaryFilter::make('confidential'),
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
