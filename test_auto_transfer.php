<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;

echo "=== Test Auto Transfer with IMEI ===\n";

// Tìm phiếu chuyển kho tự động đang pending
$transfer = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
    ->where('status', 'pending')
    ->first();

if (!$transfer) {
    echo "No pending auto transfers found\n";
    exit;
}

echo "Testing transfer: {$transfer->transfer_code}\n";
echo "From: {$transfer->fromLocation->name}\n";
echo "To: {$transfer->toLocation->name}\n";
echo "Status: {$transfer->status}\n";

// Kiểm tra items và serial tracking
echo "\nItems in transfer:\n";
foreach ($transfer->items as $item) {
    echo "- {$item->productVariant->sku}: {$item->quantity} units";
    if ($item->productVariant->has_serial_tracking) {
        echo " (has serial tracking)";
        
        // Kiểm tra serial có sẵn tại kho nguồn
        $availableSerials = \App\Models\InventorySerial::where('product_variant_id', $item->product_variant_id)
            ->where('store_location_id', $transfer->from_location_id)
            ->where('status', 'available')
            ->count();
        echo " - Available serials: {$availableSerials}";
    }
    echo "\n";
}

// Test workflow service
echo "\nTesting StockTransferWorkflowService...\n";
$service = new StockTransferWorkflowService();

try {
    $result = $service->processTransferWorkflow($transfer);
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    // Reload transfer để xem trạng thái mới
    $transfer->refresh();
    echo "\nNew status: {$transfer->status}\n";
    
    // Kiểm tra serial đã được assign chưa
    if ($transfer->status === 'received') {
        echo "\nChecking serial assignments:\n";
        foreach ($transfer->items as $item) {
            if ($item->productVariant->has_serial_tracking) {
                $assignedSerials = $item->serials()->with('inventorySerial')->get();
                echo "- {$item->productVariant->sku}: {$assignedSerials->count()} serials assigned\n";
                foreach ($assignedSerials as $serial) {
                    echo "  * {$serial->inventorySerial->serial_number} (status: {$serial->status})\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";