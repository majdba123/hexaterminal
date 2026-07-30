<?php

namespace App\Filament\Resources\EstimatorVersions\Pages;

use App\Filament\Resources\EstimatorVersions\EstimatorVersionResource;
use Filament\Resources\Pages\EditRecord;

class EditEstimatorVersion extends EditRecord
{
    protected static string $resource = EstimatorVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
