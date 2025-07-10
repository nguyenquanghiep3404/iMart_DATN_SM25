<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php', 
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
    ->withMiddleware(function (Middleware $middleware) {
        // =================================================================
        // ĐĂNG KÝ ALIAS CHO MIDDLEWARE TẠI ĐÂY
        // =================================================================
        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen(fn ($request) => $request->is('api/webhooks/casso'));
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen(fn ($request) => $request->is('api/webhooks/casso'));
        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckUserRole::class,
            'permission' => \App\Http\Middleware\CheckUserPermission::class,
            'check.content.access' => \App\Http\Middleware\CheckContentAccess::class

            
            
            // Bạn cũng có thể thêm các alias khác ở đây
            // 'auth' => \App\Http\Middleware\Authenticate::class, // (Ví dụ)
        ]);
        
        // Ghi chú thêm:
        // Nếu bạn muốn thêm middleware vào một group cụ thể (ví dụ 'web'), bạn dùng:
        // $middleware->group('web', [
        //     \App\Http\Middleware\EncryptCookies::class,
        //     // ...
        // ]);

        // Nếu bạn muốn thêm một middleware chạy toàn cục, bạn dùng:
        // $middleware->prepend(\App\Http\Middleware\ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();