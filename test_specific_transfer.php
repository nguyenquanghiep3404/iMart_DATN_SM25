<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== TEST LOGIC CẬP NHẬT FULFILLMENT ===\n\n";

// Test với phiếu chuyển kho cụ thể đã received
$transferCode = 'AUTO-IM250826000076IVVO-0AFB';
$transfer = StockTransfer::where('transfer_code', $transferCode)
    ->with(['fromLocation', 'toLocation', 'items'])
    ->first();

if (!$transfer) {
    echo "Không tìm thấy phiếu chuyển kho {$transferCode}\n";
    exit;
}

echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
echo "Trạng thái: {$transfer->status}\n";
echo "Từ kho: {$transfer->fromLocation->name} (Tỉnh: {$transfer->fromLocation->province_code})\n";
echo "Đến kho: {$transfer->toLocation->name} (Tỉnh: {$transfer->toLocation->province_code})\n";
echo "Ghi chú: {$transfer->notes}\n\n";

// Tìm đơn hàng liên quan
$orderCode = null;
if (preg_match('/Order:([A-Z0-9\-]+)/', $transfer->notes, $matches)) {
    $orderCode = $matches[1];
}

if (!$orderCode) {
    echo "Không tìm thấy mã đơn hàng trong ghi chú\n";
    exit;
}

echo "Mã đơn hàng từ ghi chú: {$orderCode}\n";

$order = Order::where('order_code', $orderCode)
    ->with(['fulfillments.storeLocation', 'fulfillments.items.orderItem.productVariant'])
    ->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng {$orderCode}\n";
    exit;
}

echo "Đơn hàng: {$order->order_code}\n";
echo "Trạng thái đơn hàng: {$order->status}\n";
echo "Địa chỉ giao hàng (tỉnh): {$order->shipping_old_province_code}\n\n";

// Kiểm tra điều kiện cùng tỉnh
$destinationProvince = $transfer->toLocation->province_code;
$shippingProvince = $order->shipping_old_province_code;
$sameProvince = $destinationProvince === $shippingProvince;

echo "Kho đích (tỉnh): {$destinationProvince}\n";
echo "Địa chỉ giao hàng (tỉnh): {$shippingProvince}\n";
echo "Cùng tỉnh: " . ($sameProvince ? 'Có' : 'Không') . "\n\n";

if (!$sameProvince) {
    echo "Không cùng tỉnh - Logic không cập nhật fulfillment\n";
    exit;
}

echo "=== KIỂM TRA FULFILLMENTS ===\n";
foreach ($order->fulfillments as $fulfillment) {
    echo "Fulfillment ID: {$fulfillment->id}\n";
    echo "Trạng thái: {$fulfillment->status}\n";
    echo "Kho hiện tại: {$fulfillment->storeLocation->name} (ID: {$fulfillment->store_location_id})\n";
    echo "Kho đích: {$transfer->toLocation->name} (ID: {$transfer->to_location_id})\n";
    
    if ($fulfillment->status === 'processing') {
        echo "*** FULFILLMENT VẪN Ở TRẠNG THÁI PROCESSING ***\n";
        
        // Kiểm tra tồn kho tại kho đích
        echo "\nKiểm tra tồn kho tại kho đích...\n";
        $hasEnoughStock = true;
        
        foreach ($fulfillment->items as $item) {
            $productVariantId = $item->orderItem->product_variant_id;
            $inventory = \App\Models\ProductInventory::where('product_variant_id', $productVariantId)
                ->where('store_location_id', $transfer->to_location_id)
                ->where('inventory_type', 'new')
                ->first();
            
            $availableQty = $inventory ? $inventory->quantity : 0;
            $requiredQty = $item->quantity;
            
            echo "Sản phẩm {$item->orderItem->productVariant->sku}:\n";
            echo "  Cần: {$requiredQty}\n";
            echo "  Có: {$availableQty}\n";
            echo "  Đủ: " . ($availableQty >= $requiredQty ? 'Có' : 'Không') . "\n";
            
            if ($availableQty < $requiredQty) {
                $hasEnoughStock = false;
            }
        }
        
        echo "\nTổng kết tồn kho: " . ($hasEnoughStock ? 'Đủ hàng' : 'Không đủ hàng') . "\n";
        
        if ($hasEnoughStock) {
            echo "\n*** VẤN ĐỀ: Đủ điều kiện nhưng fulfillment chưa được cập nhật ***\n";
            echo "Các nguyên nhân có thể:\n";
            echo "1. Logic updateRelatedFulfillmentStatus không được gọi khi receive\n";
            echo "2. Có lỗi trong quá trình cập nhật database\n";
            echo "3. Điều kiện kiểm tra trong code không chính xác\n";
            
            // Thử gọi trực tiếp logic cập nhật
            echo "\nThử gọi trực tiếp logic cập nhật...\n";
            $workflowService = new StockTransferWorkflowService();
            
            try {
                // Gọi method private thông qua reflection
                $reflection = new \ReflectionClass($workflowService);
                $method = $reflection->getMethod('updateRelatedFulfillmentStatus');
                $method->setAccessible(true);
                
                $method->invoke($workflowService, $transfer);
                
                // Kiểm tra lại trạng thái
                $fulfillment->refresh();
                echo "Trạng thái fulfillment sau khi gọi updateRelatedFulfillmentStatus: {$fulfillment->status}\n";
                
                if ($fulfillment->status === 'packed') {
                    echo "✓ Logic hoạt động bình thường - Fulfillment đã được cập nhật\n";
                    echo "*** VẤN ĐỀ: Logic không được gọi tự động khi receive transfer ***\n";
                } else {
                    echo "✗ Logic không hoạt động - Cần kiểm tra chi tiết hơn\n";
                }
                
            } catch (Exception $e) {
                echo "Lỗi khi gọi updateRelatedFulfillmentStatus: {$e->getMessage()}\n";
            }
        } else {
            echo "\nLý do hợp lệ: Không đủ hàng tại kho đích\n";
        }
    } else {
        echo "Fulfillment đã ở trạng thái: {$fulfillment->status}\n";
    }
    
    echo "---\n";
}

echo "\n=== KẾT THÚC TEST ===\n";