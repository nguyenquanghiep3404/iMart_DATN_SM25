<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Services\FulfillmentService;
use Illuminate\Support\Facades\Log;

echo "Laravel đã được khởi tạo thành công!\n";

// Tìm đơn hàng không có fulfillments
$order = Order::whereDoesntHave('fulfillments')->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng nào không có fulfillments\n";
    
    // Hiển thị thông tin các đơn hàng
    $orders = Order::with('fulfillments')->take(5)->get();
    foreach ($orders as $o) {
        echo "- ID: {$o->id}, Code: {$o->order_code}, Status: {$o->status}, Fulfillments: {$o->fulfillments->count()}\n";
    }
    exit;
}

echo "Tìm thấy đơn hàng không có fulfillments: {$order->order_code} (ID: {$order->id})\n";
echo "Trạng thái: {$order->status}\n";
echo "Payment status: {$order->payment_status}\n";

// Kiểm tra order items
$orderItems = $order->orderItems;
echo "Số lượng order items: {$orderItems->count()}\n";

foreach ($orderItems as $item) {
    echo "- Item ID: {$item->id}, Product: {$item->product_name}, Quantity: {$item->quantity}\n";
}

// Thử tạo fulfillments cho đơn hàng này
echo "\nThử tạo fulfillments cho đơn hàng...\n";

try {
    $fulfillmentService = new FulfillmentService();
    $fulfillmentService->createFulfillmentsForOrder($order);
    
    echo "Đã tạo fulfillments thành công!\n";
    
    // Kiểm tra lại fulfillments
    $order->refresh();
    $fulfillments = $order->fulfillments;
    echo "Số lượng fulfillments sau khi tạo: {$fulfillments->count()}\n";
    
    foreach ($fulfillments as $fulfillment) {
        echo "- Fulfillment ID: {$fulfillment->id}, Status: {$fulfillment->status}, Store: {$fulfillment->store_location_id}\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi khi tạo fulfillments: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}