<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "=== TEST VIEW DATA ===\n\n";

// Simulate controller logic
$order = Order::with([
    'user:id,name,email,phone_number',
    'items.productVariant.product:id,name,slug',
    'items.productVariant.product.coverImage',
    'items.productVariant.primaryImage',
    'processor:id,name',
    'shipper:id,name',
    'couponUsages.coupon:id,code,type,value,description',
    // Load thông tin fulfillments cho mô hình đa kho
    'fulfillments:id,order_id,store_location_id,shipper_id,tracking_code,shipping_carrier,status,shipped_at,delivered_at,estimated_delivery_date,shipping_fee',
    'fulfillments.storeLocation:id,name,address,phone,province_code,district_code,ward_code,type',
    'fulfillments.storeLocation.province:code,name,name_with_type',
    'fulfillments.storeLocation.district:code,name,name_with_type', 
    'fulfillments.storeLocation.ward:code,name,name_with_type',
    'fulfillments.items',
    'fulfillments.items.orderItem:id,product_variant_id,product_name,variant_attributes,sku,quantity,price,total_price',
    'fulfillments.items.orderItem.productVariant:id,sku,product_id,primary_image_id',
    'fulfillments.items.orderItem.productVariant.product:id,name',
    'fulfillments.items.orderItem.productVariant.primaryImage',
    'fulfillments.items.orderItem.productVariant.product.coverImage',
])->find(71);

if (!$order) {
    echo "❌ Không tìm thấy đơn hàng 71\n";
    exit;
}

echo "✅ Đơn hàng #{$order->order_code}\n\n";

echo "📦 Fulfillments được load từ controller:\n";
foreach ($order->fulfillments as $fulfillment) {
    echo "   - ID: {$fulfillment->id}\n";
    echo "   - Store: {$fulfillment->storeLocation->name}\n";
    echo "   - Status: {$fulfillment->status}\n";
    
    // Test blade template logic
    if ($fulfillment->estimated_delivery_date) {
        $formatted_date = \Carbon\Carbon::parse($fulfillment->estimated_delivery_date)->format('d/m/Y');
        echo "   - ✅ Estimated Delivery Date: {$fulfillment->estimated_delivery_date} (formatted: {$formatted_date})\n";
    } else {
        echo "   - ❌ Estimated Delivery Date: NULL\n";
    }
    
    if ($fulfillment->shipping_fee) {
        $formatted_fee = number_format($fulfillment->shipping_fee, 0, ',', '.');
        echo "   - ✅ Shipping Fee: {$fulfillment->shipping_fee} (formatted: {$formatted_fee} ₫)\n";
    } else {
        echo "   - ❌ Shipping Fee: NULL\n";
    }
    
    // Test condition for showing the section
    $show_section = $fulfillment->shipped_at || $fulfillment->delivered_at || $fulfillment->estimated_delivery_date || $fulfillment->shipping_fee;
    echo "   - Show section: " . ($show_section ? 'YES' : 'NO') . "\n";
    
    echo "   ---\n";
}

echo "\n✅ TEST HOÀN THÀNH!\n";