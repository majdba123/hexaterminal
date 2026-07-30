<?php

namespace App\Filament\Resources\EstimatorVersions;

use App\Filament\Resources\EstimatorVersions\Pages\CreateEstimatorVersion;
use App\Filament\Resources\EstimatorVersions\Pages\EditEstimatorVersion;
use App\Filament\Resources\EstimatorVersions\Pages\ListEstimatorVersions;
use App\Filament\Resources\EstimatorVersions\Schemas\EstimatorVersionForm;
use App\Filament\Resources\EstimatorVersions\Tables\EstimatorVersionsTable;
use App\Models\EstimatorVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Estimator version lifecycle. Version meta is editable only while draft;
 * an active/archived version's fields are locked (clone to change it) so
 * historical estimates stay reproducible. Question/rule authoring is done
 * via the fixture seeder or a cloned draft's data -- see
 * docs/architecture/pricing-estimator-architecture.md.
 */
class EstimatorVersionResource extends Resource
{
    protected static ?string $model = EstimatorVersion::class;

    protected static ?string $recordTitleAttribute = 'label';

    protected static string|\UnitEnum|null $navigationGroup = 'Pricing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Estimator Versions';

    public static function form(Schema $schema): Schema
    {
        return EstimatorVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EstimatorVersionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstimatorVersions::route('/'),
            'create' => CreateEstimatorVersion::route('/create'),
            'edit' => EditEstimatorVersion::route('/{record}/edit'),
        ];
    }
}
