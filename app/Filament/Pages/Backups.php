<?php

namespace App\Filament\Pages;

use App\Support\Backups as BackupManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use UnitEnum;

/**
 * Lists, creates, downloads and deletes site backups. Super admins only.
 */
class Backups extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Backups';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function getSubheading(): ?string
    {
        return 'Each backup holds the database and every uploaded image. One is made automatically each night at 3am and kept for '
            .BackupManager::KEEP_DAYS.' days (the newest '.BackupManager::KEEP_AT_LEAST.' are always kept).';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Back up now')
                ->icon(Heroicon::OutlinedPlus)
                ->action(function (BackupManager $backups): void {
                    try {
                        $name = $backups->create();
                    } catch (Throwable $e) {
                        report($e);

                        Notification::make()->title('Backup failed')->body($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title('Backup created')->body($name)->success()->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => app(BackupManager::class)->all()->all())
            ->columns([
                TextColumn::make('name')->label('Backup')->weight('medium'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->dateTimeTooltip('M j, Y g:i a'),
                TextColumn::make('size')
                    ->formatStateUsing(fn (int $state): string => BackupManager::formatBytes($state)),
            ])
            ->recordActions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(function (array $record, BackupManager $backups): ?BinaryFileResponse {
                        if (! $backups->exists($record['name'])) {
                            Notification::make()->title('That backup no longer exists')->danger()->send();

                            return null;
                        }

                        return response()->download($backups->path($record['name']));
                    }),
                Action::make('delete')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This backup file will be permanently removed.')
                    ->action(function (array $record, BackupManager $backups): void {
                        $backups->delete($record['name']);

                        Notification::make()->title('Backup deleted')->success()->send();
                    }),
            ])
            ->emptyStateHeading('No backups yet')
            ->emptyStateDescription('Click "Back up now" to make the first one.')
            ->paginated(false);
    }
}
