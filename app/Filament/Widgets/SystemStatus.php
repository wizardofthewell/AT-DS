<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Backups;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Support\Backups as BackupManager;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Quick health check on the dashboard, for super admins only.
 */
class SystemStatus extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'System';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    protected function getStats(): array
    {
        $latestBackup = app(BackupManager::class)->latest();
        $backupIsRecent = $latestBackup && $latestBackup['created_at']->gt(now()->subHours(36));
        $failedLogins = AuditLog::where('event', 'login_failed')->where('created_at', '>=', now()->subDay())->count();

        return [
            Stat::make('Last backup', $latestBackup ? $latestBackup['created_at']->diffForHumans() : 'Never')
                ->description($backupIsRecent ? 'Up to date' : 'Older than a day and a half')
                ->descriptionIcon($backupIsRecent ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($backupIsRecent ? 'success' : 'danger')
                ->url(Backups::getUrl()),
            Stat::make('Changes today', AuditLog::whereIn('event', ['created', 'updated', 'deleted'])->where('created_at', '>=', now()->startOfDay())->count())
                ->description('In the activity log')
                ->url(AuditLogResource::getUrl('index')),
            Stat::make('Failed sign-ins (24h)', $failedLogins)
                ->description($failedLogins > 5 ? 'Someone may be guessing passwords' : 'Nothing unusual')
                ->color($failedLogins > 5 ? 'danger' : 'gray')
                ->url(AuditLogResource::getUrl('index', ['tableFilters' => ['event' => ['values' => ['login_failed']]]])),
        ];
    }
}
