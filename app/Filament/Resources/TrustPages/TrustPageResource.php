<?php

namespace App\Filament\Resources\TrustPages;

use App\Filament\Resources\TrustPages\Pages\CreateTrustPage;
use App\Filament\Resources\TrustPages\Pages\EditTrustPage;
use App\Filament\Resources\TrustPages\Pages\ListTrustPages;
use App\Filament\Resources\TrustPages\Schemas\TrustPageForm;
use App\Filament\Resources\TrustPages\Tables\TrustPagesTable;
use App\Models\TrustPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class TrustPageResource extends Resource
{
    use Translatable;

    protected static ?string $model = TrustPage::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'Trust & Governance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TrustPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrustPagesTable::configure($table);
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
            'index' => ListTrustPages::route('/'),
            'create' => CreateTrustPage::route('/create'),
            'edit' => EditTrustPage::route('/{record}/edit'),
        ];
    }
}
