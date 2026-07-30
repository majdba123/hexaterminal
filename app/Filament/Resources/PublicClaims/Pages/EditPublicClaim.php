<?php

namespace App\Filament\Resources\PublicClaims\Pages;

use App\Filament\Resources\PublicClaims\PublicClaimResource;
use App\Filament\Resources\PublicClaims\Schemas\PublicClaimForm;
use App\Models\PublicClaim;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPublicClaim extends EditRecord
{
    protected static string $resource = PublicClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $alias = array_search($data['claimable_type'] ?? null, PublicClaimForm::CLAIMABLE_TYPES, true);
        $data['claimable_type_alias'] = $alias ?: null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $alias = $data['claimable_type_alias'] ?? null;
        $current = $this->record instanceof PublicClaim ? $this->record->claimable_type : null;
        $data['claimable_type'] = PublicClaimForm::CLAIMABLE_TYPES[$alias] ?? $current;

        return $data;
    }
}
