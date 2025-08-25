<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG FULFILLMENT DATA ===\n\n";

// 1. Kiểm tra đơn hàng 71
$order = Order::with([
    'fulfillments:id,order_id,store_location_id,shipper_id,tracking_code,shipping_carrier,status,shipped_at,delivered_at,estimated_delivery_date,shipping_fee',
    'fulfillments.storeLocation:id,name'
])->find(71);

if (!$order) {
    echo "❌ Không tìm thấy đơn hàng 71\n";
    exit;
}

echo "✅ Tìm thấy đơn hàng #{$order->order_code}\n";
echo "   - Khách hàng: {$order->customer_name}\n\n";

// 2. Kiểm tra fulfillments với dữ liệu chi tiết
echo "📦 Fulfillments data:\n";
foreach ($order->fulfillments as $fulfillment) {
    echo "   - ID: {$fulfillment->id}\n";
    echo "   - Store: {$fulfillment->storeLocation->name}\n";
    echo "   - Status: {$fulfillment->status}\n";
    echo "   - Estimated Delivery Date: " . ($fulfillment->estimated_delivery_date ?? 'NULL') . "\n";
    echo "   - Shipping Fee: " . ($fulfillment->shipping_fee ?? 'NULL') . "\n";
    echo "   - Tracking Code: " . ($fulfillment->tracking_code ?? 'NULL') . "\n";
    echo "   - Shipping Carrier: " . ($fulfillment->shipping_carrier ?? 'NULL') . "\n";
    echo "   ---\n";
}

// 3. Kiểm tra raw data từ database
echo "\n🔍 Raw database data:\n";
$rawFulfillments = DB::table('order_fulfillments')
    ->where('order_id', 71)
    ->select('id', 'estimated_delivery_date', 'shipping_fee', 'status', 'tracking_code')
    ->get();

foreach ($rawFulfillments as $raw) {
    echo "   - ID: {$raw->id}\n";
    echo "   - estimated_delivery_date: " . ($raw->estimated_delivery_date ?? 'NULL') . "\n";
    echo "   - shipping_fee: " . ($raw->shipping_fee ?? 'NULL') . "\n";
    echo "   - status: {$raw->status}\n";
    echo "   - tracking_code: " . ($raw->tracking_code ?? 'NULL') . "\n";
    echo "   ---\n";
}

echo "\n✅ DEBUG HOÀN THÀNH!\n";