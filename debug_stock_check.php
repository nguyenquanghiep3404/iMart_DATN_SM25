<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\ProductInventory;
use App\Services\StockTransferWorkflowService;

echo "=== DEBUG STOCK CHECK ===\n\n";

// Lấy fulfillment ID 32
$fulfillment = OrderFulfillment::with(['items.orderItem.productVariant'])->find(32);

if (!$fulfillment) {
    echo "Không tìm thấy fulfillment ID 32\n";
    exit;
}

echo "Fulfillment ID: {$fulfillment->id}\n";
echo "Store Location: {$fulfillment->store_location_id}\n";
echo "Status: {$fulfillment->status}\n\n";

// Kiểm tra tồn kho tại kho đích (kho 6)
$warehouseId = 6;
echo "=== KIỂM TRA TỒN KHO TẠI KHO {$warehouseId} ===\n";

$hasStock = true;
foreach ($fulfillment->items as $item) {
    $productVariantId = $item->orderItem->product_variant_id;
    $inventory = ProductInventory::where('product_variant_id', $productVariantId)
        ->where('store_location_id', $warehouseId)
        ->where('inventory_type', 'new')
        ->first();
        
    $availableQty = $inventory ? $inventory->quantity : 0;
    $requiredQty = $item->quantity;
    
    echo "Sản phẩm {$item->orderItem->productVariant->sku}:\n";
    echo "  Product Variant ID: {$productVariantId}\n";
    echo "  Cần: {$requiredQty}\n";
    echo "  Có: {$availableQty}\n";
    echo "  Đủ: " . ($availableQty >= $requiredQty ? 'Có' : 'Không') . "\n";
    
    if ($inventory) {
        echo "  Inventory ID: {$inventory->id}\n";
        echo "  Inventory Type: {$inventory->inventory_type}\n";
    } else {
        echo "  Không tìm thấy inventory record\n";
    }
    echo "\n";
    
    if ($availableQty < $requiredQty) {
        $hasStock = false;
    }
}

echo "Tổng kết tồn kho: " . ($hasStock ? 'Đủ hàng' : 'Thiếu hàng') . "\n\n";

// Test logic checkFulfillmentStock
echo "=== TEST LOGIC checkFulfillmentStock ===\n";
$service = new StockTransferWorkflowService();

try {
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('checkFulfillmentStock');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $fulfillment, $warehouseId);
    echo "Kết quả checkFulfillmentStock: " . ($result ? 'true (đủ hàng)' : 'false (thiếu hàng)') . "\n";
    
    if ($result !== $hasStock) {
        echo "⚠️ CẢNH BÁO: Kết quả không khớp với tính toán thủ công!\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi khi gọi checkFulfillmentStock: {$e->getMessage()}\n";
}

echo "\n=== KẾT THÚC DEBUG ===\n";