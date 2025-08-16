<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\InventorySerial;
use Illuminate\Support\Facades\DB;

echo "=== Check Product Serial Tracking ===\n";

// Tìm sản phẩm ffggfgfgggdsffd
$product = ProductVariant::where('sku', 'ffggfgfgggdsffd')->first();

if (!$product) {
    echo "Product ffggfgfgggdsffd not found\n";
    exit;
}

echo "\nProduct: {$product->sku}\n";
echo "ID: {$product->id}\n";
echo "Has serial tracking: {$product->has_serial_tracking}\n";

// Kiểm tra cấu trúc bảng product_variants
echo "\n=== Product Variants Table Structure ===\n";
$table = DB::select('DESCRIBE product_variants');

foreach($table as $column) {
    if (strpos($column->Field, 'serial') !== false || strpos($column->Field, 'track') !== false) {
        echo "- {$column->Field}: {$column->Type}";
        if ($column->Null === 'NO') echo " NOT NULL";
        if ($column->Default !== null) echo " DEFAULT '{$column->Default}'";
        echo "\n";
    }
}

// Kiểm tra các sản phẩm có serial tracking
echo "\n=== Products with Serial Tracking ===\n";
$serialProducts = ProductVariant::where('has_serial_tracking', 1)
    ->get(['id', 'sku', 'has_serial_tracking']);
    
foreach($serialProducts as $sp) {
    echo "- {$sp->sku}: has_serial_tracking={$sp->has_serial_tracking}\n";
}

// Kiểm tra inventory serials cho sản phẩm này
echo "\n=== Inventory Serials for this product ===\n";
$serials = InventorySerial::where('product_variant_id', $product->id)
    ->get(['id', 'serial_number', 'status', 'store_location_id']);
    
echo "Found {$serials->count()} serials:\n";
foreach($serials as $serial) {
    echo "- IMEI: {$serial->serial_number}, Status: {$serial->status}, Location: {$serial->store_location_id}\n";
}

echo "\n=== Check completed ===\n";