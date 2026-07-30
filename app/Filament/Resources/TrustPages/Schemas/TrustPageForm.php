<?php

namespace App\Filament\Resources\TrustPages\Schemas;

use App\Filament\Support\Slugs;
use App\Filament\Support\PublishingSection;
use App\Models\TrustPage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TrustPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        Select::make('page_type')
                            ->options(array_combine(TrustPage::TYPES, array_map(
                                fn (string $t) => Str::headline($t),
                                TrustPage::TYPES,
                            )))
                            ->required(),
                        TextInput::make('title')
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
                        Repeater::make('sections')
                            ->schema([
                                TextInput::make('heading')->required(),
                                Textarea::make('body')->rows(4)->required(),
                            ])
                            ->columnSpanFull()
                            ->helperText('Structured content sections rendered in order. A page cannot publish without at least one section.'),
                        Repeater::make('faqs')
                            ->schema([
                                TextInput::make('question')->required(),
                                Textarea::make('answer')->rows(2)->required(),
                            ])
                            ->columnSpanFull(),
                        Repeater::make('cta')
                            ->schema([
                                TextInput::make('label')->required(),
                                TextInput::make('url')->url()->required(),
                            ])
                            ->maxItems(1)
                            ->columnSpanFull(),
                    ]),
                Section::make('Governance')
                    ->columns(2)
                    ->components([
                        Select::make('content_owner_id')
                            ->relationship('contentOwner', 'name')
                            ->searchable(),
                        Select::make('reviewer_id')
                            ->relationship('reviewer', 'name')
                            ->searchable(),
                        Toggle::make('founder_approved'),
                        Toggle::make('legal_approved'),
                        Toggle::make('security_approved')
                            ->helperText('Required (with founder_approved) before a "security" page_type can go public.'),
                        DateTimePicker::make('reviewed_at'),
                        DateTimePicker::make('next_review_at'),
                    ]),
                Section::make('Visibility')
                    ->columns(2)
                    ->components([
                        Toggle::make('noindex')->default(true),
                        Toggle::make('show_in_nav'),
                        Toggle::make('show_in_footer'),
                    ]),
                PublishingSection::make(),
            ]);
    }
}
