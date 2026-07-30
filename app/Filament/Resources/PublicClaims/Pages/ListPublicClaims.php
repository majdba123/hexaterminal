<?php

namespace App\Filament\Resources\PublicClaims\Pages;

use App\Filament\Resources\PublicClaims\PublicClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicClaims extends ListRecords
{
    protected static string $resource = PublicClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
