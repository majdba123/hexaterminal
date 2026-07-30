<?php

namespace App\Filament\Resources\EstimatorVersions\Pages;

use App\Filament\Resources\EstimatorVersions\EstimatorVersionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstimatorVersion extends CreateRecord
{
    protected static string $resource = EstimatorVersionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['is_active'] = false;
        $data['created_by'] = auth()->id();

        return $data;
    }
}
