<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CancelUnpaidQrOrders::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:detect-abandoned-carts')->everyMinute();
        $schedule->command('customers:update-groups')->daily();
        $schedule->command('orders:cancel-unpaid-qr')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    
}
