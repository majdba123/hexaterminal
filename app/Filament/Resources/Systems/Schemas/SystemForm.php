<?php

namespace App\Filament\Resources\Systems\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\PublishingSection;
use App\Filament\Support\Uploads;
use App\Models\Industry;
use App\Models\System;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SystemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, ?string $state, callable $set, $record) {
                                if ($context === 'create' && filled($state) && ! $record?->slug) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Slugs::input()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->options([
                                System::TYPE_SAAS_PRODUCT => 'SaaS Product',
                                System::TYPE_BUSINESS_SYSTEM => 'Business System',
                                System::TYPE_CLIENT_SYSTEM => 'Client System',
                                System::TYPE_INTERNAL_PRODUCT => 'Internal Product',
                                System::TYPE_PLATFORM => 'Platform',
                                System::TYPE_AI_SYSTEM => 'AI System',
                            ])
                            ->required(),
                        TextInput::make('category'),
                        TextInput::make('tagline')
                            ->columnSpanFull(),
                        Textarea::make('short_description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('full_description')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('problem')
                            ->rows(3),
                        Textarea::make('solution')
                            ->rows(3),
                        Textarea::make('features')
                            ->rows(3)
                            ->helperText('One capability per line.'),
                        Textarea::make('business_outcomes')
                            ->rows(3)
                            ->helperText('One outcome per line.'),
                        Textarea::make('target_audience')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Media & tech')
                    ->columns(2)
                    ->components([
                        Uploads::image('cover_image')
                            ->image()
                            ->directory('systems'),
                        Uploads::altText('cover_image_alt', 'cover image'),
                        Uploads::image('gallery')
                            ->image()
                            ->multiple()
                            ->directory('systems/gallery'),
                        TagsInput::make('tech_stack')
                            ->columnSpanFull(),
                        TextInput::make('demo_url')
                            ->url(),
                        TextInput::make('live_url')
                            ->url(),
                        Select::make('industries')
                            ->relationship('industries', 'slug')
                            ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->name)
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Visibility')
                    ->columns(2)
                    ->components([
                        Toggle::make('is_featured'),
                    ]),
                PublishingSection::make(),
            ]);
    }
}
