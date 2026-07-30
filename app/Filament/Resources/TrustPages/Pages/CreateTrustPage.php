<?php

namespace App\Filament\Resources\TrustPages\Pages;

use App\Filament\Resources\TrustPages\TrustPageResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateTrustPage extends CreateRecord
{
    use Translatable;

    protected static string $resource = TrustPageResource::class;
}
