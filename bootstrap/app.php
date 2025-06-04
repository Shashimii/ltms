<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \Inertia\EncryptHistoryMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,         // 2
            'chief' => \App\Http\Middleware\ChiefMiddleware::class,         // 1
            'officer' => \App\Http\Middleware\OfficerMiddleware::class,     // 0
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
