<?php

namespace App\Filament\Pages;

use App\Filament\Support\Uploads;
use App\Models\CompanySetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Single-row Company Settings page (not a resource -- there is only ever one
 * record). Centralizes company facts that would otherwise be scattered
 * across translations/env: public info, socials, defaults, and the two
 * operational-but-non-secret settings (lead notification recipients,
 * analytics provider/site id). Real secrets never live here.
 *
 * @property-read Schema $form resolved by Filament\Schemas\Concerns\InteractsWithSchemas::__get via the form() method below
 */
class CompanySettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Company Settings';

    // Without this Filament derives the slug from the class name and
    // serves this at /cms/company-settings-page -- the "Page" suffix is
    // an implementation detail and does not belong in a URL.
    protected static ?string $slug = 'company-settings';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.company-settings-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(CompanySetting::current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Company')
                    ->columns(2)
                    ->components([
                        TextInput::make('company_name')->columnSpanFull(),
                        TextInput::make('tagline')->columnSpanFull(),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        TextInput::make('email')->email(),
                        TextInput::make('phone'),
                        TextInput::make('whatsapp'),
                        TextInput::make('address'),
                        TextInput::make('booking_url')->url(),
                    ]),
                Section::make('Social & Branding')
                    ->columns(2)
                    ->components([
                        KeyValue::make('social_links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->columnSpanFull(),
                        Uploads::image('default_og_image')
                            ->image()
                            ->directory('company'),
                        Textarea::make('footer_note')->rows(2),
                    ]),
                Section::make('Operational (not exposed publicly)')
                    ->description('Internal only -- never returned by the public API.')
                    ->columns(2)
                    ->components([
                        Textarea::make('lead_recipients')
                            ->label('Lead notification recipients')
                            ->helperText('Comma-separated internal email addresses. Empty disables lead notifications.')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('analytics_provider')
                            ->helperText('plausible, umami, or blank to disable analytics.'),
                        TextInput::make('analytics_site_id'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->action(function () {
                    $data = $this->form->getState();
                    CompanySetting::current()->update($data);
                    Notification::make()->title('Settings saved')->success()->send();
                }),
        ];
    }
}
