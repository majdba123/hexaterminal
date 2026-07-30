<?php

namespace App\Filament\Resources\ArticleTags\Schemas;

use App\Filament\Support\Slugs;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tag')
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
                ]),
        ]);
    }
}
