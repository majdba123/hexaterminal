<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use App\Filament\Support\Slugs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')
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
                    Slugs::input()->required()->unique(ignoreRecord: true),
                    Textarea::make('description')->rows(2)->columnSpanFull(),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
        ]);
    }
}
