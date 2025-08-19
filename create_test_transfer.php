<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use Illuminate\Support\Facades\DB;

echo "=== Create Test Transfer with IMEI ===\n";

// Tìm sản phẩm có IMEI tracking
$product = ProductVariant::where('sku', 'ffggfgfgggdsffd')
    ->where('has_serial_tracking', 1)
    ->first();

if (!$product) {
    echo "Product with IMEI tracking not found\n";
    exit;
}

// Tìm kho nguồn và đích
$fromLocation = StoreLocation::where('name', 'LIKE', '%ha noi%')->first();
$toLocation = StoreLocation::where('name', 'LIKE', '%sơn la%')->first();

if (!$fromLocation || !$toLocation) {
    echo "Source or destination location not found\n";
    exit;
}

echo "Product: {$product->sku}\n";
echo "From: {$fromLocation->name} (ID: {$fromLocation->id})\n";
echo "To: {$toLocation->name} (ID: {$toLocation->id})\n";

// Tạo phiếu chuyển kho mới
$transferCode = 'AUTO-' . strtoupper(uniqid());

DB::beginTransaction();
try {
    $transfer = StockTransfer::create([
        'transfer_code' => $transferCode,
        'from_location_id' => $fromLocation->id,
        'to_location_id' => $toLocation->id,
        'status' => 'pending',
        'created_by' => 1,
        'notes' => 'Test transfer for IMEI tracking'
    ]);
    
    // Thêm item
    StockTransferItem::create([
        'stock_transfer_id' => $transfer->id,
        'product_variant_id' => $product->id,
        'quantity' => 2 // Test với 2 sản phẩm
    ]);
    
    DB::commit();
    
    echo "\nCreated transfer: {$transferCode}\n";
    echo "Transfer ID: {$transfer->id}\n";
    echo "Status: {$transfer->status}\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "Error creating transfer: {$e->getMessage()}\n";
}

echo "\n=== Test transfer created ===\n";