<?php

namespace App\Filament\Resources\FaqItems\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('category'),
                Toggle::make('is_published')
                    ->default(true),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
