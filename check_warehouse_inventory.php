<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductInventory;
use App\Models\StoreLocation;

echo "Kiểm tra sản phẩm có tồn kho ở warehouse:\n";

// Tìm sản phẩm có tồn kho ở warehouse
$warehouseInventory = ProductInventory::where('quantity', '>', 2)
    ->whereHas('storeLocation', function($query) {
        $query->where('type', 'warehouse')
              ->where('is_active', true);
    })
    ->with(['productVariant', 'storeLocation'])
    ->first();

if ($warehouseInventory) {
    echo "Tìm thấy sản phẩm ở warehouse:\n";
    echo "SKU: {$warehouseInventory->productVariant->sku}\n";
    echo "Quantity: {$warehouseInventory->quantity}\n";
    echo "Warehouse: {$warehouseInventory->storeLocation->name}\n";
    echo "Location ID: {$warehouseInventory->store_location_id}\n";
} else {
    echo "Không tìm thấy sản phẩm nào có tồn kho ở warehouse\n";
    
    // Kiểm tra tất cả inventory ở warehouse
    echo "\nTất cả inventory ở warehouse:\n";
    $allWarehouseInventory = ProductInventory::whereHas('storeLocation', function($query) {
        $query->where('type', 'warehouse')
              ->where('is_active', true);
    })
    ->with(['productVariant', 'storeLocation'])
    ->get();
    
    foreach ($allWarehouseInventory as $inv) {
        echo "SKU: {$inv->productVariant->sku}, Qty: {$inv->quantity}, Warehouse: {$inv->storeLocation->name}\n";
    }
}