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

echo "=== SỬA VỊ TRÍ VÀ TRẠNG THÁI FULFILLMENT ===\n\n";

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

// Tìm fulfillment liên quan đến phiếu chuyển kho này
// Từ ghi chú: "Order:DH-JIHM4GDU0X - Chuyển kho tự động cho fulfillment #51"
preg_match('/fulfillment #(\d+)/', $stockTransfer->notes, $matches);
if (!isset($matches[1])) {
    echo "Không tìm thấy fulfillment ID trong ghi chú\n";
    exit;
}

$fulfillmentId = $matches[1];
echo "Fulfillment ID từ ghi chú: {$fulfillmentId}\n\n";

$fulfillment = OrderFulfillment::find($fulfillmentId);
if (!$fulfillment) {
    echo "Không tìm thấy fulfillment ID {$fulfillmentId}\n";
    exit;
}

echo "=== FULFILLMENT HIỆN TẠI ===\n";
echo "ID: {$fulfillment->id}\n";
echo "Trạng thái: {$fulfillment->status}\n";
echo "Kho hiện tại: {$fulfillment->store_location_id}\n";
echo "Cập nhật lần cuối: {$fulfillment->updated_at}\n\n";

// Kiểm tra xem kho đích có cùng tỉnh với địa chỉ giao hàng không
if ($destinationLocation->province_code === $relatedOrder->shipping_old_province_code) {
    echo "Kho đích cùng tỉnh với địa chỉ giao hàng. Tiến hành cập nhật fulfillment...\n\n";
    
    // Kiểm tra tồn kho tại kho đích
    $hasStock = true;
    echo "Kiểm tra tồn kho tại kho đích:\n";
    
    foreach ($fulfillment->items as $item) {
        $inventory = ProductInventory::where('product_variant_id', $item->orderItem->product_variant_id)
            ->where('store_location_id', $destinationLocation->id)
            ->where('inventory_type', 'new')
            ->first();
            
        echo "- Sản phẩm ID {$item->orderItem->product_variant_id}: ";
        
        if (!$inventory || $inventory->quantity < $item->quantity) {
            $hasStock = false;
            $availableQty = $inventory ? $inventory->quantity : 0;
            echo "❌ Không đủ hàng (Cần: {$item->quantity}, Có: {$availableQty})\n";
        } else {
            echo "✅ Đủ hàng (Cần: {$item->quantity}, Có: {$inventory->quantity})\n";
        }
    }
    
    if ($hasStock) {
        echo "\n✅ Đủ hàng tại kho đích. Cập nhật fulfillment...\n";
        
        // Cập nhật cả vị trí kho và trạng thái
        $fulfillment->update([
            'store_location_id' => $destinationLocation->id,
            'status' => 'packed'
        ]);
        
        echo "✅ Đã cập nhật fulfillment thành công!\n";
        echo "- Kho mới: {$destinationLocation->id}\n";
        echo "- Trạng thái mới: packed\n";
        
        // Kiểm tra lại
        $fulfillment->refresh();
        echo "\n=== FULFILLMENT SAU KHI CẬP NHẬT ===\n";
        echo "ID: {$fulfillment->id}\n";
        echo "Trạng thái: {$fulfillment->status}\n";
        echo "Kho: {$fulfillment->store_location_id}\n";
        echo "Cập nhật lần cuối: {$fulfillment->updated_at}\n";
        
    } else {
        echo "\n❌ Không đủ hàng tại kho đích. Không cập nhật fulfillment.\n";
    }
} else {
    echo "Kho đích không cùng tỉnh với địa chỉ giao hàng. Không cập nhật fulfillment.\n";
}

echo "\n=== HOÀN THÀNH ===\n";