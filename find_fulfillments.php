<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\OrderFulfillment;
use App\Models\Order;

// Khởi tạo Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Tìm Fulfillments để Test ===\n";

// Tìm fulfillments có trạng thái 'processing' hoặc 'awaiting_shipment'
$fulfillments = OrderFulfillment::whereIn('status', ['processing', 'awaiting_shipment'])
    ->with(['order', 'storeLocation'])
    ->take(10)
    ->get();

echo "Tìm thấy " . $fulfillments->count() . " fulfillments:\n\n";

foreach ($fulfillments as $fulfillment) {
    echo "ID: {$fulfillment->id}\n";
    echo "Status: {$fulfillment->status}\n";
    echo "Order: {$fulfillment->order->order_code}\n";
    echo "Store Location ID: {$fulfillment->store_location_id}\n";
    if ($fulfillment->storeLocation) {
        echo "Store Location: {$fulfillment->storeLocation->name}\n";
        echo "Province Code: {$fulfillment->storeLocation->province_code}\n";
    }
    echo "Shipping Province: {$fulfillment->order->shipping_old_province_code}\n";
    echo "---\n";
}

echo "\n=== Kết thúc ===\n";