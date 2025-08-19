<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessStockTransferArrival;

class ProcessStockTransferArrivals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock-transfer:process-arrivals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process stock transfer arrivals and auto-mark as received when transit time is reached';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing stock transfer arrivals...');
        
        ProcessStockTransferArrival::dispatch();
        
        $this->info('Stock transfer arrival processing job dispatched successfully.');
        
        return 0;
    }
}
