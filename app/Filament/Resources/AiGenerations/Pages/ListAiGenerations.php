<?php

namespace App\Filament\Resources\AiGenerations\Pages;

use App\Filament\Resources\AiGenerations\AiGenerationResource;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Services\AiSeo\AiSeoService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAiGenerations extends ListRecords
{
    protected static string $resource = AiGenerationResource::class;

    /** @return array<class-string, string> */
    private function targetTypes(): array
    {
        return [
            Service::class => 'Service',
            System::class => 'System',
            CaseStudy::class => 'Case Study',
            Industry::class => 'Industry',
            Article::class => 'Article',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate suggestion')
                ->icon('heroicon-o-sparkles')
                ->schema([
                    Select::make('target_class')
                        ->label('Content type')
                        ->options($this->targetTypes())
                        ->live()
                        ->required(),
                    Select::make('target_id')
                        ->label('Target')
                        ->options(function (callable $get) {
                            $class = $get('target_class');

                            return $class ? $class::query()->pluck('slug', 'id') : [];
                        })
                        ->searchable()
                        ->required(),
                    Select::make('locale')
                        ->options(['en' => 'English', 'ar' => 'Arabic'])
                        ->default('en')
                        ->required(),
                    Select::make('type')
                        ->label('Suggestion type')
                        ->options(array_combine(AiSeoService::TYPES, array_map(
                            fn ($t) => str_replace('_', ' ', ucfirst($t)),
                            AiSeoService::TYPES,
                        )))
                        ->required(),
                ])
                ->action(function (array $data, AiSeoService $service) {
                    if (! $service->enabled()) {
                        Notification::make()
                            ->title('AI SEO assistant is disabled')
                            ->body('Set ANTHROPIC_API_KEY to enable real generations.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $target = $data['target_class']::findOrFail($data['target_id']);
                    $generation = $service->suggest($target, $data['type'], $data['locale'], auth()->user());

                    if ($generation->status === 'failed') {
                        Notification::make()->title('Generation failed: '.$generation->failure_reason)->danger()->send();
                    } else {
                        Notification::make()->title('Suggestion generated')->success()->send();
                    }

                    $this->resetTable();
                }),
        ];
    }
}
