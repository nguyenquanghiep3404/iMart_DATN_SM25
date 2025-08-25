<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StoreLocation;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM TRA VÀ TẠO DỮ LIỆU FULFILLMENT ===\n\n";

// 1. Kiểm tra đơn hàng 71
$order = Order::find(71);
if (!$order) {
    echo "❌ Không tìm thấy đơn hàng 71\n";
    exit;
}

echo "✅ Tìm thấy đơn hàng #{$order->order_code}\n";
echo "   - Khách hàng: {$order->customer_name}\n";
echo "   - Tổng tiền: " . number_format($order->grand_total) . " VND\n";
echo "   - Phí ship: " . number_format($order->shipping_fee ?? 0) . " VND\n\n";

// 2. Kiểm tra fulfillments hiện tại
$existingFulfillments = $order->fulfillments;
echo "📦 Fulfillments hiện tại: {$existingFulfillments->count()}\n";

if ($existingFulfillments->count() > 0) {
    foreach ($existingFulfillments as $fulfillment) {
        echo "   - ID: {$fulfillment->id}, Status: {$fulfillment->status}";
        echo ", Estimated Delivery: " . ($fulfillment->estimated_delivery_date ?? 'NULL') . "\n";
    }
} else {
    echo "   - Chưa có fulfillment nào\n";
}

echo "\n";

// 3. Kiểm tra store locations
$storeLocations = StoreLocation::where('is_active', true)->get();
echo "🏪 Store locations có sẵn: {$storeLocations->count()}\n";

if ($storeLocations->count() == 0) {
    echo "❌ Không có store location nào. Tạo store location mẫu...\n";
    $storeLocation = StoreLocation::create([
        'name' => 'Kho chính',
        'address' => '123 Đường ABC, Quận 1',
        'phone' => '0123456789',
        'province_code' => '01',
        'district_code' => '001',
        'ward_code' => '00001',
        'type' => 'warehouse',
        'is_active' => true
    ]);
    echo "✅ Đã tạo store location ID: {$storeLocation->id}\n";
} else {
    $storeLocation = $storeLocations->first();
    echo "✅ Sử dụng store location: {$storeLocation->name} (ID: {$storeLocation->id})\n";
}

echo "\n";

// 4. Tạo hoặc cập nhật fulfillment
if ($existingFulfillments->count() == 0) {
    echo "🔨 Tạo fulfillment mới...\n";
    $fulfillment = OrderFulfillment::create([
        'order_id' => $order->id,
        'store_location_id' => $storeLocation->id,
        'status' => 'pending',
        'estimated_delivery_date' => '2025-08-30',
        'shipping_carrier' => 'Giao hàng nhanh',
        'tracking_code' => 'GHN' . time()
    ]);
    echo "✅ Đã tạo fulfillment ID: {$fulfillment->id}\n";
} else {
    echo "🔨 Cập nhật fulfillment hiện tại...\n";
    $fulfillment = $existingFulfillments->first();
    $fulfillment->update([
        'estimated_delivery_date' => '2025-08-30',
        'shipping_carrier' => 'Giao hàng nhanh',
        'tracking_code' => 'GHN' . time(),
        'shipping_fee' => 25000
    ]);
    echo "✅ Đã cập nhật fulfillment ID: {$fulfillment->id} với shipping_fee\n";
    
    // Cập nhật fulfillment thứ hai nếu có
    if ($existingFulfillments->count() > 1) {
        $secondFulfillment = $existingFulfillments->skip(1)->first();
        $secondFulfillment->update([
            'shipping_fee' => 30000
        ]);
        echo "✅ Đã cập nhật fulfillment ID: {$secondFulfillment->id} với shipping_fee\n";
    }
}

echo "\n";

// 5. Kiểm tra lại dữ liệu
echo "🔍 Kiểm tra lại dữ liệu sau khi tạo/cập nhật:\n";
$order->refresh();
$order->load('fulfillments.storeLocation');

foreach ($order->fulfillments as $fulfillment) {
    echo "   - Fulfillment ID: {$fulfillment->id}\n";
    echo "   - Store: " . ($fulfillment->storeLocation->name ?? 'N/A') . "\n";
    echo "   - Status: {$fulfillment->status}\n";
    echo "   - Estimated Delivery: {$fulfillment->estimated_delivery_date}\n";
    echo "   - Shipping Carrier: " . ($fulfillment->shipping_carrier ?? 'N/A') . "\n";
    echo "   - Tracking Code: " . ($fulfillment->tracking_code ?? 'N/A') . "\n";
}

echo "\n✅ HOÀN THÀNH! Bây giờ hãy kiểm tra trang chi tiết đơn hàng.\n";
echo "🌐 URL: /admin/orders/{$order->id}\n";