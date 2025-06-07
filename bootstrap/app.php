<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../core/routes/web.php',
        api: __DIR__.'/../core/routes/api.php',
        commands: __DIR__.'/../core/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\PathServiceProvider::class,
    ])
    ->create()
    ->useAppPath(realpath(__DIR__.'/../core/app'))
    ->useConfigPath(realpath(__DIR__.'/../core/config'))
    ->useDatabasePath(realpath(__DIR__.'/../core/database'))
    ->useBootstrapPath(realpath(__DIR__)); 