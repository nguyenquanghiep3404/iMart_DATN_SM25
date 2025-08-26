<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;

echo "Checking fulfillments...\n";

// Kiểm tra đơn hàng có gói hàng
$order = Order::where('order_code', 'DH-P5XAZJP3RB')
    ->with(['fulfillments', 'items'])
    ->first();

if ($order) {
    echo "Order: {$order->order_code}\n";
    echo "Status: {$order->status}\n";
    echo "Store Location: {$order->store_location_id}\n";
    echo "Payment Status: {$order->payment_status}\n";
    
    echo "\nFulfillments ({$order->fulfillments->count()}):";
    foreach ($order->fulfillments as $fulfillment) {
        echo "\n  - ID: {$fulfillment->id}";
        echo "\n    Status: {$fulfillment->status}";
        echo "\n    Store Location: {$fulfillment->store_location_id}";
        echo "\n    Shipper ID: {$fulfillment->shipper_id}";
        echo "\n    Created: {$fulfillment->created_at}";
        echo "\n    Updated: {$fulfillment->updated_at}";
    }
    
    echo "\n\nOrder Items ({$order->items->count()}):";
    foreach ($order->items as $item) {
        echo "\n  - {$item->product_name} (Qty: {$item->quantity}, Price: {$item->price})";
    }
    
} else {
    echo "Order not found\n";
}

echo "\n\nDone.\n";