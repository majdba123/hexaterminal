<?php

namespace App\Filament\Resources\AiGenerations\Pages;

use App\Filament\Resources\AiGenerations\AiGenerationResource;
use App\Models\AiGeneration;
use App\Services\AiSeo\AiSeoService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class EditAiGeneration extends EditRecord
{
    protected static string $resource = AiGenerationResource::class;

    /** getRecord()'s declared type is loose (Model|int|string); this page only ever edits AiGeneration rows. */
    private function generation(): AiGeneration
    {
        $record = $this->getRecord();
        if (! $record instanceof AiGeneration) {
            throw new RuntimeException('Expected an AiGeneration record.');
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => in_array($this->generation()->status, ['generated', 'reviewed'], true))
                ->requiresConfirmation()
                ->modalDescription('Approving applies the reviewed text to SEO title/description fields automatically for those suggestion types. Advisory suggestions (outlines, FAQs, internal links, summaries, social snippets, answer sections) are never applied automatically -- copy them manually.')
                ->action(function (AiSeoService $service) {
                    $state = $this->form->getState();
                    $service->approve($this->generation(), auth()->user(), $state['output'] ?? null);
                    Notification::make()->title('Suggestion approved')->success()->send();
                    $this->fillForm();
                }),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => in_array($this->generation()->status, ['generated', 'reviewed'], true))
                ->requiresConfirmation()
                ->action(function (AiSeoService $service) {
                    $service->reject($this->generation(), auth()->user());
                    Notification::make()->title('Suggestion rejected')->send();
                    $this->fillForm();
                }),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        // Direct saves only touch `output` (the reviewer's edited draft);
        // status transitions happen exclusively via approve()/reject() above.
        $record->update(['output' => $data['output'] ?? $record->output]);

        return $record;
    }
}
