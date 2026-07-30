<?php

namespace App\Filament\Resources\PricingProfiles\Tables;

use App\Models\PricingProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PricingProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('priceable.title')->label('Engagement model')->limit(30),
                TextColumn::make('currency')->badge(),
                TextColumn::make('min_amount')->numeric()->label('Min'),
                TextColumn::make('max_amount')->numeric()->label('Max'),
                IconColumn::make('approved_for_publication')->boolean()->label('Approved'),
                TextColumn::make('effective_date')->date()->toggleable(),
                TextColumn::make('approver.name')->label('Approved by')->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('approved_for_publication'),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'AED' => 'AED', 'SAR' => 'SAR']),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Approving publishes this price band once its effective date has arrived. Only approve founder-confirmed commercial pricing.')
                    ->visible(fn (PricingProfile $record): bool => ! $record->approved_for_publication
                        && (auth()->user()?->hasRole('admin') ?? false))
                    ->action(function (PricingProfile $record): void {
                        $record->forceFill([
                            'approved_for_publication' => true,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ])->save();
                    }),
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PricingProfile $record): bool => $record->approved_for_publication
                        && (auth()->user()?->hasRole('admin') ?? false))
                    ->action(function (PricingProfile $record): void {
                        $record->forceFill([
                            'approved_for_publication' => false,
                            'approved_by' => null,
                            'approved_at' => null,
                        ])->save();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
