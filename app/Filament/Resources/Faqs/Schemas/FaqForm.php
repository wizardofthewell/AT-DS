<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Cms\PageBlocks;
use App\Models\Faq;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->columnSpanFull(),
                        PageBlocks::richEditor('answer')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('category')
                            ->placeholder('General')
                            ->datalist(fn () => Faq::query()->whereNotNull('category')->distinct()->pluck('category')->all())
                            ->helperText('Optional. FAQ sections can be filtered by category.'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_published')->label('Published')->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
