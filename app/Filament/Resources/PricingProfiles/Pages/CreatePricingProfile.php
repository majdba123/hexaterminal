<?php

namespace App\Filament\Resources\PricingProfiles\Pages;

use App\Filament\Resources\PricingProfiles\PricingProfileResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreatePricingProfile extends CreateRecord
{
    use Translatable;

    protected static string $resource = PricingProfileResource::class;
}
