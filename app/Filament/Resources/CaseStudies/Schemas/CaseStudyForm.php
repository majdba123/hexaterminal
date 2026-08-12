<?php

namespace App\Filament\Resources\CaseStudies\Schemas;

use App\Filament\Support\PublishingSection;
use App\Filament\Support\Slugs;
use App\Filament\Support\Uploads;
use App\Models\CaseStudy;
use App\Models\Industry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CaseStudyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Story')
                    ->description('context -> problem -> constraints -> solution -> architecture -> outcomes -> evidence')
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
                        Textarea::make('summary')->rows(2)->columnSpanFull(),
                        Textarea::make('context')->rows(3),
                        Textarea::make('problem')->rows(3),
                        Textarea::make('constraints')->rows(3),
                        Textarea::make('solution')->rows(3),
                        Textarea::make('architecture')->rows(3),
                        Textarea::make('outcomes')->rows(3),
                        Textarea::make('evidence')
                            ->rows(3)
                            ->helperText('Concrete, verifiable proof only -- no fabricated metrics.'),
                        Textarea::make('features')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Details')
                    ->columns(2)
                    ->components([
                        TextInput::make('client_name'),
                        TextInput::make('project_url')->url(),
                        TextInput::make('video_url')->url(),
                        Select::make('project_classification')
                            ->label('Project classification')
                            ->options(CaseStudy::CLASSIFICATION_OPTIONS)
                            ->in(CaseStudy::CLASSIFICATIONS)
                            ->nullable(),
                        Select::make('service_offering_id')
                            ->relationship('serviceOffering', 'slug')
                            ->searchable()
                            ->preload(),
                        Select::make('system_id')
                            ->relationship('system', 'slug')
                            ->searchable()
                            ->preload(),
                        Select::make('industries')
                            ->relationship('industries', 'slug')
                            ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->name)
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Media')
                    ->columns(2)
                    ->components([
                        Uploads::image('cover_image')->directory('case-studies'),
                        Uploads::altText('cover_image_alt', 'cover image'),
                        Uploads::image('gallery')->multiple()->directory('case-studies/gallery'),
                    ]),
                Section::make('Visibility')
                    ->columns(2)
                    ->components([
                        Toggle::make('is_featured'),
                    ]),
                PublishingSection::make(),
            ]);
    }
}
