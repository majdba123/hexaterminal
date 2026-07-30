<?php

namespace App\Filament\Resources\PublicClaims\Schemas;

use App\Models\PublicClaim;
use App\Models\TeamMember;
use App\Models\TrustPage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * `claimable_type` is edited as a short alias (trust_page / team_member)
 * and mapped to/from the real morph class in
 * PublicClaimResource::mutateFormDataBeforeCreate/Fill, keeping the admin
 * UI decoupled from fully-qualified class names.
 */
class PublicClaimForm
{
    public const CLAIMABLE_TYPES = [
        'trust_page' => TrustPage::class,
        'team_member' => TeamMember::class,
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Claim')
                    ->columns(2)
                    ->components([
                        Select::make('category')
                            ->options(array_combine(
                                PublicClaim::CATEGORIES,
                                array_map(fn (string $c) => Str::headline($c), PublicClaim::CATEGORIES),
                            ))
                            ->required(),
                        Select::make('locale')
                            ->options(['en' => 'English', 'ar' => 'Arabic'])
                            ->default('en')
                            ->required(),
                        Textarea::make('claim_text')->rows(2)->required()->columnSpanFull(),
                        Textarea::make('evidence')->rows(2)->columnSpanFull()
                            ->helperText('Source/evidence for this claim. Required before it can be verified.'),
                    ]),
                Section::make('Attached to')
                    ->columns(2)
                    ->components([
                        Select::make('claimable_type_alias')
                            ->label('Entity type')
                            ->options(['trust_page' => 'Trust Page', 'team_member' => 'Team Member'])
                            ->live()
                            ->dehydrated(false)
                            ->required(),
                        Select::make('claimable_id')
                            ->label('Entity')
                            ->options(function (callable $get) {
                                $alias = $get('claimable_type_alias');
                                $class = self::CLAIMABLE_TYPES[$alias] ?? null;

                                return $class ? $class::query()->pluck('slug', 'id') : [];
                            })
                            ->searchable()
                            ->required(),
                    ]),
                Section::make('Governance')
                    ->columns(2)
                    ->components([
                        Select::make('verification_status')
                            ->options(array_combine(PublicClaim::VERIFICATION_STATUSES, array_map(
                                fn (string $s) => Str::headline($s),
                                PublicClaim::VERIFICATION_STATUSES,
                            )))
                            ->default('unverified')
                            ->required(),
                        Toggle::make('confidential'),
                        Toggle::make('approved_for_publication')
                            ->helperText('Publication also requires verification_status = Verified and confidential = false.'),
                        Select::make('review_owner_id')
                            ->relationship('reviewOwner', 'name')
                            ->searchable(),
                        DateTimePicker::make('expires_at'),
                        DateTimePicker::make('next_review_at'),
                        Textarea::make('internal_notes')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
