<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Audit;
use App\Support\Impersonation;
use App\Support\Site;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Site::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('site', app(Site::class));

        $this->registerAuditListeners();
    }

    /**
     * Log sign-ins, sign-outs and failed attempts to the activity log.
     */
    protected function registerAuditListeners(): void
    {
        Event::listen(function (Login $event): void {
            if (! Impersonation::$isSwitching && $event->user instanceof User) {
                Audit::record('login', $event->user, user: $event->user, description: $event->remember ? 'Remembered sign-in' : null);
            }
        });

        Event::listen(function (Logout $event): void {
            if (! Impersonation::$isSwitching && $event->user instanceof User) {
                Audit::record('logout', $event->user, user: $event->user);
            }
        });

        Event::listen(function (Failed $event): void {
            $email = $event->credentials['email'] ?? 'unknown';

            Audit::record('login_failed', $event->user, description: "Failed sign-in for {$email}");
        });

        Event::listen(function (PasswordReset $event): void {
            if ($event->user instanceof User) {
                Audit::record('password_reset', $event->user, user: $event->user);
            }
        });
    }
}
