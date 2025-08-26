<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\Order;

echo "=== DEBUG CURRENT FULFILLMENTS ===\n\n";

// Tìm đơn hàng
$order = Order::where('order_code', 'DH-PMSHCHKQGA')->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng\n";
    exit;
}

echo "Đơn hàng: {$order->order_code}\n";
echo "Trạng thái: {$order->status}\n\n";

// Lấy tất cả fulfillments của đơn hàng
$fulfillments = $order->fulfillments()->get();

echo "Tổng số fulfillments: {$fulfillments->count()}\n\n";

foreach ($fulfillments as $fulfillment) {
    echo "--- Fulfillment ID: {$fulfillment->id} ---\n";
    echo "Store Location: {$fulfillment->store_location_id}\n";
    echo "Status: {$fulfillment->status}\n";
    echo "Tracking: {$fulfillment->tracking_code}\n";
    echo "Created: {$fulfillment->created_at}\n";
    echo "Updated: {$fulfillment->updated_at}\n";
    
    // Kiểm tra items
    echo "Items:\n";
    foreach ($fulfillment->items as $item) {
        echo "  - {$item->orderItem->productVariant->sku}: {$item->quantity}\n";
    }
    echo "\n";
}

// Kiểm tra các điều kiện tìm kiếm khác nhau
echo "=== KIỂM TRA CÁC ĐIỀU KIỆN TÌM KIẾM ===\n\n";

// Điều kiện hiện tại (sai)
echo "1. Fulfillments tại kho 7 với status 'processing':\n";
$fulfillments1 = $order->fulfillments()
    ->where('store_location_id', 7)
    ->where('status', 'processing')
    ->get();
echo "Số lượng: {$fulfillments1->count()}\n\n";

// Điều kiện đúng (tìm fulfillment đã chuyển kho nhưng chưa packed)
echo "2. Fulfillments tại kho 6 với status 'processing':\n";
$fulfillments2 = $order->fulfillments()
    ->where('store_location_id', 6)
    ->where('status', 'processing')
    ->get();
echo "Số lượng: {$fulfillments2->count()}\n";
foreach ($fulfillments2 as $f) {
    echo "  - ID: {$f->id}, Tracking: {$f->tracking_code}\n";
}
echo "\n";

// Tìm tất cả fulfillment có status processing
echo "3. Tất cả fulfillments với status 'processing':\n";
$fulfillments3 = $order->fulfillments()
    ->where('status', 'processing')
    ->get();
echo "Số lượng: {$fulfillments3->count()}\n";
foreach ($fulfillments3 as $f) {
    echo "  - ID: {$f->id}, Store: {$f->store_location_id}, Tracking: {$f->tracking_code}\n";
}

echo "\n=== KẾT THÚC DEBUG ===\n";