<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== Test Order Status Logic ===\n\n";

try {
    // Tìm một đơn hàng có nhiều fulfillments để test
    $order = Order::with('fulfillments')
        ->whereHas('fulfillments')
        ->get()
        ->filter(function($order) {
            return $order->fulfillments->count() > 1;
        })
        ->first();
    
    if (!$order) {
        echo "Không tìm thấy đơn hàng có nhiều fulfillments để test.\n";
        echo "Tạo test data...\n";
        
        // Tạo đơn hàng test
        $order = Order::create([
            'user_id' => 1,
            'order_code' => 'TEST-' . time(),
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '0123456789',
            'shipping_address_line1' => 'Test Address',
            'status' => Order::STATUS_PROCESSING,
            'sub_total' => 100.00,
            'grand_total' => 100.00,
            'payment_status' => 'paid'
        ]);
        
        // Tạo 2 fulfillments
        $fulfillment1 = OrderFulfillment::create([
            'order_id' => $order->id,
            'tracking_code' => 'TRACK1-' . time(),
            'status' => OrderFulfillment::STATUS_PROCESSING
        ]);
        
        $fulfillment2 = OrderFulfillment::create([
            'order_id' => $order->id,
            'tracking_code' => 'TRACK2-' . time(),
            'status' => OrderFulfillment::STATUS_PROCESSING
        ]);
        
        $order->load('fulfillments');
    }
    
    echo "Đơn hàng test: {$order->order_code}\n";
    echo "Trạng thái hiện tại: {$order->status}\n";
    echo "Số lượng fulfillments: {$order->fulfillments->count()}\n\n";
    
    // Test case 1: Một fulfillment shipped -> partially_shipped
    echo "=== Test Case 1: Một fulfillment shipped ===\n";
    $firstFulfillment = $order->fulfillments->first();
    $firstFulfillment->status = OrderFulfillment::STATUS_SHIPPED;
    $firstFulfillment->save();
    
    // Gọi method cập nhật trạng thái
    $order->updateStatusBasedOnFulfillments();
    $order->refresh();
    echo "Trạng thái đơn hàng sau khi 1 fulfillment shipped: {$order->status}\n";
    echo "Expected: " . Order::STATUS_PARTIALLY_SHIPPED . "\n\n";
    
    // Test case 2: Tất cả fulfillments shipped -> shipped
    echo "=== Test Case 2: Tất cả fulfillments shipped ===\n";
    foreach ($order->fulfillments as $fulfillment) {
        $fulfillment->status = OrderFulfillment::STATUS_SHIPPED;
        $fulfillment->save();
    }
    
    // Gọi method cập nhật trạng thái
    $order->updateStatusBasedOnFulfillments();
    $order->refresh();
    echo "Trạng thái đơn hàng sau khi tất cả fulfillments shipped: {$order->status}\n";
    echo "Expected: " . Order::STATUS_SHIPPED . "\n\n";
    
    // Test case 3: Một fulfillment delivered -> partially_delivered
    echo "=== Test Case 3: Một fulfillment delivered ===\n";
    $firstFulfillment->status = OrderFulfillment::STATUS_DELIVERED;
    $firstFulfillment->save();
    
    // Gọi method cập nhật trạng thái
    $order->updateStatusBasedOnFulfillments();
    $order->refresh();
    echo "Trạng thái đơn hàng sau khi 1 fulfillment delivered: {$order->status}\n";
    echo "Expected: " . Order::STATUS_PARTIALLY_DELIVERED . "\n\n";
    
    // Test case 4: Tất cả fulfillments delivered -> delivered
    echo "=== Test Case 4: Tất cả fulfillments delivered ===\n";
    foreach ($order->fulfillments as $fulfillment) {
        $fulfillment->status = OrderFulfillment::STATUS_DELIVERED;
        $fulfillment->save();
    }
    
    // Gọi method cập nhật trạng thái
    $order->updateStatusBasedOnFulfillments();
    $order->refresh();
    echo "Trạng thái đơn hàng sau khi tất cả fulfillments delivered: {$order->status}\n";
    echo "Expected: " . Order::STATUS_DELIVERED . "\n\n";
    
    echo "=== Test hoàn thành ===\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}