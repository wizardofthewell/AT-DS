<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Cms\PageBlocks;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->schema([
                        Textarea::make('quote')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('author')
                            ->required()
                            ->placeholder('Megan G.'),
                        TextInput::make('context')
                            ->placeholder('First-time parents, birth doula client'),
                        Select::make('service_id')
                            ->label('Related service')
                            ->relationship('service', 'title')
                            ->helperText('Lets this quote appear in that service\'s spotlight and page.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
                Section::make()
                    ->schema([
                        Toggle::make('is_published')->label('Published')->default(true),
                        Toggle::make('is_featured')->label('Featured')->default(true)
                            ->helperText('Featured quotes appear in the testimonials carousel.'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        PageBlocks::image('photo', 'Photo (optional)')->directory('testimonials')->avatar(),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
