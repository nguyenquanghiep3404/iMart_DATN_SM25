<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

// Import các lớp cho xác thực người dùng
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateUserStatusAfterVerification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
        
        // Đăng ký listener cho sự kiện Verified
        Event::listen(
            Verified::class,
            [UpdateUserStatusAfterVerification::class, 'handle']
        );
    }
}
