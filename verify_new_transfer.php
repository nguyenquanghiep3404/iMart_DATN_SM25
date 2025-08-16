<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\InventorySerial;
use App\Models\StockTransferItemSerial;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

echo "=== Verify IMEI Tracking for New Transfer ===\n";

// Tìm phiếu chuyển kho vừa xử lý
$transfer = StockTransfer::where('transfer_code', 'AUTO-689F626BF3186')
    ->with(['items.productVariant', 'fromLocation', 'toLocation'])
    ->first();

if (!$transfer) {
    echo "Transfer not found\n";
    exit;
}

echo "\nTransfer: {$transfer->transfer_code}\n";
echo "Status: {$transfer->status}\n";
echo "From: {$transfer->fromLocation->name}\n";
echo "To: {$transfer->toLocation->name}\n";
echo "Dispatched at: {$transfer->dispatched_at}\n";

echo "\n=== Items and IMEI Tracking ===\n";
foreach ($transfer->items as $item) {
    echo "\nProduct: {$item->productVariant->sku}\n";
    echo "Quantity: {$item->quantity}\n";
    
    // Kiểm tra serial tracking
    if ($item->productVariant->has_serial_tracking) {
        echo "Has serial tracking: YES\n";
        
        // Kiểm tra serials đã được gán
        $assignedSerials = StockTransferItemSerial::where('stock_transfer_item_id', $item->id)
            ->with('inventorySerial')
            ->get();
            
        echo "Assigned serials: {$assignedSerials->count()}\n";
        
        foreach ($assignedSerials as $assignedSerial) {
            $serial = $assignedSerial->inventorySerial;
            echo "  - IMEI: {$serial->serial_number}\n";
            echo "    Status in inventory_serials: {$serial->status}\n";
            echo "    Status in transfer: {$assignedSerial->status}\n";
            echo "    Location: {$serial->store_location_id}\n";
        }
        
        // Kiểm tra serials còn available tại kho nguồn
        $remainingSerials = InventorySerial::where('product_variant_id', $item->product_variant_id)
            ->where('store_location_id', $transfer->from_location_id)
            ->where('status', 'available')
            ->count();
            
        echo "Remaining available serials at source: {$remainingSerials}\n";
    } else {
        echo "Has serial tracking: NO\n";
    }
}

// Kiểm tra inventory movements
echo "\n=== Inventory Movements ===\n";
$movements = InventoryMovement::where('reference_type', 'stock_transfer')
    ->where('reference_id', $transfer->id)
    ->with('productVariant')
    ->get();
    
foreach ($movements as $movement) {
    echo "\nProduct: {$movement->productVariant->sku}\n";
    echo "Store: {$movement->store_location_id}\n";
    echo "Type: {$movement->inventory_type}\n";
    echo "Quantity change: {$movement->quantity_change}\n";
    echo "Quantity after: {$movement->quantity_after_change}\n";
    echo "Reason: {$movement->reason}\n";
    echo "Notes: {$movement->notes}\n";
}

echo "\n=== Verification completed ===\n";