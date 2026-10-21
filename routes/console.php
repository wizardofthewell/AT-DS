<?php

use App\Models\AuditLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * The server needs one cron entry for these to run:
 * * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('app:backup --prune')->dailyAt('03:00')->withoutOverlapping();

Schedule::command('model:prune', ['--model' => [AuditLog::class]])->dailyAt('03:30');

// Lets the System page show whether the scheduler (and so nightly backups) is running.
Schedule::call(fn () => Cache::forever('scheduler:last_run', now()->toIso8601String()))
    ->name('scheduler-heartbeat')
    ->everyMinute();
