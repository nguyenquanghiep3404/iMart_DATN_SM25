<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;

echo "=== Test New Auto Transfer with IMEI ===\n";

// Tìm phiếu chuyển kho vừa tạo
$transfer = StockTransfer::where('transfer_code', 'AUTO-689F626BF3186')
    ->with(['items.productVariant', 'fromLocation', 'toLocation'])
    ->first();

if (!$transfer) {
    echo "Transfer not found\n";
    exit;
}

echo "\nTesting transfer: {$transfer->transfer_code}\n";
echo "From: {$transfer->fromLocation->name}\n";
echo "To: {$transfer->toLocation->name}\n";
echo "Status: {$transfer->status}\n";

echo "\nItems in transfer:\n";
foreach ($transfer->items as $item) {
    $hasSerial = $item->productVariant->has_serial_tracking ? 'has serial tracking' : 'no serial tracking';
    echo "- {$item->productVariant->sku}: {$item->quantity} units ({$hasSerial})\n";
    
    if ($item->productVariant->has_serial_tracking) {
        $availableSerials = \App\Models\InventorySerial::where('product_variant_id', $item->product_variant_id)
            ->where('store_location_id', $transfer->from_location_id)
            ->where('status', 'available')
            ->count();
        echo "  - Available serials: {$availableSerials}\n";
    }
}

echo "\nTesting StockTransferWorkflowService...\n";
$workflowService = new StockTransferWorkflowService();

try {
    $result = $workflowService->processTransferWorkflow($transfer);
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

// Kiểm tra trạng thái mới
$transfer->refresh();
echo "\nNew status: {$transfer->status}\n";

echo "\n=== Test completed ===\n";