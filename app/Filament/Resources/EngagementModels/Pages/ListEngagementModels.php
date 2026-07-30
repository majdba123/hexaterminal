<?php

namespace App\Filament\Resources\EngagementModels\Pages;

use App\Filament\Resources\EngagementModels\EngagementModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListEngagementModels extends ListRecords
{
    use Translatable;

    protected static string $resource = EngagementModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
