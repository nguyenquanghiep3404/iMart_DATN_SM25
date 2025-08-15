<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ShippingTransitTime;
use App\Models\StockTransfer;
use App\Models\Location;

echo "=== Tạo dữ liệu mẫu ===\n";

// 1. Kiểm tra dữ liệu hiện có
echo "1. Kiểm tra dữ liệu ShippingTransitTime hiện có:\n";
$existingData = ShippingTransitTime::all();
echo "   Số lượng: " . $existingData->count() . "\n";

if ($existingData->count() == 0) {
    echo "2. Tạo dữ liệu mẫu ShippingTransitTime:\n";
    
    $sampleData = [
        ['01', '79', 48], // Hà Nội -> TP.HCM: 48 giờ
        ['79', '01', 48], // TP.HCM -> Hà Nội: 48 giờ
        ['01', '48', 24], // Hà Nội -> Đà Nẵng: 24 giờ
        ['48', '01', 24], // Đà Nẵng -> Hà Nội: 24 giờ
        ['79', '48', 24], // TP.HCM -> Đà Nẵng: 24 giờ
        ['48', '79', 24], // Đà Nẵng -> TP.HCM: 24 giờ
    ];
    
    foreach ($sampleData as $data) {
        ShippingTransitTime::create([
            'from_province_code' => $data[0],
            'to_province_code' => $data[1],
            'transit_time_hours' => $data[2],
            'transit_days_min' => ceil($data[2] / 24),
            'transit_days_max' => ceil($data[2] / 24) + 1,
        ]);
        echo "   Tạo: {$data[0]} -> {$data[1]}: {$data[2]} giờ\n";
    }
}

// 3. Kiểm tra locations
echo "3. Kiểm tra locations:\n";
$locations = Location::take(5)->get();
foreach ($locations as $location) {
    echo "   {$location->name} ({$location->province_code})\n";
}

// 4. Tạo stock transfer mẫu nếu cần
echo "4. Kiểm tra stock transfers:\n";
$transfers = StockTransfer::whereNotNull('shipped_at')->take(3)->get();
echo "   Số lượng có shipped_at: " . $transfers->count() . "\n";

if ($transfers->count() == 0) {
    echo "   Tạo stock transfer mẫu...\n";
    $fromLocation = Location::where('province_code', '01')->first();
    $toLocation = Location::where('province_code', '79')->first();
    
    if ($fromLocation && $toLocation) {
        $transfer = StockTransfer::create([
            'transfer_code' => 'TEST-' . time(),
            'from_location_id' => $fromLocation->id,
            'to_location_id' => $toLocation->id,
            'status' => 'in_transit',
            'shipped_at' => now()->subHours(12),
            'created_by' => 1,
        ]);
        echo "   Tạo transfer: {$transfer->transfer_code}\n";
    }
}

echo "\n=== Hoàn thành ===\n";