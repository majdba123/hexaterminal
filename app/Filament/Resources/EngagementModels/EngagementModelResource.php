<?php

namespace App\Filament\Resources\EngagementModels;

use App\Filament\Resources\EngagementModels\Pages\CreateEngagementModel;
use App\Filament\Resources\EngagementModels\Pages\EditEngagementModel;
use App\Filament\Resources\EngagementModels\Pages\ListEngagementModels;
use App\Filament\Resources\EngagementModels\Schemas\EngagementModelForm;
use App\Filament\Resources\EngagementModels\Tables\EngagementModelsTable;
use App\Models\EngagementModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class EngagementModelResource extends Resource
{
    use Translatable;

    protected static ?string $model = EngagementModel::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'Pricing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EngagementModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EngagementModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEngagementModels::route('/'),
            'create' => CreateEngagementModel::route('/create'),
            'edit' => EditEngagementModel::route('/{record}/edit'),
        ];
    }
}
