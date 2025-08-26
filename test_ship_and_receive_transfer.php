<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== Test Ship and Receive Stock Transfer ===\n\n";

try {
    // Tìm phiếu chuyển kho pending
    $transfer = StockTransfer::where('status', 'pending')
        ->where('notes', 'LIKE', '%Order:%')
        ->first();
    
    if (!$transfer) {
        echo "Không tìm thấy phiếu chuyển kho pending\n";
        exit;
    }
    
    echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
    echo "Trạng thái hiện tại: {$transfer->status}\n";
    echo "Từ kho: {$transfer->from_location_id}\n";
    echo "Đến kho: {$transfer->to_location_id}\n";
    echo "Ghi chú: {$transfer->notes}\n\n";
    
    // Kiểm tra fulfillment trước khi xử lý
    preg_match('/Order:([^\s-]+)/', $transfer->notes, $matches);
    if (isset($matches[1])) {
        $orderCode = $matches[1];
        $order = \App\Models\Order::where('order_code', $orderCode)->first();
        
        if ($order) {
            echo "Đơn hàng liên quan: {$order->order_code}\n";
            echo "Mã tỉnh giao hàng: {$order->shipping_old_province_code}\n";
            
            // Kiểm tra kho đích
            $toLocation = \App\Models\StoreLocation::find($transfer->to_location_id);
            if ($toLocation) {
                echo "Kho đích: {$toLocation->name} (Mã tỉnh: {$toLocation->province_code})\n";
            }
            
            echo "Fulfillments trước khi xử lý:\n";
            foreach ($order->fulfillments as $fulfillment) {
                echo "  - Fulfillment {$fulfillment->id}: {$fulfillment->status} (Kho: {$fulfillment->store_location_id})\n";
            }
            echo "\n";
        }
    }
    
    // Bước 1: Xuất hàng (ship)
    echo "Bước 1: Xuất hàng...\n";
    DB::transaction(function () use ($transfer) {
        $transfer->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'shipped_by' => 1
        ]);
    });
    
    $transfer->refresh();
    echo "Trạng thái sau xuất hàng: {$transfer->status}\n\n";
    
    // Bước 2: Nhận hàng
    echo "Bước 2: Nhận hàng...\n";
    $workflowService = new StockTransferWorkflowService();
    $result = $workflowService->receiveTransfer($transfer);
    
    if ($result['success']) {
        echo "Nhận hàng thành công!\n\n";
        
        // Kiểm tra trạng thái sau khi nhận hàng
        $transfer->refresh();
        echo "Trạng thái phiếu chuyển kho sau nhận hàng: {$transfer->status}\n";
        
        if (isset($order)) {
            $order->refresh();
            echo "Fulfillments sau khi nhận hàng:\n";
            foreach ($order->fulfillments as $fulfillment) {
                echo "  - Fulfillment {$fulfillment->id}: {$fulfillment->status} (Kho: {$fulfillment->store_location_id})\n";
            }
        }
    } else {
        echo "Lỗi nhận hàng: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Kết thúc test ===\n";