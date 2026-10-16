<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\Theme;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageSiteSettings extends Page
{
    /**
     * Setting groups stored as one row each in site_settings.
     */
    public const GROUPS = ['branding', 'theme', 'topbar', 'navigation', 'contact', 'social', 'footer', 'seo'];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(static::GROUPS)
            ->mapWithKeys(fn (string $group) => [$group => SiteSetting::get($group, [])])
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Branding')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('branding.site_name')
                                            ->label('Business name')
                                            ->placeholder('Aubri Porter'),
                                        TextInput::make('branding.tagline')
                                            ->placeholder('Birth & Postpartum Doula')
                                            ->helperText('Shown under the name when there is no logo.'),
                                        FileUpload::make('branding.logo')
                                            ->label('Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->helperText('Optional. A transparent PNG or SVG works best. Without a logo, your name is shown in the heading font.'),
                                        TextInput::make('branding.logo_height')
                                            ->label('Logo height (px)')
                                            ->numeric()
                                            ->placeholder('64'),
                                        FileUpload::make('branding.favicon')
                                            ->label('Browser tab icon (favicon)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->helperText('A square image, at least 64×64.'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Colours & Fonts')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Section::make('Quick start')
                                    ->description('Pick a preset to fill in all colours at once, then fine-tune below.')
                                    ->schema([
                                        Select::make('theme.preset')
                                            ->label('Colour preset')
                                            ->options(collect(Theme::PRESETS)->map(fn ($p) => $p['label']))
                                            ->live()
                                            ->afterStateUpdated(function (?string $state, Set $set) {
                                                foreach (Theme::PRESETS[$state]['colors'] ?? [] as $key => $value) {
                                                    $set("theme.$key", $value);
                                                }
                                            }),
                                    ]),
                                Section::make('Colours')
                                    ->schema([
                                        ColorPicker::make('theme.primary')
                                            ->label('Primary')
                                            ->placeholder(Theme::DEFAULTS['primary'])
                                            ->helperText('Buttons, links and highlights.'),
                                        ColorPicker::make('theme.accent')
                                            ->label('Accent')
                                            ->placeholder(Theme::DEFAULTS['accent'])
                                            ->helperText('Secondary highlights and decorative touches.'),
                                        ColorPicker::make('theme.background')
                                            ->label('Page background')
                                            ->placeholder(Theme::DEFAULTS['background']),
                                        ColorPicker::make('theme.text')
                                            ->label('Text')
                                            ->placeholder(Theme::DEFAULTS['text']),
                                        ColorPicker::make('theme.dark')
                                            ->label('Dark sections & footer')
                                            ->placeholder(Theme::DEFAULTS['dark']),
                                    ])
                                    ->columns(3),
                                Section::make('Fonts')
                                    ->schema([
                                        Select::make('theme.heading_font')
                                            ->options(array_combine(Theme::HEADING_FONTS, Theme::HEADING_FONTS))
                                            ->placeholder(Theme::DEFAULTS['heading_font']),
                                        Select::make('theme.body_font')
                                            ->options(array_combine(Theme::BODY_FONTS, Theme::BODY_FONTS))
                                            ->placeholder(Theme::DEFAULTS['body_font']),
                                        Select::make('theme.button_shape')
                                            ->options(['pill' => 'Rounded pill', 'soft' => 'Softly rounded', 'square' => 'Square'])
                                            ->placeholder('Rounded pill'),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make('Header & Menu')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                Section::make('Announcement bar')
                                    ->description('The thin bar along the very top of every page.')
                                    ->schema([
                                        Toggle::make('topbar.enabled')
                                            ->label('Show announcement bar')
                                            ->default(true),
                                        TextInput::make('topbar.text')
                                            ->placeholder('Serving Greater Victoria & Southern Vancouver Island'),
                                        TextInput::make('topbar.link_label')->label('Link label')->placeholder('Book a free consult'),
                                        TextInput::make('topbar.link_url')->label('Link URL')->placeholder('/contact'),
                                    ])
                                    ->columns(2),
                                Section::make('Main menu')
                                    ->description('Links in the site header. Leave empty to automatically list pages marked "Show in the main menu".')
                                    ->schema([
                                        Repeater::make('navigation.items')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextInput::make('label')->required(),
                                                TextInput::make('url')
                                                    ->label('Link')
                                                    ->required()
                                                    ->placeholder('/about or /services or https://...'),
                                                Toggle::make('external')->label('Open in new tab')->inline(false),
                                                Repeater::make('children')
                                                    ->label('Dropdown links (optional)')
                                                    ->schema([
                                                        TextInput::make('label')->required(),
                                                        TextInput::make('url')->label('Link')->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(0)
                                                    ->collapsible()
                                                    ->addActionLabel('Add dropdown link')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->reorderable()
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                            ->addActionLabel('Add menu link')
                                            ->defaultItems(0),
                                        Toggle::make('navigation.show_cta')
                                            ->label('Show a highlighted button at the end of the menu')
                                            ->default(true),
                                        TextInput::make('navigation.cta_label')->label('Button label')->placeholder('Book a Consult'),
                                        TextInput::make('navigation.cta_url')->label('Button link')->placeholder('/contact'),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make('Contact')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('contact.email')->email()->label('Public email'),
                                        TextInput::make('contact.phone')->tel(),
                                        TextInput::make('contact.service_area')
                                            ->label('Service area')
                                            ->placeholder('Victoria, Saanich, Langford & surrounding areas'),
                                        TextInput::make('contact.address')
                                            ->label('Mailing address (optional)'),
                                        TextInput::make('contact.hours')
                                            ->label('Availability')
                                            ->placeholder('On call 24/7 for clients · Inquiries answered within 1 business day'),
                                        TextInput::make('contact.booking_url')
                                            ->label('Online booking link (optional)')
                                            ->url()
                                            ->placeholder('https://calendly.com/...'),
                                        TextInput::make('contact.notify_email')
                                            ->label('Send new inquiries to')
                                            ->email()
                                            ->helperText('Where contact-form notifications are emailed. Defaults to the public email.'),
                                        Textarea::make('contact.thank_you')
                                            ->label('Message shown after someone sends the form')
                                            ->rows(2)
                                            ->placeholder('Thank you for reaching out! I\'ll be in touch within one business day.'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Social')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('social.instagram')->url()->prefixIcon('heroicon-o-link'),
                                        TextInput::make('social.facebook')->url()->prefixIcon('heroicon-o-link'),
                                        TextInput::make('social.tiktok')->label('TikTok')->url()->prefixIcon('heroicon-o-link'),
                                        TextInput::make('social.pinterest')->url()->prefixIcon('heroicon-o-link'),
                                        TextInput::make('social.youtube')->label('YouTube')->url()->prefixIcon('heroicon-o-link'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Footer')
                            ->icon('heroicon-o-queue-list')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Textarea::make('footer.about')
                                            ->label('Short blurb')
                                            ->rows(3),
                                        Textarea::make('footer.acknowledgement')
                                            ->label('Land acknowledgement or extra note (optional)')
                                            ->rows(3),
                                        TextInput::make('footer.copyright')
                                            ->placeholder('© {year} Aubri Porter Doula Services')
                                            ->helperText('{year} becomes the current year.'),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Textarea::make('seo.description')
                                            ->label('Default description')
                                            ->rows(2)
                                            ->maxLength(300)
                                            ->helperText('Used by Google when a page has no description of its own.'),
                                        FileUpload::make('seo.share_image')
                                            ->label('Social share image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->helperText('Shown when the site is shared on Facebook etc. 1200×630 is ideal.'),
                                        TextInput::make('seo.google_analytics_id')
                                            ->label('Google Analytics ID (optional)')
                                            ->placeholder('G-XXXXXXXXXX'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save settings')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (static::GROUPS as $group) {
            SiteSetting::set($group, $data[$group] ?? []);
        }

        Notification::make()
            ->title('Settings saved')
            ->body('Your changes are live on the website.')
            ->success()
            ->send();
    }
}
