<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "Kiểm tra đơn hàng có nhiều gói hàng...\n";

// Lấy tất cả đơn hàng có fulfillments
$orders = Order::with('fulfillments')->get();

// Lọc đơn hàng có nhiều hơn 1 fulfillment
$multiPackageOrders = $orders->filter(function($order) {
    return $order->fulfillments->count() > 1;
});

echo "Tổng số đơn hàng: " . $orders->count() . "\n";
echo "Đơn hàng có nhiều gói hàng: " . $multiPackageOrders->count() . "\n\n";

// Hiển thị chi tiết 5 đơn hàng đầu tiên
foreach($multiPackageOrders->take(5) as $order) {
    echo "Đơn hàng: {$order->order_code} - Số gói: {$order->fulfillments->count()}\n";
    foreach($order->fulfillments as $fulfillment) {
        $trackingCode = $fulfillment->tracking_code ?? 'NULL';
        echo "  Gói {$fulfillment->id}: tracking_code = {$trackingCode}, store_location_id = {$fulfillment->store_location_id}\n";
    }
    echo "\n";
}

// Kiểm tra đơn hàng có fulfillments nhưng thiếu tracking code
$ordersWithMissingTrackingCodes = $orders->filter(function($order) {
    return $order->fulfillments->count() > 0 && 
           $order->fulfillments->whereNull('tracking_code')->count() > 0;
});

echo "Đơn hàng có gói thiếu mã vận đơn: " . $ordersWithMissingTrackingCodes->count() . "\n";
foreach($ordersWithMissingTrackingCodes->take(3) as $order) {
    echo "Đơn hàng: {$order->order_code}\n";
    foreach($order->fulfillments as $fulfillment) {
        $trackingCode = $fulfillment->tracking_code ?? 'THIẾU';
        echo "  Gói {$fulfillment->id}: tracking_code = {$trackingCode}\n";
    }
    echo "\n";
}