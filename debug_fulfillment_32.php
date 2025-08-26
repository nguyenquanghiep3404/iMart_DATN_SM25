<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\Order;
use App\Models\StoreLocation;

echo "=== DEBUG FULFILLMENT ID 32 ===\n\n";

// Kiểm tra fulfillment ID 32
$fulfillment = OrderFulfillment::find(32);

if (!$fulfillment) {
    echo "✗ Không tìm thấy fulfillment ID 32\n";
    exit;
}

echo "Fulfillment ID: {$fulfillment->id}\n";
echo "Order ID: {$fulfillment->order_id}\n";
echo "Store Location ID: {$fulfillment->store_location_id}\n";
echo "Status: {$fulfillment->status}\n";
echo "Tracking Code: {$fulfillment->tracking_code}\n\n";

// Kiểm tra đơn hàng
$order = $fulfillment->order;
echo "=== THÔNG TIN ĐƠN HÀNG ===\n";
echo "Order Code: {$order->order_code}\n";
echo "Order Status: {$order->status}\n";
echo "Shipping Province: {$order->shipping_old_province_code}\n\n";

// Kiểm tra kho
$storeLocation = $fulfillment->storeLocation;
echo "=== THÔNG TIN KHO ===\n";
echo "Store Name: {$storeLocation->name}\n";
echo "Store ID: {$storeLocation->id}\n";
echo "Province Code: {$storeLocation->province_code}\n\n";

// Kiểm tra điều kiện lọc
echo "=== KIỂM TRA ĐIỀU KIỆN LỌC ===\n";
echo "Kho đích cần tìm: 6 (hà nội)\n";
echo "Kho hiện tại của fulfillment: {$fulfillment->store_location_id}\n";
echo "Khớp kho: " . ($fulfillment->store_location_id == 6 ? 'Có' : 'Không') . "\n";

echo "Status hiện tại: {$fulfillment->status}\n";
echo "Điều kiện status != 'packed': " . ($fulfillment->status != 'packed' ? 'Thỏa mãn' : 'Không thỏa mãn') . "\n\n";

// Kiểm tra query chính xác
echo "=== KIỂM TRA QUERY ===\n";
$fulfillments = $order->fulfillments()
    ->where('store_location_id', 6)
    ->where('status', '!=', 'packed')
    ->get();

echo "Số fulfillments tìm thấy với điều kiện:\n";
echo "- store_location_id = 6\n";
echo "- status != 'packed'\n";
echo "Kết quả: {$fulfillments->count()} fulfillments\n\n";

foreach ($fulfillments as $f) {
    echo "- ID: {$f->id}, Store: {$f->store_location_id}, Status: {$f->status}\n";
}

// Kiểm tra tất cả fulfillments của đơn hàng
echo "\n=== TẤT CẢ FULFILLMENTS CỦA ĐƠN HÀNG ===\n";
$allFulfillments = $order->fulfillments()->get();
foreach ($allFulfillments as $f) {
    echo "- ID: {$f->id}, Store: {$f->store_location_id}, Status: {$f->status}, Tracking: {$f->tracking_code}\n";
}

echo "\n=== KẾT THÚC DEBUG ===\n";