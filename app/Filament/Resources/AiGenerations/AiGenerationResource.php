<?php

namespace App\Filament\Resources\AiGenerations;

use App\Filament\Resources\AiGenerations\Pages\EditAiGeneration;
use App\Filament\Resources\AiGenerations\Pages\ListAiGenerations;
use App\Filament\Resources\AiGenerations\Schemas\AiGenerationForm;
use App\Filament\Resources\AiGenerations\Tables\AiGenerationsTable;
use App\Models\AiGeneration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Review-only UI for AI-SEO suggestion provenance (Stage 14, not yet
 * populated -- the AI generation service is built in Phase 7). Rows are
 * only ever created by that service, never manually, so there is no
 * "create" page here.
 */
class AiGenerationResource extends Resource
{
    protected static ?string $model = AiGeneration::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AiGenerationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiGenerationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiGenerations::route('/'),
            'edit' => EditAiGeneration::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
