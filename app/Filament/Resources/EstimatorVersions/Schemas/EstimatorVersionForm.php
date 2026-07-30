<?php

namespace App\Filament\Resources\EstimatorVersions\Schemas;

use App\Models\EstimatorVersion;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EstimatorVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        // Version meta is only editable while draft; active/archived versions
        // are locked so historical estimates stay reproducible. A not-yet-saved
        // record (create page) is always editable.
        //
        // The parameter MUST be nullable. On the create page Filament passes
        // null, not an unsaved model, so a non-nullable `EstimatorVersion`
        // hint threw a TypeError before the `$record->exists` guard below
        // could run -- /cms/estimator-versions/create was a hard 500 for
        // anyone who clicked "New" in the sidebar.
        $lockedIfNotDraft = fn (?EstimatorVersion $record): bool => (bool) $record?->exists
            && $record->status !== 'draft';

        return $schema
            ->components([
                Section::make('Version')
                    ->columns(2)
                    ->components([
                        TextInput::make('key')->required()->disabled($lockedIfNotDraft)
                            ->helperText('Immutable identifier, e.g. "v1", "v2".'),
                        TextInput::make('label')->required()->disabled($lockedIfNotDraft),
                        // Parameter MUST be named `$record` and be nullable.
                        // Filament injects closure arguments BY NAME, so `$r`
                        // was unresolvable and threw
                        // BindingResolutionException; and on the create page
                        // the value is null.
                        Placeholder::make('status')
                            ->content(fn (?EstimatorVersion $record): string => $record?->status ?? 'draft'),
                        Placeholder::make('counts')
                            ->label('Questions / rules')
                            ->content(fn (?EstimatorVersion $record): string => $record === null
                                ? '0 / 0'
                                : $record->questions()->count().' / '.$record->rules()->count()),
                    ]),
                Section::make('Calculation guardrails')
                    ->description('Base currency is authoritative; rates are fixed USD pegs, never live FX.')
                    ->columns(2)
                    ->components([
                        TextInput::make('base_currency')->default('USD')->disabled($lockedIfNotDraft),
                        TextInput::make('floor_min')->numeric()->default(4000)->disabled($lockedIfNotDraft)
                            ->helperText('Lower guardrail (base currency).'),
                        TextInput::make('ceiling_max')->numeric()->default(400000)->disabled($lockedIfNotDraft)
                            ->helperText('Upper guardrail (base currency).'),
                        KeyValue::make('currency_rates')
                            ->keyLabel('Currency')->valueLabel('Rate vs base')
                            ->default(['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75])
                            ->disabled($lockedIfNotDraft)
                            ->columnSpanFull(),
                        Textarea::make('notes')->rows(2)->columnSpanFull()->disabled($lockedIfNotDraft),
                    ]),
            ]);
    }
}
