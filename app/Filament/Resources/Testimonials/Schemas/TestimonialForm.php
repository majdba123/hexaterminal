<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Testimonial')
                    ->columns(2)
                    ->components([
                        TextInput::make('author_name')->required(),
                        TextInput::make('author_title'),
                        TextInput::make('company'),
                        Uploads::image('company_logo')->directory('testimonials'),
                        Textarea::make('content')->required()->rows(4)->columnSpanFull(),
                        Select::make('rating')
                            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                            ->required(),
                        DatePicker::make('given_at'),
                    ]),
                Section::make('Moderation')
                    ->columns(2)
                    ->components([
                        Toggle::make('is_approved')
                            ->helperText('Only approved testimonials are ever exposed publicly.'),
                        Toggle::make('is_featured'),
                    ]),
            ]);
    }
}
