<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\Uploads;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(2)
                    ->components([
                        TextInput::make('first_name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, ?string $state, callable $set, $get, $record) {
                                if ($context === 'create' && filled($state) && ! $record?->slug) {
                                    $set('slug', Str::slug(trim($state.' '.$get('last_name'))));
                                }
                            }),
                        TextInput::make('last_name'),
                        Slugs::input()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        TextInput::make('position')->columnSpanFull(),
                        Textarea::make('bio')->rows(4)->columnSpanFull(),
                        TextInput::make('specialization'),
                        TagsInput::make('expertise')->columnSpanFull(),
                        TagsInput::make('languages')->columnSpanFull(),
                        TextInput::make('location')->helperText('Optional public location (city/region only).'),
                        TextInput::make('email')->email(),
                        TextInput::make('phone')->tel(),
                    ]),
                Section::make('Media & links')
                    ->columns(2)
                    ->components([
                        Uploads::image('photo')->directory('team'),
                        Uploads::altText('photo_alt', 'photo'),
                        Uploads::document('cv_file')->directory('team/cv'),
                        TextInput::make('github_url')->url(),
                        TextInput::make('linkedin_url')->url(),
                    ]),
                Section::make('Publishing & governance')
                    ->columns(2)
                    ->components([
                        Toggle::make('is_published')->default(true),
                        Toggle::make('publication_consent')
                            ->helperText('Required in addition to Published before this member appears publicly.'),
                        Toggle::make('is_founder'),
                        Toggle::make('seo_eligible'),
                        Toggle::make('person_jsonld_eligible')
                            ->helperText('Only emit Person structured data when explicitly eligible.'),
                        TextInput::make('sort_order')->numeric()->default(0)->required(),
                        DateTimePicker::make('reviewed_at'),
                        DateTimePicker::make('next_review_at'),
                    ]),
            ]);
    }
}
