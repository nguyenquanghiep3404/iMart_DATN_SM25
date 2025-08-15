<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StockTransfer;
use App\Services\OrderFulfillmentCheckService;
use App\Models\ShippingTransitTime;

echo "=== Kiểm thử logic thời gian vận chuyển ===\n\n";

// 1. Kiểm tra dữ liệu ShippingTransitTime
echo "1. Kiểm tra dữ liệu thời gian vận chuyển:\n";
$transitTimes = ShippingTransitTime::take(5)->get();
foreach ($transitTimes as $time) {
    echo "   {$time->from_province_code} -> {$time->to_province_code}: {$time->transit_time_hours} giờ\n";
}
echo "\n";

// 2. Kiểm tra phiếu chuyển kho
echo "2. Kiểm tra phiếu chuyển kho:\n";
$transfers = StockTransfer::with(['fromLocation', 'toLocation'])
    ->whereNotNull('shipped_at')
    ->take(3)
    ->get();

if ($transfers->count() > 0) {
    $service = new OrderFulfillmentCheckService();
    
    foreach ($transfers as $transfer) {
        echo "   Transfer: {$transfer->transfer_code}\n";
        echo "   From: {$transfer->fromLocation->name} ({$transfer->fromLocation->province_code})\n";
        echo "   To: {$transfer->toLocation->name} ({$transfer->toLocation->province_code})\n";
        echo "   Status: {$transfer->status}\n";
        echo "   Shipped: {$transfer->shipped_at}\n";
        
        $estimatedArrival = $service->calculateEstimatedArrival($transfer);
        echo "   Estimated arrival: " . ($estimatedArrival ? $estimatedArrival->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "   ---\n";
    }
} else {
    echo "   Không tìm thấy phiếu chuyển kho nào\n";
}

// 3. Test tạo phiếu chuyển kho mới
echo "\n3. Test tính toán thời gian cho phiếu chuyển kho mới:\n";
$fromProvince = 'HN'; // Hà Nội
$toProvince = 'HCM';  // TP.HCM

$transitTime = ShippingTransitTime::where('from_province_code', $fromProvince)
    ->where('to_province_code', $toProvince)
    ->first();

if ($transitTime) {
    $dispatchTime = now();
    $estimatedArrival = $dispatchTime->copy()->addHours($transitTime->transit_time_hours);
    
    echo "   Route: {$fromProvince} -> {$toProvince}\n";
    echo "   Transit time: {$transitTime->transit_time_hours} giờ\n";
    echo "   Dispatch time: {$dispatchTime->format('Y-m-d H:i:s')}\n";
    echo "   Estimated arrival: {$estimatedArrival->format('Y-m-d H:i:s')}\n";
} else {
    echo "   Không tìm thấy dữ liệu thời gian vận chuyển cho route {$fromProvince} -> {$toProvince}\n";
}

echo "\n=== Hoàn thành kiểm thử ===\n";