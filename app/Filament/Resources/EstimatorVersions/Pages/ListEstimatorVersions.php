<?php

namespace App\Filament\Resources\EstimatorVersions\Pages;

use App\Filament\Resources\EstimatorVersions\EstimatorVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEstimatorVersions extends ListRecords
{
    protected static string $resource = EstimatorVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
