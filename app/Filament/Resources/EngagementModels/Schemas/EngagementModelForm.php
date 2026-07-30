<?php

namespace App\Filament\Resources\EngagementModels\Schemas;

use App\Filament\Support\Slugs;
use App\Models\EngagementModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EngagementModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')->required()->columnSpanFull(),
                        Slugs::input()
                            ->helperText('Leave blank to auto-generate from the title.')
                            ->columnSpanFull(),
                        Textarea::make('summary')->rows(2)->columnSpanFull(),
                        Textarea::make('buyer_fit')->label('Suitable buyer')->rows(2)->columnSpanFull(),
                        Textarea::make('typical_scope')->rows(2)->columnSpanFull(),
                        TextInput::make('indicative_duration')->helperText('e.g. "4–6 weeks"'),
                        self::listField('deliverables'),
                        self::listField('included_items'),
                        self::listField('excluded_items'),
                    ]),
                Section::make('Commercial presentation')
                    ->description('How this model is presented. A price NUMBER only ever shows when an approved PricingProfile exists -- see the Pricing Profiles resource.')
                    ->columns(2)
                    ->components([
                        Select::make('pricing_display_mode')
                            ->options(array_combine(EngagementModel::DISPLAY_MODES, EngagementModel::DISPLAY_MODES))
                            ->default('request_quote')
                            ->required()
                            ->helperText('hidden / request_quote never show a number.'),
                        Select::make('billing_model')
                            ->options(array_combine(EngagementModel::BILLING_MODELS, EngagementModel::BILLING_MODELS))
                            ->default('fixed_project')
                            ->required(),
                        TextInput::make('cta_label'),
                        Select::make('cta_intent')
                            ->options([
                                'start_project' => 'Start a project',
                                'request_quote' => 'Request a quote',
                                'book_call' => 'Book a call',
                                'cost_estimate' => 'Cost estimate',
                            ])
                            ->default('request_quote'),
                    ]),
                Section::make('Publication')
                    ->columns(3)
                    ->components([
                        Toggle::make('is_published')->helperText('Content visibility. Publishing never reveals an unapproved price.'),
                        Toggle::make('is_featured'),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
            ]);
    }

    /** A translatable list edited as one item per line. */
    private static function listField(string $name): Textarea
    {
        return Textarea::make($name)
            ->rows(3)
            ->helperText('One item per line.')
            ->columnSpanFull()
            ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : (string) $state)
            ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                array_map('trim', explode("\n", (string) $state)),
                fn ($line) => $line !== '',
            )));
    }
}
