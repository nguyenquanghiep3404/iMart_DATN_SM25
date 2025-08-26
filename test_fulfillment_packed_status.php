<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\Log;

echo "=== Test Fulfillment Packed Status Update ===\n\n";

try {
    // Tìm một đơn hàng có fulfillment và phiếu chuyển kho
    $order = Order::with(['fulfillments', 'fulfillments.storeLocation'])
        ->where('status', 'processing')
        ->whereHas('fulfillments', function($q) {
            $q->where('status', 'received');
        })
        ->first();
    
    if (!$order) {
        echo "Không tìm thấy đơn hàng có fulfillment với trạng thái 'received'\n";
        
        // Tìm bất kỳ đơn hàng nào có fulfillment
        $order = Order::with(['fulfillments', 'fulfillments.storeLocation'])
            ->whereHas('fulfillments')
            ->first();
            
        if (!$order) {
            echo "Không tìm thấy đơn hàng nào có fulfillment\n";
            exit;
        }
    }
    
    echo "Đơn hàng test: {$order->order_code} (ID: {$order->id})\n";
    echo "Trạng thái đơn hàng: {$order->status}\n";
    echo "Số fulfillments: " . $order->fulfillments->count() . "\n\n";
    
    foreach ($order->fulfillments as $fulfillment) {
        echo "Fulfillment ID: {$fulfillment->id}\n";
        echo "Tracking Code: {$fulfillment->tracking_code}\n";
        echo "Trạng thái hiện tại: {$fulfillment->status}\n";
        echo "Kho hiện tại: {$fulfillment->store_location_id}\n";
        
        if ($fulfillment->storeLocation) {
            echo "Tên kho: {$fulfillment->storeLocation->name}\n";
            echo "Mã tỉnh kho: {$fulfillment->storeLocation->province_code}\n";
        }
        
        echo "Mã tỉnh giao hàng: {$order->shipping_old_province_code}\n";
        echo "\n";
    }
    
    // Tìm phiếu chuyển kho liên quan
    $stockTransfers = StockTransfer::where('notes', 'LIKE', '%Order:' . $order->order_code . '%')
        ->get();
    
    echo "Số phiếu chuyển kho liên quan: " . $stockTransfers->count() . "\n\n";
    
    foreach ($stockTransfers as $transfer) {
        echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
        echo "Trạng thái: {$transfer->status}\n";
        echo "Từ kho: {$transfer->from_location_id}\n";
        echo "Đến kho: {$transfer->to_location_id}\n";
        echo "Ghi chú: {$transfer->notes}\n";
        
        if ($transfer->status === 'received') {
            echo "\n--- Test logic cập nhật fulfillment status ---\n";
            
            $workflowService = new StockTransferWorkflowService();
            
            // Gọi phương thức private thông qua reflection
            $reflection = new ReflectionClass($workflowService);
            $method = $reflection->getMethod('updateRelatedFulfillmentStatus');
            $method->setAccessible(true);
            
            echo "Gọi updateRelatedFulfillmentStatus...\n";
            $method->invoke($workflowService, $transfer);
            
            // Kiểm tra trạng thái fulfillment sau khi cập nhật
            $order->refresh();
            foreach ($order->fulfillments as $fulfillment) {
                echo "Fulfillment {$fulfillment->id} - Trạng thái sau cập nhật: {$fulfillment->status}\n";
            }
        }
        
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Kết thúc test ===\n";