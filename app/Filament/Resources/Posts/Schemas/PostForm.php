<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Cms\PageBlocks;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
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
                            ->prefix(url('/blog').'/'),
                        Textarea::make('excerpt')
                            ->rows(2)
                            ->helperText('Short teaser shown in post listings.'),
                        PageBlocks::richEditor('body')->label('Post'),
                    ])
                    ->columnSpan(2),
                Group::make([
                    Section::make('Publishing')
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label('Publish date')
                                ->helperText('Leave empty to keep as a draft. Future dates publish automatically.'),
                        ]),
                    Section::make('Cover image')
                        ->schema([
                            PageBlocks::image('cover_image', '')->directory('blog'),
                        ]),
                    Section::make('SEO')
                        ->schema([
                            Textarea::make('meta_description')->rows(3)->maxLength(255),
                        ])
                        ->collapsed(),
                ])->columnSpan(1),
            ]);
    }
}
