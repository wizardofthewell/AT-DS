<?php

namespace App\Providers;

use App\Support\Site;
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
    }
}
