<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\BackupFailed;
use App\Support\Backups;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

#[Signature('app:backup {--prune : Also remove old backups}')]
#[Description('Back up the database and uploaded files to storage/app/private/backups')]
class BackupSite extends Command
{
    public function handle(Backups $backups): int
    {
        try {
            $name = $backups->create();
        } catch (Throwable $e) {
            report($e);
            $this->error('Backup failed: '.$e->getMessage());

            Notification::send(User::where('is_super_admin', true)->get(), new BackupFailed($e->getMessage()));

            return self::FAILURE;
        }

        $this->info("Created {$name}");

        if ($this->option('prune')) {
            $removed = $backups->prune();
            $this->info("Removed {$removed} old ".str('backup')->plural($removed).'.');
        }

        return self::SUCCESS;
    }
}
