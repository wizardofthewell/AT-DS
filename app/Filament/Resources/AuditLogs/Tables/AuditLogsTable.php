<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['user', 'impersonator']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->dateTimeTooltip('M j, Y g:i:s a')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Who')
                    ->placeholder('Visitor / system')
                    ->description(fn (AuditLog $record): ?string => $record->impersonator ? 'via '.$record->impersonator->name : null)
                    ->searchable(),
                TextColumn::make('event')
                    ->badge()
                    ->formatStateUsing(fn (AuditLog $record): string => $record->eventLabel())
                    ->color(fn (string $state): string => static::eventColor($state)),
                TextColumn::make('auditable_label')
                    ->label('What')
                    ->description(fn (AuditLog $record): ?string => $record->subjectTypeLabel())
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('description')
                    ->state(fn (AuditLog $record): ?string => $record->description ?? static::changedFieldsSummary($record))
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(AuditLog::EVENTS)
                    ->multiple(),
                SelectFilter::make('user_id')
                    ->label('Who')
                    ->options(fn (): array => User::orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('auditable_type')
                    ->label('Type')
                    ->options(fn (): array => AuditLog::query()
                        ->whereNotNull('auditable_type')
                        ->distinct()
                        ->pluck('auditable_type')
                        ->mapWithKeys(fn (string $type): array => [$type => Str::headline(class_basename($type))])
                        ->sort()
                        ->all()),
                Filter::make('created_at')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date)))
                    ->columns(2),
            ])
            ->recordActions([
                ViewAction::make()->label('Details')->modalWidth('4xl'),
            ]);
    }

    public static function eventColor(string $event): string
    {
        return match ($event) {
            'created', 'backup_created' => 'success',
            'updated' => 'info',
            'deleted', 'backup_deleted', 'login_failed', 'backup_failed' => 'danger',
            'impersonation_started', 'impersonation_stopped', 'password_reset' => 'warning',
            default => 'gray',
        };
    }

    /**
     * E.g. "Changed title, contact › email and 2 more" for update entries without a description.
     */
    protected static function changedFieldsSummary(AuditLog $record): ?string
    {
        if ($record->event !== 'updated') {
            return null;
        }

        $fields = collect(array_keys($record->fieldChanges()))
            ->map(fn (string $field): string => Str::of($field)->replace('.', ' › ')->replace('_', ' ')->toString());

        if ($fields->isEmpty()) {
            return null;
        }

        $summary = 'Changed '.$fields->take(3)->join(', ');

        return $fields->count() > 3 ? $summary.' and '.($fields->count() - 3).' more' : $summary;
    }
}
