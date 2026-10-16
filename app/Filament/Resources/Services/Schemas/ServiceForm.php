<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Cms\PageBlocks;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Service')
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
                                ->prefix(url('/services').'/'),
                            TextInput::make('tagline')
                                ->placeholder('In-person support across Greater Victoria')
                                ->helperText('Short line shown on the service card.')
                                ->columnSpanFull(),
                            Textarea::make('summary')
                                ->rows(3)
                                ->helperText('One or two sentences used on cards and spotlights.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Full description')
                        ->description('Shown on this service\'s own page.')
                        ->schema([
                            PageBlocks::richEditor('description')->label(''),
                            Repeater::make('features')
                                ->label('Highlights / what\'s included')
                                ->simple(TextInput::make('item')->required())
                                ->addActionLabel('Add highlight')
                                ->reorderable(),
                        ]),
                ])->columnSpan(2),
                Group::make([
                    Section::make('Visibility')
                        ->schema([
                            Toggle::make('is_published')->label('Published')->default(true),
                            Toggle::make('is_featured')
                                ->label('Featured')
                                ->helperText('Featured services appear in the homepage service cards.')
                                ->default(true),
                            TextInput::make('sort_order')->numeric()->default(0),
                        ]),
                    Section::make('Image & icon')
                        ->schema([
                            PageBlocks::image('image')->directory('services'),
                            PageBlocks::iconSelect()
                                ->helperText('Used when there is no image.'),
                        ]),
                    Section::make('Pricing & button')
                        ->schema([
                            TextInput::make('price_label')
                                ->label('Price')
                                ->placeholder('Starting at $950'),
                            TextInput::make('button_label')->placeholder('Book a Free Consult'),
                            TextInput::make('button_url')->placeholder('/contact'),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
