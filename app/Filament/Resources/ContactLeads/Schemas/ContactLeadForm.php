<?php

namespace App\Filament\Resources\ContactLeads\Schemas;

use App\Models\ContactLead;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission')
                    ->description('What the prospect submitted -- read-only; work the lead in Operations below.')
                    ->columns(2)
                    ->components([
                        TextInput::make('intent')->disabled(),
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('whatsapp')->disabled(),
                        TextInput::make('company')->disabled(),
                        TextInput::make('company_size')->disabled(),
                        TextInput::make('role_title')->disabled(),
                        TextInput::make('country')->disabled(),
                        TextInput::make('project_type')->disabled(),
                        TextInput::make('industry')->disabled(),
                        TextInput::make('budget_range')->disabled(),
                        TextInput::make('timeline')->disabled(),
                        TextInput::make('preferred_contact_method')->disabled(),
                        Textarea::make('summary')->disabled()->columnSpanFull(),
                        Textarea::make('pain_points')->disabled()->columnSpanFull(),
                    ]),
                Section::make('Attribution')
                    ->collapsed()
                    ->columns(2)
                    ->components([
                        TextInput::make('source_page')->disabled(),
                        TextInput::make('landing_page')->disabled(),
                        TextInput::make('referrer')->disabled(),
                        DateTimePicker::make('first_touch_at')->disabled(),
                        Placeholder::make('utm_summary')
                            ->label('UTM')
                            ->columnSpanFull()
                            ->content(function (?ContactLead $record): string {
                                $utm = $record === null ? [] : ($record->utm ?? []);

                                return $utm === []
                                    ? '—'
                                    : collect($utm)->map(fn ($v, $k) => "{$k}={$v}")->implode(' · ');
                            }),
                    ]),
                Section::make('Qualification')
                    ->columns(2)
                    ->components([
                        Placeholder::make('score_display')
                            ->label('Deterministic score')
                            ->content(function (?ContactLead $record): string {
                                if (! $record || $record->score === null) {
                                    return 'Not scored';
                                }
                                $breakdown = collect($record->score_breakdown ?? [])
                                    ->map(fn ($f) => $f['factor'].' ('.($f['points'] > 0 ? '+' : '').$f['points'].')')
                                    ->implode(', ');

                                return $record->score.'/100 — '.$breakdown;
                            })
                            ->columnSpanFull(),
                        Textarea::make('qualification_summary')
                            ->rows(3)
                            ->helperText('Human qualification notes; the score above is only a queue-ordering hint.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Operations')
                    ->columns(2)
                    ->components([
                        Select::make('status')
                            ->options(array_combine(ContactLead::STATUSES, array_map(
                                fn ($s) => str_replace('_', ' ', ucfirst($s)),
                                ContactLead::STATUSES,
                            )))
                            ->required(),
                        Select::make('priority')
                            ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'])
                            ->required()
                            ->helperText('Admin override wins over the computed score.'),
                        Select::make('assigned_to')
                            ->label('Assignee')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('follow_up_at')
                            ->label('Follow up by'),
                        Textarea::make('notes')
                            ->rows(4)
                            ->helperText('Internal only -- never exposed publicly.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
