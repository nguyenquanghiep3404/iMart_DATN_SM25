<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== SỬA TRẠNG THÁI FULFILLMENT THỦ CÔNG ===\n\n";

// Tìm phiếu chuyển kho đã received
$stockTransfer = StockTransfer::where('transfer_code', 'FULFILL-DH-JIHM4GDU0X-51-48AA')->first();

if (!$stockTransfer) {
    echo "Không tìm thấy phiếu chuyển kho\n";
    exit;
}

echo "Phiếu chuyển kho: {$stockTransfer->transfer_code}\n";
echo "Trạng thái: {$stockTransfer->status}\n";
echo "Từ kho: {$stockTransfer->from_location_id}\n";
echo "Đến kho: {$stockTransfer->to_location_id}\n\n";

// Tìm đơn hàng liên quan
$orderCode = null;
if ($stockTransfer->notes && str_contains($stockTransfer->notes, 'Order:')) {
    $parts = explode('Order:', $stockTransfer->notes);
    if (count($parts) > 1) {
        $orderPart = trim($parts[1]);
        $orderCode = explode(' ', $orderPart)[0];
        $orderCode = explode('(', $orderCode)[0];
        $orderCode = trim($orderCode);
    }
}

if (!$orderCode) {
    echo "Không tìm thấy mã đơn hàng trong ghi chú\n";
    exit;
}

$relatedOrder = Order::where('order_code', $orderCode)->first();
if (!$relatedOrder) {
    echo "Không tìm thấy đơn hàng {$orderCode}\n";
    exit;
}

echo "Đơn hàng liên quan: {$relatedOrder->order_code}\n";
echo "Tỉnh giao hàng: {$relatedOrder->shipping_old_province_code}\n\n";

$destinationLocation = StoreLocation::find($stockTransfer->to_location_id);
echo "Kho đích: {$destinationLocation->name} (Tỉnh: {$destinationLocation->province_code})\n\n";

// Kiểm tra xem kho đích có cùng tỉnh với địa chỉ giao hàng không
if ($destinationLocation->province_code === $relatedOrder->shipping_old_province_code) {
    echo "Kho đích cùng tỉnh với địa chỉ giao hàng. Tiến hành cập nhật fulfillment...\n\n";
    
    // Tìm các fulfillment của đơn hàng này tại kho ĐÍCH với trạng thái 'processing'
    $fulfillments = $relatedOrder->fulfillments()
        ->where('store_location_id', $destinationLocation->id)
        ->where('status', 'processing')
        ->get();
    
    echo "Tìm thấy {$fulfillments->count()} fulfillment(s) cần cập nhật:\n";
    
    foreach ($fulfillments as $fulfillment) {
        echo "- Fulfillment ID: {$fulfillment->id}\n";
        echo "  Trạng thái hiện tại: {$fulfillment->status}\n";
        echo "  Kho hiện tại: {$fulfillment->store_location_id}\n";
        
        // Kiểm tra tồn kho
        $hasStock = true;
        foreach ($fulfillment->items as $item) {
            $inventory = ProductInventory::where('product_variant_id', $item->orderItem->product_variant_id)
                ->where('store_location_id', $destinationLocation->id)
                ->where('inventory_type', 'new')
                ->first();
                
            if (!$inventory || $inventory->quantity < $item->quantity) {
                $hasStock = false;
                echo "  ❌ Không đủ hàng cho sản phẩm ID {$item->orderItem->product_variant_id}\n";
                break;
            }
        }
        
        if ($hasStock) {
            echo "  ✅ Đủ hàng, cập nhật trạng thái thành 'packed'\n";
            
            // Cập nhật trạng thái fulfillment
            $fulfillment->update([
                'status' => 'packed'
            ]);
            
            echo "  ✅ Đã cập nhật thành công!\n";
        } else {
            echo "  ❌ Không đủ hàng, không cập nhật\n";
        }
        
        echo "\n";
    }
} else {
    echo "Kho đích không cùng tỉnh với địa chỉ giao hàng. Không cập nhật fulfillment.\n";
}

echo "=== HOÀN THÀNH ===\n";