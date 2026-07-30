<?php

namespace App\Filament\Resources\TrustPages\Pages;

use App\Filament\Resources\TrustPages\TrustPageResource;
use App\Filament\Support\PreviewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditTrustPage extends EditRecord
{
    use Translatable;

    protected static string $resource = TrustPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            PreviewAction::make(),
            DeleteAction::make(),
        ];
    }
}
