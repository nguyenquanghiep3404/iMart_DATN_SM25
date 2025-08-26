<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\StockTransfer;
use App\Models\OrderFulfillment;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Khởi tạo Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Dispatch and Receive Transfer ===\n";

try {
    // Tìm phiếu chuyển kho đang ở trạng thái 'pending' và có ghi chú chứa 'Order:'
    $stockTransfer = StockTransfer::where('status', 'pending')
        ->where('notes', 'like', '%Order:%')
        ->first();
    
    if (!$stockTransfer) {
        echo "Không tìm thấy phiếu chuyển kho ở trạng thái 'pending' với ghi chú chứa 'Order:'\n";
        exit;
    }
    
    echo "Tìm thấy phiếu chuyển kho: {$stockTransfer->transfer_code}\n";
    echo "Trạng thái hiện tại: {$stockTransfer->status}\n";
    echo "Ghi chú: {$stockTransfer->notes}\n";
    
    // Lấy thông tin đơn hàng từ ghi chú
    preg_match('/Order:([A-Z0-9-]+)/', $stockTransfer->notes, $matches);
    $orderCode = $matches[1] ?? null;
    
    if ($orderCode) {
        echo "Đơn hàng liên quan: {$orderCode}\n";
        
        // Tìm fulfillment của đơn hàng này
        $fulfillment = OrderFulfillment::whereHas('order', function($query) use ($orderCode) {
            $query->where('order_code', $orderCode);
        })->first();
        
        if ($fulfillment) {
            echo "Fulfillment ID: {$fulfillment->id}, Trạng thái: {$fulfillment->status}\n";
        }
    }
    
    // Bước 1: Cập nhật trạng thái thành 'dispatched' (không phải 'shipped')
    echo "\n--- Bước 1: Cập nhật trạng thái thành 'dispatched' ---\n";
    $stockTransfer->update([
        'status' => 'dispatched',
        'dispatched_at' => now(),
        'dispatched_by' => 1
    ]);
    
    echo "Đã cập nhật trạng thái phiếu chuyển kho thành 'dispatched'\n";
    
    // Bước 2: Nhận hàng
    echo "\n--- Bước 2: Nhận hàng ---\n";
    $workflowService = new StockTransferWorkflowService();
    
    try {
        $workflowService->receiveTransfer($stockTransfer);
        echo "Đã nhận hàng thành công!\n";
        
        // Kiểm tra trạng thái sau khi nhận
        $stockTransfer->refresh();
        echo "Trạng thái phiếu chuyển kho sau khi nhận: {$stockTransfer->status}\n";
        
        // Kiểm tra trạng thái fulfillment
        if ($fulfillment) {
            $fulfillment->refresh();
            echo "Trạng thái fulfillment sau khi nhận hàng: {$fulfillment->status}\n";
        }
        
    } catch (Exception $e) {
        echo "Lỗi khi nhận hàng: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Kết thúc test ===\n";