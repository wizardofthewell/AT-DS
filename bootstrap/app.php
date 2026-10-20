<?php

use App\Http\Middleware\ShowComingSoonUntilLive;
use App\Support\Site;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Addresses outside the public routes would otherwise show the full 404 page (menu and all) before launch.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('admin', 'admin/*') && ShowComingSoonUntilLive::shouldShowComingSoon($request, app(Site::class))) {
                return ShowComingSoonUntilLive::comingSoonResponse();
            }
        });
    })->create();
