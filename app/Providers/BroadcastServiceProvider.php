<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         Broadcast::routes(['middleware' => ['web']]);

        // THAY ĐỔI: Đăng ký các kênh
        require base_path('routes/channels.php');
    }
}
