<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "Testing fixed query for Order ID 57:\n";

try {
    $order = Order::with([
        'user',
        'orderItems.productVariant.product',
        'orderFulfillments.storeLocation',
        'orderFulfillments.items.orderItem.productVariant.product'
    ])->findOrFail(57);
    
    echo "SUCCESS: Order loaded with all relationships!\n";
    echo "Order code: {$order->order_code}\n";
    echo "User: {$order->user->name}\n";
    echo "Order items: {$order->orderItems->count()}\n";
    echo "Order fulfillments: {$order->orderFulfillments->count()}\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}