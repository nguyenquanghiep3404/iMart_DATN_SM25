<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\StockTransfer;
use App\Models\OrderFulfillment;
use App\Models\Order;
use App\Services\StockTransferWorkflowService;
use App\Services\FulfillmentStockTransferService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Khởi tạo Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Same Province Fulfillment ===\n";

try {
    // Tìm đơn hàng DH-PEUVTBZDIX có fulfillment cùng tỉnh
    $order = Order::where('order_code', 'DH-PEUVTBZDIX')->first();
    
    if (!$order) {
        echo "Không tìm thấy đơn hàng DH-PEUVTBZDIX\n";
        exit;
    }
    
    echo "Tìm thấy đơn hàng: {$order->order_code}\n";
    echo "Trạng thái đơn hàng: {$order->status}\n";
    echo "Shipping Province: {$order->shipping_old_province_code}\n";
    
    // Tìm fulfillment
    $fulfillment = $order->fulfillments()->first();
    if (!$fulfillment) {
        echo "Không tìm thấy fulfillment cho đơn hàng này\n";
        exit;
    }
    
    echo "Fulfillment ID: {$fulfillment->id}\n";
    echo "Fulfillment Status: {$fulfillment->status}\n";
    echo "Store Location ID: {$fulfillment->store_location_id}\n";
    echo "Store Province: {$fulfillment->storeLocation->province_code}\n";
    
    // Kiểm tra xem có phiếu chuyển kho nào cho đơn hàng này không
    $existingTransfer = StockTransfer::where('notes', 'like', "%Order:{$order->order_code}%")->first();
    
    if ($existingTransfer) {
        echo "\nTìm thấy phiếu chuyển kho hiện có: {$existingTransfer->transfer_code}\n";
        echo "Trạng thái: {$existingTransfer->status}\n";
        
        if ($existingTransfer->status === 'pending') {
            echo "\n--- Test với phiếu chuyển kho hiện có ---\n";
            
            // Dispatch
            echo "Bước 1: Dispatch phiếu chuyển kho...\n";
            $existingTransfer->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
                'dispatched_by' => 1
            ]);
            
            // Receive
            echo "Bước 2: Receive phiếu chuyển kho...\n";
            $workflowService = new StockTransferWorkflowService();
            $result = $workflowService->receiveTransfer($existingTransfer);
            
            echo "Kết quả nhận hàng: " . json_encode($result) . "\n";
            
            // Kiểm tra trạng thái fulfillment
            $fulfillment->refresh();
            echo "Trạng thái fulfillment sau khi nhận hàng: {$fulfillment->status}\n";
            
        } else {
            echo "Phiếu chuyển kho không ở trạng thái pending: {$existingTransfer->status}\n";
        }
    } else {
        echo "\nKhông tìm thấy phiếu chuyển kho cho đơn hàng này\n";
        echo "Tạo phiếu chuyển kho mới...\n";
        
        // Tạo phiếu chuyển kho mới
        $transferService = new FulfillmentStockTransferService();
        $result = $transferService->checkAndCreateFulfillmentTransfers($order);
        echo "Kết quả tạo phiếu chuyển kho: " . json_encode($result) . "\n";
        
        echo "Đã tạo phiếu chuyển kho mới\n";
        
        // Tìm phiếu chuyển kho vừa tạo
        $newTransfer = StockTransfer::where('notes', 'like', "%Order:{$order->order_code}%")
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($newTransfer) {
            echo "Phiếu chuyển kho mới: {$newTransfer->transfer_code}\n";
            
            // Test workflow
            echo "\n--- Test workflow với phiếu chuyển kho mới ---\n";
            
            // Dispatch
            echo "Bước 1: Dispatch...\n";
            $newTransfer->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
                'dispatched_by' => 1
            ]);
            
            // Receive
            echo "Bước 2: Receive...\n";
            $workflowService = new StockTransferWorkflowService();
            $result = $workflowService->receiveTransfer($newTransfer);
            
            echo "Kết quả nhận hàng: " . json_encode($result) . "\n";
            
            // Kiểm tra trạng thái fulfillment
            $fulfillment->refresh();
            echo "Trạng thái fulfillment sau khi nhận hàng: {$fulfillment->status}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Kết thúc test ===\n";