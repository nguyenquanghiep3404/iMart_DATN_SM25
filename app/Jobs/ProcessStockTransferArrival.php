<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\StockTransfer;
use App\Models\ShippingTransitTime;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessStockTransferArrival implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ProcessStockTransferArrival job started');
        
        // Lấy tất cả phiếu chuyển kho đang trong trạng thái in_transit
        $inTransitTransfers = StockTransfer::where('status', 'in_transit')
            ->whereNotNull('shipped_at')
            ->get();
            
        $processedCount = 0;
        
        foreach ($inTransitTransfers as $transfer) {
            if ($this->shouldMarkAsReceived($transfer)) {
                $transfer->update([
                    'status' => 'received',
                    'received_at' => now()
                ]);
                
                Log::info('Auto-marked stock transfer as received', [
                    'transfer_id' => $transfer->id,
                    'transfer_code' => $transfer->transfer_code
                ]);
                
                $processedCount++;
            }
        }
        
        Log::info('ProcessStockTransferArrival job completed', [
            'total_checked' => $inTransitTransfers->count(),
            'processed' => $processedCount
        ]);
    }
    
    /**
     * Kiểm tra xem phiếu chuyển kho có nên được đánh dấu là đã nhận không
     */
    private function shouldMarkAsReceived(StockTransfer $transfer): bool
    {
        $fromLocation = StoreLocation::find($transfer->from_location_id);
        $toLocation = StoreLocation::find($transfer->to_location_id);
        
        if (!$fromLocation || !$toLocation) {
            return false;
        }
        
        // Tính thời gian vận chuyển
        $transitTime = ShippingTransitTime::getTransitTime(
            'store_shipper',
            $fromLocation->province_code,
            $toLocation->province_code
        );
        
        // Nếu không có dữ liệu, sử dụng giá trị mặc định
        if (!$transitTime) {
            $fromProvince = ProvinceOld::where('code', $fromLocation->province_code)->first();
            $toProvince = ProvinceOld::where('code', $toLocation->province_code)->first();
            
            $transitDays = $this->calculateDefaultTransitTime($fromProvince, $toProvince);
        } else {
            $transitDays = $transitTime->transit_days_max;
        }
        
        // Kiểm tra xem đã quá thời gian dự kiến chưa
        $expectedArrivalTime = Carbon::parse($transfer->shipped_at)->addDays($transitDays);
        
        return now()->greaterThanOrEqualTo($expectedArrivalTime);
    }
    
    /**
     * Tính thời gian vận chuyển mặc định dựa trên vùng miền
     */
    private function calculateDefaultTransitTime($fromProvince, $toProvince): int
    {
        if (!$fromProvince || !$toProvince) {
            return 3; // Mặc định 3 ngày
        }
        
        // Cùng vùng miền: 2 ngày
        if ($fromProvince->region === $toProvince->region) {
            return 2;
        }
        
        // Khác vùng miền: 4 ngày
        return 4;
    }
}
