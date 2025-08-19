<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Services\AutoStockTransferService;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG LOGIC CHUYỂN KHO TỰ ĐỘNG ===\n\n";

// Lấy đơn hàng gần đây nhất
$order = Order::with(['items.productVariant', 'fulfillments'])
    ->where('status', 'processing')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng processing nào.\n";
    exit;
}

echo "Đơn hàng test: {$order->order_code}\n";
echo "Trạng thái: {$order->status}\n";
echo "Tỉnh giao hàng: {$order->shipping_old_province_code}\n";
echo "Số sản phẩm: " . $order->items->count() . "\n\n";

// Kiểm tra từng sản phẩm
foreach ($order->items as $item) {
    echo "Sản phẩm: {$item->product_name} (SKU: {$item->sku})\n";
    echo "Số lượng: {$item->quantity}\n";
    echo "Product Variant ID: {$item->product_variant_id}\n";
    
    // Kiểm tra tồn kho warehouse
    $warehouseStock = \App\Models\ProductInventory::where('product_variant_id', $item->product_variant_id)
        ->where('inventory_type', 'new')
        ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
        ->where('store_locations.type', 'warehouse')
        ->where('store_locations.is_active', true)
        ->select('product_inventories.quantity', 'store_locations.name', 'store_locations.province_code')
        ->get();
    
    echo "Tồn kho warehouse:\n";
    foreach ($warehouseStock as $stock) {
        echo "  - {$stock->name} (Tỉnh: {$stock->province_code}): {$stock->quantity}\n";
    }
    
    // Kiểm tra tồn kho store
    $storeStock = \App\Models\ProductInventory::where('product_variant_id', $item->product_variant_id)
        ->where('inventory_type', 'new')
        ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
        ->where('store_locations.type', 'store')
        ->where('store_locations.is_active', true)
        ->select('product_inventories.quantity', 'store_locations.name', 'store_locations.province_code')
        ->get();
    
    echo "Tồn kho store:\n";
    foreach ($storeStock as $stock) {
        echo "  - {$stock->name} (Tỉnh: {$stock->province_code}): {$stock->quantity}\n";
    }
    echo "\n";
}

// Test logic tạo phiếu chuyển kho
echo "=== TEST LOGIC TẠO PHIẾU CHUYỂN KHO ===\n";
$autoTransferService = new AutoStockTransferService();
$result = $autoTransferService->checkAndCreateAutoTransfer($order);

echo "Kết quả: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== KẾT THÚC DEBUG ===\n";