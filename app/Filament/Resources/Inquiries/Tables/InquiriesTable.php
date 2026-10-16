<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight(fn (Inquiry $record): string => $record->status === 'new' ? 'bold' : 'normal')
                    ->description(fn (Inquiry $record): string => $record->email),
                TextColumn::make('service_interest')->label('Interested in')->toggleable(),
                TextColumn::make('due_date')->date('M j, Y')->sortable()->toggleable(),
                TextColumn::make('message')->limit(60)->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'contacted' => 'warning',
                        'booked' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Inquiry::STATUSES),
            ])
            ->recordActions([
                Action::make('reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->url(fn (Inquiry $record): string => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: your inquiry')),
                EditAction::make()->label('Open'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
