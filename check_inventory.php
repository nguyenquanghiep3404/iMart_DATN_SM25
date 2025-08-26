<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductInventory;
use App\Models\StoreLocation;

echo "Kiểm tra tồn kho cho product_variant_id = 1:\n";

$inventories = ProductInventory::where('product_variant_id', 1)
    ->with(['productVariant', 'storeLocation'])
    ->get();

foreach ($inventories as $inventory) {
    echo "SKU: {$inventory->productVariant->sku}\n";
    echo "Quantity: {$inventory->quantity}\n";
    echo "Store: {$inventory->storeLocation->name}\n";
    echo "Type: {$inventory->storeLocation->type}\n";
    echo "Active: " . ($inventory->storeLocation->is_active ? 'Yes' : 'No') . "\n";
    echo "Inventory Type: {$inventory->inventory_type}\n";
    echo "---\n";
}

echo "\nKiểm tra store locations có type = 'warehouse':\n";
$warehouses = StoreLocation::where('type', 'warehouse')
    ->where('is_active', true)
    ->get();

foreach ($warehouses as $warehouse) {
    echo "Warehouse: {$warehouse->name} (ID: {$warehouse->id})\n";
}