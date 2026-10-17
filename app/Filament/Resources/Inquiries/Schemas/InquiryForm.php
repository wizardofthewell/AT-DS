<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Message')
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('phone')->tel(),
                        DatePicker::make('due_date')->label("Due date / baby's birthday"),
                        TextInput::make('service_interest')->label('Interested in')->columnSpanFull(),
                        Textarea::make('message')->rows(6)->required()->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
                Section::make('Follow-up')
                    ->schema([
                        Select::make('status')
                            ->options(Inquiry::STATUSES)
                            ->default('new')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Private notes')
                            ->rows(6),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
