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
                Section::make('Business story')
                    ->description('Describe the business context, challenge, solution, delivered capabilities, and only outcomes that can be stated responsibly.')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label('Case study title')
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
                        Textarea::make('summary')
                            ->label('Short overview')
                            ->helperText('A concise description of the business scenario or project.')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('context')
                            ->label('Business and process context')
                            ->helperText('Describe the organization, workflow, users, or operational setting. Do not add unverified client claims.')
                            ->rows(3),
                        Textarea::make('problem')
                            ->label('Business challenge')
                            ->helperText('Explain the operational problem, gap, or friction that needed to be addressed.')
                            ->rows(3),
                        Textarea::make('constraints')
                            ->label('Operational constraints')
                            ->helperText('For example: roles, permissions, rollout needs, integrations, compliance, or field conditions.')
                            ->rows(3),
                        Textarea::make('solution')
                            ->label('Solution approach')
                            ->helperText('Explain the workflow, system, platform, or product approach in business terms.')
                            ->rows(3),
                        Textarea::make('architecture')
                            ->label('System and workflow structure')
                            ->helperText('Describe modules, roles, connected workflows, and integrations. Avoid a technology-stack dump.')
                            ->rows(3),
                        Textarea::make('features')
                            ->label('Delivered capabilities, workflows, or modules')
                            ->helperText('Enter one capability per line so the public page can present it clearly.')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('outcomes')
                            ->label('Qualitative outcome')
                            ->helperText('State only outcomes that are appropriate to publish. Never invent metrics, revenue, or performance results.')
                            ->rows(3),
                        Textarea::make('evidence')
                            ->rows(3)
                            ->helperText('Concrete, verifiable proof only -- no fabricated metrics.'),
                    ]),
                Section::make('Classification and context')
                    ->description('Use existing relationships to place the case study in the right service, system, and industry context.')
                    ->columns(2)
                    ->components([
                        Select::make('project_classification')
                            ->label('Project classification')
                            ->helperText('Choose the primary business track when it is known.')
                            ->options(CaseStudy::CLASSIFICATION_OPTIONS)
                            ->in(CaseStudy::CLASSIFICATIONS)
                            ->nullable(),
                        Select::make('service_offering_id')
                            ->label('Related service offering')
                            ->helperText('Select only the service offering this work genuinely represents.')
                            ->relationship('serviceOffering', 'slug')
                            ->searchable()
                            ->preload(),
                        Select::make('system_id')
                            ->label('Related system (optional)')
                            ->helperText('Use only when the case study is directly tied to a system record.')
                            ->relationship('system', 'slug')
                            ->searchable()
                            ->preload(),
                        Select::make('industries')
                            ->label('Relevant industries')
                            ->helperText('Select the industries that add real business context.')
                            ->relationship('industries', 'slug')
                            ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->name)
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        TextInput::make('client_name')
                            ->label('Client name (only with approval)')
                            ->helperText('Leave blank for conceptual work or where public naming is not approved.'),
                        TextInput::make('project_url')
                            ->label('Public project URL (optional)')
                            ->url(),
                        TextInput::make('video_url')
                            ->label('Public video URL (optional)')
                            ->url(),
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
