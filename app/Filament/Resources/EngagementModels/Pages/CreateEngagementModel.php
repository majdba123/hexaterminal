<?php

namespace App\Filament\Resources\EngagementModels\Pages;

use App\Filament\Resources\EngagementModels\EngagementModelResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateEngagementModel extends CreateRecord
{
    use Translatable;

    protected static string $resource = EngagementModelResource::class;
}
