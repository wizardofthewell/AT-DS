<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label('Address')
                    ->formatStateUsing(fn (string $state): string => $state === 'home' ? '/' : '/'.$state)
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('show_in_nav')
                    ->label('In menu')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Last edited')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('nav_order')
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => $record->url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['title'] .= ' (copy)';
                        $data['slug'] .= '-copy-'.Str::lower(Str::random(4));
                        $data['is_published'] = false;
                        $data['show_in_nav'] = false;

                        return $data;
                    }),
                DeleteAction::make(),
            ]);
    }
}
