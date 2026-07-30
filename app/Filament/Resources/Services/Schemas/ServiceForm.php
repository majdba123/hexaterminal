<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\PublishingSection;
use App\Filament\Support\Uploads;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, ?string $state, callable $set, $record) {
                                if ($context === 'create' && filled($state) && ! $record?->slug) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpan(1),
                        Slugs::input()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from the English name if left blank on create.')
                            ->columnSpan(1),
                        TextInput::make('tagline')
                            ->columnSpanFull(),
                        Textarea::make('summary')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
                Section::make('Details')
                    ->columns(2)
                    ->components([
                        TextInput::make('icon')
                            ->helperText('Icon name/class, not raw HTML.'),
                        Uploads::image('cover_image')
                            ->image()
                            ->directory('service-offerings'),
                        Uploads::altText('cover_image_alt', 'cover image'),
                        TagsInput::make('features')
                            ->columnSpanFull(),
                        TagsInput::make('tech_stack')
                            ->columnSpanFull(),
                    ]),
                PublishingSection::make(),
            ]);
    }
}
