<?php

namespace App\Filament\Resources\EstimatorVersions\Tables;

use App\Models\EstimatorVersion;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class EstimatorVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->badge()->sortable(),
                TextColumn::make('label')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('questions_count')->counts('questions')->label('Questions'),
                TextColumn::make('estimates_count')->counts('estimates')->label('Estimates'),
                TextColumn::make('activated_at')->dateTime()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Activating makes this the single version accepting new estimates and archives the current active one. Existing estimates keep their original version.')
                    ->visible(fn (EstimatorVersion $record): bool => ! $record->is_active
                        && (auth()->user()?->hasRole('admin') ?? false))
                    ->action(function (EstimatorVersion $record): void {
                        if ($record->questions()->count() === 0 || $record->rules()->count() === 0) {
                            Notification::make()->danger()->title('Cannot activate an empty version')
                                ->body('Add questions and rules before activating.')->send();

                            return;
                        }
                        $record->activate(auth()->id());
                        Notification::make()->success()->title("Version {$record->key} is now active")->send();
                    }),
                Action::make('clone')
                    ->label('Clone to draft')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalDescription('Creates an editable draft copy (questions + rules) so an active version is never altered in place.')
                    ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false)
                    ->action(function (EstimatorVersion $record): void {
                        self::clone($record);
                        Notification::make()->success()->title('Cloned to a new draft version')->send();
                    }),
                EditAction::make(),
            ]);
    }

    private static function clone(EstimatorVersion $source): void
    {
        DB::transaction(function () use ($source) {
            $newKey = $source->key.'-copy-'.now()->format('YmdHis');
            $clone = $source->replicate(['is_active', 'activated_at', 'activated_by']);
            $clone->key = $newKey;
            $clone->label = $source->label.' (copy)';
            $clone->status = 'draft';
            $clone->is_active = false;
            $clone->activated_at = null;
            $clone->activated_by = null;
            $clone->created_by = auth()->id();
            $clone->save();

            foreach ($source->questions()->get() as $question) {
                $q = $question->replicate();
                $q->estimator_version_id = $clone->id;
                $q->save();
            }
            foreach ($source->rules()->get() as $rule) {
                $r = $rule->replicate();
                $r->estimator_version_id = $clone->id;
                $r->save();
            }
        });
    }
}
