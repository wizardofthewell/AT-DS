<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Filament\Resources\AuditLogs\Tables\AuditLogsTable;
use App\Models\AuditLog;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')->label('When')->dateTime('M j, Y g:i:s a'),
                        TextEntry::make('user.name')->label('Who')->placeholder('Visitor / system'),
                        TextEntry::make('event')
                            ->badge()
                            ->formatStateUsing(fn (AuditLog $record): string => $record->eventLabel())
                            ->color(fn (string $state): string => AuditLogsTable::eventColor($state)),
                        TextEntry::make('auditable_label')
                            ->label('What')
                            ->placeholder('—')
                            ->helperText(fn (AuditLog $record): ?string => $record->subjectTypeLabel() ? $record->subjectTypeLabel().' #'.$record->auditable_id : null),
                        TextEntry::make('description')->placeholder('—'),
                        TextEntry::make('impersonator.name')
                            ->label('Done while logged in as them by')
                            ->visible(fn (AuditLog $record): bool => filled($record->impersonator_id)),
                        TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                        TextEntry::make('user_agent')->label('Browser')->placeholder('—')->columnSpan(2),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                RepeatableEntry::make('changes')
                    ->state(fn (AuditLog $record): array => collect($record->fieldChanges())
                        ->map(fn (array $values, string $field): array => [
                            'field' => Str::of($field)->replace('.', ' › ')->replace('_', ' ')->ucfirst()->toString(),
                            'old' => static::display($values['old']),
                            'new' => static::display($values['new']),
                        ])
                        ->values()
                        ->all())
                    ->table([
                        TableColumn::make('Field'),
                        TableColumn::make('Before'),
                        TableColumn::make('After'),
                    ])
                    ->schema([
                        TextEntry::make('field')->weight('medium'),
                        TextEntry::make('old'),
                        TextEntry::make('new'),
                    ])
                    ->visible(fn (AuditLog $record): bool => filled($record->fieldChanges()))
                    ->columnSpanFull(),
            ]);
    }

    protected static function display(mixed $value): string
    {
        return match (true) {
            $value === null || $value === '' => '—',
            is_bool($value) => $value ? 'Yes' : 'No',
            default => Str::limit((string) $value, 500),
        };
    }
}
