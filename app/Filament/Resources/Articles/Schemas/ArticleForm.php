<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\PublishingSection;
use App\Filament\Support\Uploads;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, ?string $state, callable $set, $record) {
                                if ($context === 'create' && filled($state) && ! $record?->slug) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),
                        Slugs::input()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Textarea::make('excerpt')->rows(2)->columnSpanFull(),
                        Textarea::make('body')->rows(12)->columnSpanFull(),
                    ]),
                Section::make('Meta')
                    ->columns(2)
                    ->components([
                        Uploads::image('cover_image')->directory('articles'),
                        Uploads::altText('cover_image_alt', 'cover image'),
                        Uploads::image('og_image')
                            ->image()
                            ->directory('articles/og')
                            ->helperText('Social share image; falls back to the cover image.'),
                        Select::make('author_id')
                            ->relationship('author', 'name')
                            ->searchable(),
                        Select::make('article_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->helperText('Featured articles surface first on the Insights hub.'),
                        DateTimePicker::make('updated_content_at')
                            ->helperText('Shown as "last updated" for SEO freshness signals.'),
                    ]),
                PublishingSection::make(withSortOrder: false),
            ]);
    }
}
