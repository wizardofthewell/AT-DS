<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:super-admin {email : Email of an existing admin user} {--revoke : Remove super admin access instead}')]
#[Description('Grant (or revoke) super admin access to the System tools: activity log, backups and site health')]
class MakeSuperAdmin extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}. Create the account in the admin first.");

            return self::FAILURE;
        }

        $user->forceFill(['is_super_admin' => ! $this->option('revoke')])->save();

        $this->info($this->option('revoke')
            ? "{$user->email} is no longer a super admin."
            : "{$user->email} is now a super admin.");

        return self::SUCCESS;
    }
}
