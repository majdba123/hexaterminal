<?php

namespace App\Filament\Resources\PricingProfiles;

use App\Filament\Resources\PricingProfiles\Pages\CreatePricingProfile;
use App\Filament\Resources\PricingProfiles\Pages\EditPricingProfile;
use App\Filament\Resources\PricingProfiles\Pages\ListPricingProfiles;
use App\Filament\Resources\PricingProfiles\Schemas\PricingProfileForm;
use App\Filament\Resources\PricingProfiles\Tables\PricingProfilesTable;
use App\Models\PricingProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

/**
 * The founder-approval gate for public price numbers. A profile is created
 * unapproved; a number never reaches the public site until an admin uses
 * the Approve action here (which stamps approved_by/approved_at). This is
 * the single place commercial pricing is authorized.
 */
class PricingProfileResource extends Resource
{
    use Translatable;

    protected static ?string $model = PricingProfile::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Pricing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Pricing Profiles';

    public static function form(Schema $schema): Schema
    {
        return PricingProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPricingProfiles::route('/'),
            'create' => CreatePricingProfile::route('/create'),
            'edit' => EditPricingProfile::route('/{record}/edit'),
        ];
    }
}
