<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Shared editorial-workflow section for every content resource. Replaces the
 * old bare `is_published` toggle with the explicit workflow status; the
 * HasEditorialWorkflow trait keeps `is_published` in sync, so the public API
 * contract is unchanged. Shows a read-only audit trail (who created/updated/
 * approved/published) sourced from the trait's stamped columns.
 */
class PublishingSection
{
    public static function make(bool $withSortOrder = true): Section
    {
        $components = [
            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'in_review' => 'In review',
                    'approved' => 'Approved',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->default('draft')
                ->required()
                ->helperText('Publishing is explicit: content is public only when Published (or Scheduled with a future date, once the date passes).'),
            DateTimePicker::make('published_at')
                ->helperText('Required for Scheduled; defaults to now when publishing.'),
        ];

        if ($withSortOrder) {
            $components[] = TextInput::make('sort_order')->numeric()->default(0)->required();
        }

        $components[] = Placeholder::make('audit')
            ->label('Audit trail')
            ->columnSpanFull()
            ->content(function ($record): string {
                if (! $record) {
                    return 'New record';
                }
                $userName = fn ($user): ?string => $user !== null ? $user->name : null;
                $line = fn (?string $name, $at) => $name ? $name.($at ? ' at '.$at->format('Y-m-d H:i') : '') : '—';

                return 'Created by: '.$line($userName($record->creator), $record->created_at)
                    .' · Updated by: '.$line($userName($record->updater), $record->updated_at)
                    .' · Approved by: '.$line($userName($record->approver), $record->approved_at)
                    .' · Published by: '.($userName($record->publisher) ?? '—');
            });

        return Section::make('Publishing')
            ->columns(3)
            ->components($components);
    }
}
