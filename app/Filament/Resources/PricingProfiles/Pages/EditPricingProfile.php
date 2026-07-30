<?php

namespace App\Filament\Resources\PricingProfiles\Pages;

use App\Filament\Resources\PricingProfiles\PricingProfileResource;
use App\Filament\Support\PreviewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPricingProfile extends EditRecord
{
    use Translatable;

    protected static string $resource = PricingProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            PreviewAction::make(),
            DeleteAction::make(),
        ];
    }
}
