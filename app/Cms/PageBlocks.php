<?php

namespace App\Cms;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class PageBlocks
{
    /**
     * All block definitions available in the page builder.
     *
     * Each block has a frontend view at resources/views/cms/blocks/{name}.blade.php.
     * Every block also gets an "Appearance" section (background + anchor) which
     * the views read through the <x-block-section> component.
     *
     * @return array<Block>
     */
    public static function all(): array
    {
        return [
            static::block('hero', 'Hero Banner', 'heroicon-o-sparkles', [
                TextInput::make('eyebrow')
                    ->helperText('Small line above the heading, e.g. "Serving Greater Victoria & Surrounding Areas".'),
                TextInput::make('heading')->required(),
                Textarea::make('subheading')->rows(2),
                static::image('image', 'Background image')
                    ->helperText('Optional. A soft, light photo works best. Without one a warm gradient is used.'),
                Grid::make(3)->schema([
                    Select::make('height')
                        ->options(['medium' => 'Medium', 'large' => 'Large', 'full' => 'Full screen'])
                        ->default('large'),
                    Select::make('align')
                        ->label('Text alignment')
                        ->options(['left' => 'Left', 'center' => 'Center'])
                        ->default('center'),
                    Select::make('overlay')
                        ->label('Image overlay')
                        ->options(['light' => 'Light (dark text)', 'dark' => 'Dark (white text)'])
                        ->default('dark'),
                ]),
                static::buttons(),
            ], heading: 'heading', withAppearance: false),

            static::block('section_heading', 'Section Heading', 'heroicon-o-h2', [
                TextInput::make('eyebrow'),
                TextInput::make('heading')->required(),
                Textarea::make('intro')->rows(2),
            ], heading: 'heading'),

            static::block('rich_text', 'Text', 'heroicon-o-bars-3-bottom-left', [
                TextInput::make('heading'),
                static::richEditor('content')->required(),
                Select::make('width')
                    ->options(['narrow' => 'Narrow (easy reading)', 'wide' => 'Wide'])
                    ->default('narrow'),
            ], heading: 'heading'),

            static::block('image_text', 'Image + Text', 'heroicon-o-photo', [
                static::image('image'),
                Grid::make(2)->schema([
                    TextInput::make('image_alt')->label('Image description (for screen readers)'),
                    Select::make('image_side')
                        ->options(['left' => 'Image on left', 'right' => 'Image on right'])
                        ->default('left'),
                ]),
                TextInput::make('eyebrow'),
                TextInput::make('heading'),
                static::richEditor('content'),
                static::buttons(),
            ], heading: 'heading'),

            static::block('service_cards', 'Service Cards', 'heroicon-o-squares-2x2', [
                TextInput::make('heading')->default('How I Can Support You'),
                Textarea::make('intro')->rows(2),
                Grid::make(2)->schema([
                    Select::make('layout')
                        ->options(['grid' => 'Grid', 'carousel' => 'Sliding carousel'])
                        ->default('grid'),
                    Select::make('which')
                        ->label('Services to show')
                        ->options(['featured' => 'Featured services only', 'all' => 'All published services', 'chosen' => 'Let me pick'])
                        ->default('featured')
                        ->live(),
                ]),
                Select::make('service_ids')
                    ->label('Services')
                    ->multiple()
                    ->options(fn () => Service::ordered()->pluck('title', 'id'))
                    ->visible(fn ($get) => $get('which') === 'chosen'),
                TextInput::make('button_label')->default('Learn More'),
            ], heading: 'heading'),

            static::block('service_spotlight', 'Service Spotlight', 'heroicon-o-heart', [
                Select::make('service_id')
                    ->label('Service')
                    ->options(fn () => Service::ordered()->pluck('title', 'id'))
                    ->required()
                    ->helperText('Shows the service\'s photo, summary and highlights, with one of its testimonials.'),
                Grid::make(3)->schema([
                    Select::make('image_side')
                        ->options(['left' => 'Image on left', 'right' => 'Image on right'])
                        ->default('right'),
                    Toggle::make('show_features')->label('Show highlights')->default(true)->inline(false),
                    Toggle::make('show_testimonial')->label('Show a testimonial')->default(true)->inline(false),
                ]),
            ], describe: fn (array $state) => Service::find($state['service_id'] ?? null)?->title),

            static::block('features', 'Icon Feature List', 'heroicon-o-check-badge', [
                TextInput::make('heading'),
                Textarea::make('intro')->rows(2),
                Select::make('columns')->options([2 => '2', 3 => '3', 4 => '4'])->default(3),
                Repeater::make('items')
                    ->schema([
                        static::iconSelect(),
                        TextInput::make('title')->required(),
                        Textarea::make('text')->rows(2),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->defaultItems(3),
            ], heading: 'heading'),

            static::block('steps', 'Process Steps', 'heroicon-o-list-bullet', [
                TextInput::make('heading')->default('How It Works'),
                Textarea::make('intro')->rows(2),
                Repeater::make('steps')
                    ->schema([
                        TextInput::make('title')->required(),
                        Textarea::make('text')->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
            ], heading: 'heading'),

            static::block('packages', 'Packages / Pricing', 'heroicon-o-currency-dollar', [
                TextInput::make('heading')->default('Packages'),
                Textarea::make('intro')->rows(2),
                Repeater::make('packages')
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('price')->placeholder('$1,200'),
                        TextInput::make('price_note')->placeholder('or 3 payments of $400'),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                        TagsInput::make('features')
                            ->placeholder('Add an item and press Enter')
                            ->columnSpanFull(),
                        TextInput::make('button_label')->default('Book a Consultation'),
                        TextInput::make('button_url')->default('/contact'),
                        Toggle::make('highlighted')->label('Highlight as most popular'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                Textarea::make('footnote')->rows(2)->helperText('Small print under the packages.'),
            ], heading: 'heading'),

            static::block('testimonial_quote', 'Single Testimonial', 'heroicon-o-chat-bubble-bottom-center-text', [
                Select::make('testimonial_id')
                    ->label('Testimonial')
                    ->options(fn () => Testimonial::ordered()->get()->mapWithKeys(fn ($t) => [$t->id => $t->author.' — '.Str::limit($t->quote, 60)]))
                    ->searchable()
                    ->helperText('Leave empty to show a random featured testimonial.'),
            ], describe: fn (array $state) => Testimonial::find($state['testimonial_id'] ?? null)?->author),

            static::block('testimonials', 'Testimonials Carousel', 'heroicon-o-chat-bubble-left-right', [
                TextInput::make('heading')->default('Kind Words From Families'),
                Grid::make(2)->schema([
                    Toggle::make('featured_only')->label('Featured testimonials only')->default(true)->inline(false),
                    TextInput::make('limit')->numeric()->default(8),
                ]),
            ], heading: 'heading'),

            static::block('faq', 'FAQ', 'heroicon-o-question-mark-circle', [
                TextInput::make('heading')->default('Frequently Asked Questions'),
                Textarea::make('intro')->rows(2),
                Select::make('category')
                    ->options(fn () => Faq::query()->whereNotNull('category')->distinct()->pluck('category', 'category'))
                    ->placeholder('All categories'),
            ], heading: 'heading'),

            static::block('gallery', 'Photo Gallery', 'heroicon-o-rectangle-group', [
                TextInput::make('heading'),
                FileUpload::make('images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('gallery')
                    ->maxSize(10240),
                Select::make('columns')->options([2 => '2', 3 => '3', 4 => '4'])->default(3),
            ], heading: 'heading'),

            static::block('cta_banner', 'Call to Action Banner', 'heroicon-o-megaphone', [
                TextInput::make('heading')->required(),
                Textarea::make('text')->rows(2),
                static::image('image', 'Background image (optional)'),
                static::buttons(),
            ], heading: 'heading', withAppearance: false),

            static::block('contact_form', 'Contact Form', 'heroicon-o-envelope', [
                TextInput::make('heading')->default('Let\'s Connect'),
                Textarea::make('intro')->rows(2),
                Grid::make(2)->schema([
                    Toggle::make('show_details')->label('Show contact details beside the form')->default(true)->inline(false),
                    Toggle::make('ask_due_date')->label("Ask for due date / baby's birthday")->default(true)->inline(false),
                ]),
            ], heading: 'heading'),

            static::block('latest_posts', 'Latest Blog Posts', 'heroicon-o-newspaper', [
                TextInput::make('heading')->default('From the Blog'),
                TextInput::make('limit')->numeric()->default(3),
            ], heading: 'heading'),

            static::block('divider', 'Divider / Spacer', 'heroicon-o-minus', [
                Select::make('style')
                    ->options(['ornament' => 'Decorative leaf', 'line' => 'Thin line', 'space' => 'Empty space'])
                    ->default('ornament'),
            ], describe: fn (array $state) => $state['style'] ?? null),

            static::block('custom_html', 'Custom HTML / Embed', 'heroicon-o-code-bracket', [
                Textarea::make('html')
                    ->rows(10)
                    ->required()
                    ->helperText('Raw HTML rendered as-is — useful for booking widgets, maps or video embeds.'),
            ]),
        ];
    }

    /**
     * Icons available to editors. Keys are heroicon names (outline set).
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'heart' => 'Heart',
            'sparkles' => 'Sparkles',
            'sun' => 'Sun',
            'moon' => 'Moon',
            'home' => 'Home',
            'hand-raised' => 'Hand',
            'face-smile' => 'Smile',
            'users' => 'People',
            'user-group' => 'Family',
            'chat-bubble-left-right' => 'Conversation',
            'phone' => 'Phone',
            'calendar-days' => 'Calendar',
            'clock' => 'Clock',
            'book-open' => 'Book',
            'academic-cap' => 'Education',
            'light-bulb' => 'Idea',
            'shield-check' => 'Safety',
            'gift' => 'Gift',
            'star' => 'Star',
            'map-pin' => 'Location',
            'video-camera' => 'Virtual',
            'check-circle' => 'Check',
        ];
    }

    public static function iconSelect(string $name = 'icon'): Select
    {
        return Select::make($name)
            ->options(static::icons())
            ->default('heart')
            ->searchable();
    }

    public static function richEditor(string $name): RichEditor
    {
        return RichEditor::make($name)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'link'],
                ['h2', 'h3', 'blockquote'],
                ['bulletList', 'orderedList'],
                ['attachFiles'],
                ['undo', 'redo'],
            ])
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('content')
            ->fileAttachmentsVisibility('public');
    }

    public static function image(string $name, ?string $label = null): FileUpload
    {
        return FileUpload::make($name)
            ->label($label ?? Str::headline($name))
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory('cms')
            ->maxSize(10240);
    }

    public static function buttons(): Repeater
    {
        return Repeater::make('buttons')
            ->schema([
                TextInput::make('label')->required(),
                TextInput::make('url')->required()->placeholder('/contact or https://...'),
                Select::make('style')
                    ->options(['primary' => 'Filled', 'outline' => 'Outline'])
                    ->default('primary'),
            ])
            ->columns(3)
            ->maxItems(2)
            ->defaultItems(0)
            ->addActionLabel('Add button');
    }

    /**
     * Build a block with a sensible label and the shared appearance options.
     *
     * @param  array<int, mixed>  $schema
     */
    protected static function block(
        string $name,
        string $label,
        string $icon,
        array $schema,
        ?string $heading = null,
        bool $withAppearance = true,
        ?\Closure $describe = null,
    ): Block {
        $describe ??= ($heading ? fn (array $state) => $state[$heading] ?? null : null);

        if ($withAppearance) {
            $schema[] = Section::make('Appearance')
                ->schema([
                    Select::make('background')
                        ->options([
                            'default' => 'Page background',
                            'white' => 'White',
                            'soft' => 'Soft tint',
                            'accent' => 'Accent tint',
                            'dark' => 'Dark',
                        ])
                        ->default('default'),
                    TextInput::make('anchor')
                        ->label('Anchor ID')
                        ->helperText('Optional. Lets links jump here, e.g. "about" for /#about.'),
                ])
                ->columns(2)
                ->collapsed()
                ->compact();
        }

        return Block::make($name)
            ->label(function (?array $state) use ($label, $describe): string {
                $title = $state && $describe ? strip_tags((string) $describe($state)) : null;

                return filled($title) ? $label.': '.Str::limit($title, 50) : $label;
            })
            ->icon($icon)
            ->schema($schema);
    }
}
