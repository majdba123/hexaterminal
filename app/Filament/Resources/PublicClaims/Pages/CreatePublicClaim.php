<?php

namespace App\Filament\Resources\PublicClaims\Pages;

use App\Filament\Resources\PublicClaims\PublicClaimResource;
use App\Filament\Resources\PublicClaims\Schemas\PublicClaimForm;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicClaim extends CreateRecord
{
    protected static string $resource = PublicClaimResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $alias = $data['claimable_type_alias'] ?? null;
        $data['claimable_type'] = PublicClaimForm::CLAIMABLE_TYPES[$alias] ?? null;

        return $data;
    }
}
