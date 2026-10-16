<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Cms\PageBlocks;
use App\Models\Page;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\NotIn;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Content')
                            ->icon('heroicon-o-squares-plus')
                            ->schema([
                                Builder::make('blocks')
                                    ->label('Page sections')
                                    ->helperText('Add sections, then drag them into order. Click a section to open or close it.')
                                    ->blocks(PageBlocks::all())
                                    ->blockPickerColumns(3)
                                    ->blockPickerWidth('4xl')
                                    ->reorderableWithDragAndDrop()
                                    ->reorderableWithButtons()
                                    ->cloneable()
                                    ->blockNumbers(false)
                                    ->collapsible()
                                    ->collapsed()
                                    ->addActionLabel('Add a section'),
                            ]),
                        Tab::make('Page settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old) {
                                                if (($get('slug') ?? '') === Str::slug($old ?? '')) {
                                                    $set('slug', Str::slug($state ?? ''));
                                                }
                                            }),
                                        TextInput::make('slug')
                                            ->required()
                                            ->alphaDash()
                                            ->unique(ignoreRecord: true)
                                            ->rules([new NotIn(Page::RESERVED_SLUGS)])
                                            ->prefix(url('/').'/')
                                            ->helperText('Use "home" for the homepage.')
                                            ->validationMessages([
                                                'not_in' => 'This address is reserved by the website.',
                                            ]),
                                        Toggle::make('is_published')
                                            ->label('Published')
                                            ->default(true)
                                            ->helperText('Unpublished pages are hidden from visitors.'),
                                    ])
                                    ->columns(2),
                                Section::make('Menu')
                                    ->schema([
                                        Toggle::make('show_in_nav')
                                            ->label('Show in the main menu')
                                            ->live(),
                                        TextInput::make('nav_label')
                                            ->label('Menu label')
                                            ->placeholder(fn (Get $get): string => $get('title') ?? 'Uses the page title')
                                            ->visible(fn (Get $get): bool => (bool) $get('show_in_nav')),
                                        TextInput::make('nav_order')
                                            ->label('Menu position')
                                            ->numeric()
                                            ->default(0)
                                            ->visible(fn (Get $get): bool => (bool) $get('show_in_nav')),
                                    ])
                                    ->columns(3),
                                Section::make('Search engines (SEO)')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->helperText('Overrides the browser tab title.'),
                                        Textarea::make('meta_description')
                                            ->rows(2)
                                            ->maxLength(300)
                                            ->helperText('The short summary Google shows under your link.'),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
