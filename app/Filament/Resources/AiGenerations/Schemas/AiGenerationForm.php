<?php

namespace App\Filament\Resources\AiGenerations\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Field-level review, not a single blind-apply button: `output` is the only
 * editable field (the reviewer's edited draft), everything else -- including
 * `status` -- is system-managed and changed only via the Approve/Reject
 * header actions on EditAiGeneration, which call AiSeoService so provenance
 * and the SeoMeta write (for appliable types only) stay consistent.
 */
class AiGenerationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Generation')
                    ->description('System-generated -- not editable.')
                    ->columns(2)
                    ->components([
                        TextInput::make('provider')->disabled(),
                        TextInput::make('model')->disabled(),
                        TextInput::make('field')->disabled()->label('Suggestion type'),
                        TextInput::make('locale')->disabled(),
                        TextInput::make('target_type')->disabled(),
                        TextInput::make('target_id')->disabled(),
                        TextInput::make('input_tokens')->disabled(),
                        TextInput::make('output_tokens')->disabled(),
                        TextInput::make('estimated_cost_usd')->disabled()->prefix('$'),
                        TextInput::make('latency_ms')->disabled()->suffix('ms'),
                        TextInput::make('status')->disabled(),
                        TextInput::make('error_category')->disabled(),
                        Textarea::make('failure_reason')->disabled()->columnSpanFull(),
                    ]),
                Section::make('Review')
                    ->description('Edit the draft below before approving if it needs changes. Only appliable types (SEO title/description) write back to the content automatically on approval -- everything else is advisory.')
                    ->components([
                        Textarea::make('output')
                            ->label('Suggestion (editable)')
                            ->rows(8)
                            ->columnSpanFull(),
                        Placeholder::make('review_meta')
                            ->label('Review status')
                            ->content(function ($record): string {
                                if (! $record?->reviewed_by) {
                                    return 'Not yet reviewed';
                                }
                                $reviewer = $record->reviewedByUser;
                                $name = $reviewer !== null ? $reviewer->name : 'user #'.$record->reviewed_by;

                                return 'Reviewed by '.$name.' at '.$record->reviewed_at?->format('Y-m-d H:i');
                            }),
                    ]),
            ]);
    }
}
