<?php

namespace App\Filament\Resources\TrustPages\Pages;

use App\Filament\Resources\TrustPages\TrustPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListTrustPages extends ListRecords
{
    use Translatable;

    protected static string $resource = TrustPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
