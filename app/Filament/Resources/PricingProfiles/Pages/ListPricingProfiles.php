<?php

namespace App\Filament\Resources\PricingProfiles\Pages;

use App\Filament\Resources\PricingProfiles\PricingProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListPricingProfiles extends ListRecords
{
    use Translatable;

    protected static string $resource = PricingProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
