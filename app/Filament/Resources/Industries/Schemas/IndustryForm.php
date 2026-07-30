<?php

namespace App\Filament\Resources\Industries\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\PublishingSection;
use App\Filament\Support\Uploads;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IndustryForm
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
                        Textarea::make('summary')->rows(2)->columnSpanFull(),
                        Textarea::make('description')->rows(4)->columnSpanFull(),
                        TextInput::make('icon'),
                        Uploads::image('cover_image')->directory('industries'),
                        Uploads::altText('cover_image_alt', 'cover image'),
                    ]),
                PublishingSection::make(),
            ]);
    }
}
