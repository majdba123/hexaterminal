<?php

namespace App\Filament\Resources\PublicClaims;

use App\Filament\Resources\PublicClaims\Pages\CreatePublicClaim;
use App\Filament\Resources\PublicClaims\Pages\EditPublicClaim;
use App\Filament\Resources\PublicClaims\Pages\ListPublicClaims;
use App\Filament\Resources\PublicClaims\Schemas\PublicClaimForm;
use App\Filament\Resources\PublicClaims\Tables\PublicClaimsTable;
use App\Models\PublicClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PublicClaimResource extends Resource
{
    protected static ?string $model = PublicClaim::class;

    protected static ?string $recordTitleAttribute = 'claim_text';

    protected static string|\UnitEnum|null $navigationGroup = 'Trust & Governance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PublicClaimForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicClaimsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicClaims::route('/'),
            'create' => CreatePublicClaim::route('/create'),
            'edit' => EditPublicClaim::route('/{record}/edit'),
        ];
    }
}
