<?php

namespace App\Filament\Pages;

use App\Support\Audit;
use App\Support\Backups as BackupManager;
use App\Support\Site;
use BackedEnum;
use Composer\InstalledVersions;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use UnitEnum;

/**
 * Site health at a glance plus maintenance tools. Super admins only.
 */
class SystemHealth extends Page
{
    /**
     * How much of the end of the log file to show.
     */
    public const LOG_TAIL_BYTES = 40_000;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'System';

    protected static ?string $slug = 'system';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('Clear cache')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Clears the application cache, compiled views and cached config/routes. Safe to do any time; the site may be a touch slower for the next visitor.')
                ->action(function (): void {
                    Artisan::call('optimize:clear');

                    if (app()->isProduction()) {
                        Artisan::call('optimize');
                    }

                    Audit::record('system', description: 'Cleared cache');

                    Notification::make()->title('Cache cleared')->success()->send();
                }),
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->action(function (): void {
                    $email = auth()->user()->email;

                    try {
                        Mail::raw('This is a test email from '.config('app.name').' ('.config('app.url').'). If you are reading this, email is working.', function ($message) use ($email): void {
                            $message->to($email)->subject('Test email from '.config('app.name'));
                        });
                    } catch (Throwable $e) {
                        report($e);

                        Notification::make()->title('Email failed')->body($e->getMessage())->danger()->persistent()->send();

                        return;
                    }

                    Notification::make()
                        ->title('Test email sent to '.$email)
                        ->body(config('mail.default') === 'log' ? 'Mail is set to "log", so it was written to the log file instead of sent.' : null)
                        ->success()
                        ->send();
                }),
            Action::make('downloadLog')
                ->label('Download log')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => is_file(static::logPath()))
                ->action(fn (): BinaryFileResponse => response()->download(static::logPath(), 'laravel-'.now()->format('Y-m-d').'.log')),
            Action::make('clearLog')
                ->label('Clear log')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn (): bool => is_file(static::logPath()))
                ->requiresConfirmation()
                ->modalDescription('Empties the error log file. Download it first if you might need it.')
                ->action(function (): void {
                    File::put(static::logPath(), '');

                    Audit::record('system', description: 'Cleared error log');

                    Notification::make()->title('Log cleared')->success()->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Health')
                ->schema([
                    TextEntry::make('site_status')
                        ->label('Website')
                        ->state(fn (): string => app(Site::class)->isLive() ? 'Live' : 'Coming soon page')
                        ->badge()
                        ->color(fn (string $state): string => $state === 'Live' ? 'success' : 'warning'),
                    TextEntry::make('environment')
                        ->state(fn (): string => app()->environment().(config('app.debug') ? ' · debug on' : ''))
                        ->badge()
                        ->color(fn (): string => app()->isProduction() && config('app.debug') ? 'danger' : 'gray')
                        ->helperText(fn (): ?string => app()->isProduction() && config('app.debug') ? 'Turn APP_DEBUG off in production: errors can show secrets.' : null),
                    TextEntry::make('last_backup')
                        ->state(fn (): string => static::lastBackupSummary())
                        ->badge()
                        ->color(fn (): string => static::lastBackupIsRecent() ? 'success' : 'danger')
                        ->url(Backups::getUrl()),
                    TextEntry::make('scheduler')
                        ->label('Scheduler (nightly backups)')
                        ->state(fn (): string => static::schedulerSummary())
                        ->badge()
                        ->color(fn (): string => static::schedulerIsRunning() ? 'success' : 'danger')
                        ->helperText(fn (): ?string => static::schedulerIsRunning() ? null : 'Add the cron entry: * * * * * php artisan schedule:run'),
                    TextEntry::make('failed_jobs')
                        ->label('Failed background jobs')
                        ->state(fn (): int => DatabaseSchema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0)
                        ->badge()
                        ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success')
                        ->helperText(fn (int $state): ?string => $state > 0 ? 'Retry with: php artisan queue:retry all' : null),
                    TextEntry::make('mail')
                        ->state(fn (): string => config('mail.default').' · from '.config('mail.from.address'))
                        ->badge()
                        ->color(fn (): string => in_array(config('mail.default'), ['log', 'array'], true) ? 'warning' : 'gray')
                        ->helperText(fn (): ?string => in_array(config('mail.default'), ['log', 'array'], true) ? 'Emails (like inquiry alerts) are not actually being sent.' : null),
                ])
                ->columns(3),
            Section::make('Server')
                ->schema([
                    TextEntry::make('php')->label('PHP')->state(PHP_VERSION),
                    TextEntry::make('laravel')->state(fn (): string => app()->version()),
                    TextEntry::make('filament')->state(fn (): string => InstalledVersions::getPrettyVersion('filament/filament') ?? 'unknown'),
                    TextEntry::make('database')->state(fn (): string => static::databaseSummary()),
                    TextEntry::make('uploads')->label('Uploaded files')->state(fn (): string => static::uploadsSummary()),
                    TextEntry::make('disk_free')->label('Free disk space')->state(fn (): string => static::freeDiskSpace()),
                    TextEntry::make('drivers')->label('Cache / queue / session')->state(fn (): string => config('cache.default').' / '.config('queue.default').' / '.config('session.driver')),
                    TextEntry::make('timezone')->state(fn (): string => config('app.timezone').' · '.now()->format('M j, g:i a')),
                    TextEntry::make('url')->label('App URL')->state(fn (): string => config('app.url')),
                ])
                ->columns(3)
                ->collapsible(),
            Section::make('Error log')
                ->description('The most recent entries from storage/logs/laravel.log.')
                ->schema([
                    TextEntry::make('log')
                        ->hiddenLabel()
                        ->state(fn (): HtmlString => new HtmlString(
                            '<pre style="white-space: pre-wrap; word-break: break-word; max-height: 32rem; overflow: auto; font-size: 0.75rem; line-height: 1.4;">'
                            .e(static::logTail())
                            .'</pre>'
                        )),
                ])
                ->collapsible(),
        ]);
    }

    public static function logPath(): string
    {
        return config('logging.channels.single.path', storage_path('logs/laravel.log'));
    }

    /**
     * The end of the log, newest entries first, so the latest problem is at the top.
     */
    public static function logTail(): string
    {
        $path = static::logPath();

        if (! is_file($path) || filesize($path) === 0) {
            return 'The log is empty. 🎉';
        }

        $size = filesize($path);
        $handle = fopen($path, 'r');
        fseek($handle, max(0, $size - static::LOG_TAIL_BYTES));
        $tail = stream_get_contents($handle);
        fclose($handle);

        $entries = preg_split('/(?=^\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/m', $tail, flags: PREG_SPLIT_NO_EMPTY);

        if ($size > static::LOG_TAIL_BYTES) {
            array_shift($entries);
        }

        return implode('', array_reverse($entries));
    }

    protected static function lastBackupSummary(): string
    {
        $latest = app(BackupManager::class)->latest();

        return $latest ? $latest['created_at']->diffForHumans() : 'Never';
    }

    protected static function lastBackupIsRecent(): bool
    {
        $latest = app(BackupManager::class)->latest();

        return $latest !== null && $latest['created_at']->gt(now()->subHours(36));
    }

    protected static function schedulerSummary(): string
    {
        $lastRun = Cache::get('scheduler:last_run');

        return $lastRun ? 'Last ran '.Carbon::parse($lastRun)->diffForHumans() : 'Not running';
    }

    protected static function schedulerIsRunning(): bool
    {
        $lastRun = Cache::get('scheduler:last_run');

        return $lastRun !== null && Carbon::parse($lastRun)->gt(now()->subMinutes(10));
    }

    protected static function databaseSummary(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = $connection->getConfig('database');

        if ($driver === 'sqlite' && is_string($database) && is_file($database)) {
            return 'SQLite · '.BackupManager::formatBytes(filesize($database));
        }

        return $driver;
    }

    protected static function uploadsSummary(): string
    {
        $disk = Storage::disk('public');
        $files = collect($disk->allFiles())->reject(fn (string $file): bool => basename($file) === '.gitignore');

        return $files->count().' files · '.BackupManager::formatBytes($files->sum(fn (string $file): int => $disk->size($file)));
    }

    protected static function freeDiskSpace(): string
    {
        $free = @disk_free_space(storage_path());

        return $free === false ? 'unknown' : BackupManager::formatBytes($free);
    }
}
