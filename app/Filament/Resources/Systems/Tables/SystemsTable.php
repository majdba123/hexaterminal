<?php

namespace App\Filament\Resources\Systems\Tables;

use App\Models\System;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SystemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        System::TYPE_SAAS_PRODUCT => 'SaaS Product',
                        System::TYPE_BUSINESS_SYSTEM => 'Business System',
                        System::TYPE_CLIENT_SYSTEM => 'Client System',
                        System::TYPE_INTERNAL_PRODUCT => 'Internal Product',
                        System::TYPE_PLATFORM => 'Platform',
                        System::TYPE_AI_SYSTEM => 'AI System',
                    ]),
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_featured'),
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
