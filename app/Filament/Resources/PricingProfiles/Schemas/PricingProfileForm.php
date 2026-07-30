<?php

namespace App\Filament\Resources\PricingProfiles\Schemas;

use App\Models\EngagementModel;
use App\Models\PricingProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Engagement model & currency')
                    ->columns(2)
                    ->components([
                        Hidden::make('priceable_type')->default(EngagementModel::class),
                        Select::make('priceable_id')
                            ->label('Engagement model')
                            ->required()
                            ->options(fn () => EngagementModel::all()
                                ->mapWithKeys(fn (EngagementModel $m) => [$m->id => $m->getTranslation('title', 'en')])
                                ->all()),
                        Select::make('currency')
                            ->options(['USD' => 'USD', 'AED' => 'AED', 'SAR' => 'SAR'])
                            ->required(),
                        TextInput::make('min_amount')->numeric()->minValue(0)->label('Minimum amount'),
                        TextInput::make('max_amount')->numeric()->minValue(0)->label('Maximum amount'),
                        Select::make('price_unit')
                            ->options(['project' => 'project', 'month' => 'month', 'sprint' => 'sprint', 'day' => 'day'])
                            ->default('project'),
                        Select::make('billing_model')
                            ->options(array_combine(EngagementModel::BILLING_MODELS, EngagementModel::BILLING_MODELS))
                            ->default('fixed_project'),
                    ]),
                Section::make('Copy')
                    ->columns(1)
                    ->components([
                        TextInput::make('display_label')->helperText('e.g. "Starting from".'),
                        Textarea::make('assumptions')->rows(2),
                        Textarea::make('exclusions')->rows(2),
                        Textarea::make('disclaimer')->rows(2),
                    ]),
                Section::make('Founder approval')
                    ->description('A price is public ONLY when approved AND the effective date has arrived. Approve via the table action, not by editing these fields directly.')
                    ->columns(2)
                    ->components([
                        Placeholder::make('approved_state')
                            ->label('Approved for publication')
                            // Nullable: on the create page Filament passes
                            // null and a non-nullable hint made
                            // /cms/pricing-profiles/create throw a TypeError.
                            // Nothing is approved before it exists, so "No".
                            ->content(fn (?PricingProfile $record): string => $record?->approved_for_publication ? 'Yes' : 'No'),
                        Placeholder::make('approved_meta')
                            ->label('Approved by / at')
                            ->content(function (?PricingProfile $record): string {
                                if (! $record?->approved_at) {
                                    return '—';
                                }
                                $who = $record->approver()->value('name') ?? ('user #'.$record->approved_by);

                                return $who.' · '.$record->approved_at->toDateTimeString();
                            }),
                        DatePicker::make('effective_date'),
                        DatePicker::make('review_date'),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
            ]);
    }
}
