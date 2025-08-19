<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen(fn ($request) => $request->is('api/webhooks/casso'));
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen(fn ($request) => $request->is('api/webhooks/casso'));
        
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'permission' => \App\Http\Middleware\CheckUserPermission::class,
            'check.content.access' => \App\Http\Middleware\CheckContentAccess::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
    ])->create();