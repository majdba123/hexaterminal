<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('from_path')
                    ->required()
                    ->prefix('/')
                    ->unique(ignoreRecord: true),
                TextInput::make('to_path')
                    ->required()
                    ->helperText('Relative path or full URL.'),
                Select::make('status_code')
                    ->options([301 => '301 Permanent', 302 => '302 Temporary'])
                    ->default(301)
                    ->required(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
