<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest inquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Inquiry::query()->latest()->limit(5))
            ->paginated(false)
            ->emptyStateHeading('No inquiries yet')
            ->emptyStateDescription('Messages from the website contact form will show up here.')
            ->columns([
                TextColumn::make('name')->weight('medium')->description(fn (Inquiry $r) => $r->email),
                TextColumn::make('service_interest')->label('Interested in'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'contacted' => 'warning',
                        'booked' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordActions([
                Action::make('open')
                    ->url(fn (Inquiry $record): string => InquiryResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
