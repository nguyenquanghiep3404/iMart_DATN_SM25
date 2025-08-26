<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use Illuminate\Support\Str;

echo "Creating test transfer for UI testing..." . PHP_EOL;

// Tìm sản phẩm và kho
$productVariant = ProductVariant::where('sku', 'fgthy')->first();
$fromLocation = StoreLocation::where('name', 'hưng yên')->first();
$toLocation = StoreLocation::where('name', 'hà nội')->first();

if (!$productVariant || !$fromLocation || !$toLocation) {
    echo "Missing required data!" . PHP_EOL;
    exit(1);
}

// Tạo phiếu chuyển kho mới
$transfer = StockTransfer::create([
    'transfer_code' => 'FULFILL-TEST-' . strtoupper(Str::random(8)),
    'from_location_id' => $fromLocation->id,
    'to_location_id' => $toLocation->id,
    'status' => 'dispatched',
    'notes' => 'Test transfer for UI testing',
    'created_by' => 1,
    'dispatched_at' => now(),
]);

// Tạo item cho phiếu chuyển kho
StockTransferItem::create([
    'stock_transfer_id' => $transfer->id,
    'product_variant_id' => $productVariant->id,
    'quantity' => 1,
    'notes' => 'Test item'
]);

echo "Created transfer:" . PHP_EOL;
echo "ID: {$transfer->id}" . PHP_EOL;
echo "Code: {$transfer->transfer_code}" . PHP_EOL;
echo "Status: {$transfer->status}" . PHP_EOL;
echo "From: {$fromLocation->name}" . PHP_EOL;
echo "To: {$toLocation->name}" . PHP_EOL;
echo "\nYou can now test the receive function on the UI with this transfer." . PHP_EOL;