<?php

namespace App\Filament\Resources\CostEstimates;

use App\Filament\Resources\CostEstimates\Pages\EditCostEstimate;
use App\Filament\Resources\CostEstimates\Pages\ListCostEstimates;
use App\Filament\Resources\CostEstimates\Schemas\CostEstimateForm;
use App\Filament\Resources\CostEstimates\Tables\CostEstimatesTable;
use App\Models\CostEstimate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Read-mostly submissions inbox. Estimates are created by the public API,
 * never in the CMS, so there is no create page -- editors only triage
 * status and read the result/answers/attribution and linked lead.
 */
class CostEstimateResource extends Resource
{
    protected static ?string $model = CostEstimate::class;

    protected static ?string $recordTitleAttribute = 'public_uuid';

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Cost Estimates';

    public static function form(Schema $schema): Schema
    {
        return CostEstimateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostEstimatesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostEstimates::route('/'),
            'edit' => EditCostEstimate::route('/{record}/edit'),
        ];
    }
}
