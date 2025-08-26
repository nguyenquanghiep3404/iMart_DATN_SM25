<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Services\FulfillmentStockTransferService;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== PHÂN TÍCH LUỒNG CHUYỂN KHO TỰ ĐỘNG ===\n\n";

// 1. Tìm một fulfillment có trạng thái processing và có phiếu chuyển kho
echo "1. Tìm fulfillment có phiếu chuyển kho...\n";
$fulfillment = OrderFulfillment::where('status', 'processing')
    ->with(['order', 'storeLocation', 'order.shippingOldProvince'])
    ->first();

if ($fulfillment) {
    // Tìm phiếu chuyển kho liên quan thông qua ghi chú
    $stockTransfers = StockTransfer::where('notes', 'like', '%Order:' . $fulfillment->order->order_code . '%')
        ->with(['fromLocation', 'toLocation', 'items'])
        ->get();
    
    if ($stockTransfers->isEmpty()) {
        echo "Fulfillment tìm thấy nhưng không có phiếu chuyển kho liên quan\n";
        $fulfillment = null;
    }
}

if (!$fulfillment) {
    echo "Không tìm thấy fulfillment có phiếu chuyển kho\n";
    exit;
}

echo "Tìm thấy fulfillment ID: {$fulfillment->id}\n";
echo "Đơn hàng: {$fulfillment->order->order_code}\n";
echo "Trạng thái fulfillment: {$fulfillment->status}\n";
echo "Kho nguồn: {$fulfillment->storeLocation->name} (Tỉnh: {$fulfillment->storeLocation->province_code})\n";
echo "Địa chỉ giao hàng: {$fulfillment->order->shipping_old_province_code}\n\n";

// 2. Kiểm tra các phiếu chuyển kho liên quan
echo "2. Kiểm tra phiếu chuyển kho liên quan...\n";
foreach ($stockTransfers as $transfer) {
    echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
    echo "Trạng thái: {$transfer->status}\n";
    echo "Từ kho: {$transfer->fromLocation->name} (ID: {$transfer->from_location_id})\n";
    echo "Đến kho: {$transfer->toLocation->name} (ID: {$transfer->to_location_id})\n";
    echo "Ghi chú: {$transfer->notes}\n";
    echo "Ngày tạo: {$transfer->created_at}\n";
    echo "Ngày gửi: {$transfer->shipped_at}\n";
    echo "Ngày nhận: {$transfer->received_at}\n";
    echo "---\n";
}

// 3. Kiểm tra logic cập nhật trạng thái
echo "\n3. Kiểm tra logic cập nhật trạng thái...\n";
$workflowService = new StockTransferWorkflowService();

// Tìm phiếu chuyển kho có trạng thái 'received'
$receivedTransfer = $stockTransfers->where('status', 'received')->first();
if ($receivedTransfer) {
    echo "Tìm thấy phiếu chuyển kho đã nhận: {$receivedTransfer->transfer_code}\n";
    
    // Sử dụng đơn hàng hiện tại
    $relatedOrder = $fulfillment->order;
    echo "Đơn hàng hiện tại: {$relatedOrder->order_code}\n";
    
    // Kiểm tra fulfillment của đơn hàng này
    $relatedFulfillments = $relatedOrder->fulfillments;
    echo "Số lượng fulfillment: {$relatedFulfillments->count()}\n";
    
    foreach ($relatedFulfillments as $rf) {
            echo "Fulfillment ID: {$rf->id}, Trạng thái: {$rf->status}\n";
            echo "Kho nguồn: {$rf->storeLocation->name} (Tỉnh: {$rf->storeLocation->province_code})\n";
            
            // Kiểm tra kho đích có cùng tỉnh với địa chỉ giao hàng không
            $destinationLocation = StoreLocation::find($receivedTransfer->to_location_id);
            $shippingProvince = $relatedOrder->shipping_old_province_code;
            
            echo "Kho đích: {$destinationLocation->name} (Tỉnh: {$destinationLocation->province_code})\n";
            echo "Tỉnh giao hàng: {$shippingProvince}\n";
            
            $sameProvince = $destinationLocation->province_code === $shippingProvince;
            echo "Cùng tỉnh: " . ($sameProvince ? 'Có' : 'Không') . "\n";
            
            if ($sameProvince) {
                echo "Điều kiện cùng tỉnh đã thỏa mãn\n";
                
                // Kiểm tra tồn kho tại kho đích
                echo "Kiểm tra tồn kho tại kho đích...\n";
                $hasStock = true; // Giả định có đủ hàng
                
                foreach ($rf->items as $item) {
                    $inventory = \App\Models\ProductInventory::where('product_variant_id', $item->product_variant_id)
                        ->where('store_location_id', $destinationLocation->id)
                        ->where('inventory_type', 'new')
                        ->first();
                    
                    $availableQty = $inventory ? $inventory->quantity : 0;
                    echo "Sản phẩm {$item->productVariant->sku}: Cần {$item->quantity}, Có {$availableQty}\n";
                    
                    if ($availableQty < $item->quantity) {
                        $hasStock = false;
                    }
                }
                
                echo "Đủ hàng tại kho đích: " . ($hasStock ? 'Có' : 'Không') . "\n";
                
                if ($hasStock && $rf->status === 'processing') {
                    echo "Điều kiện để cập nhật thành 'packed' đã thỏa mãn\n";
                    echo "Lý do fulfillment chưa được cập nhật: Cần kiểm tra logic trong updateRelatedFulfillmentStatus\n";
                } else {
                    echo "Không thể cập nhật thành 'packed': ";
                    if (!$hasStock) echo "Không đủ hàng ";
                    if ($rf->status !== 'processing') echo "Trạng thái không phải 'processing' ";
                    echo "\n";
                }
            }
            echo "---\n";
        }
} else {
    echo "Không tìm thấy phiếu chuyển kho có trạng thái 'received'\n";
    
    // Kiểm tra phiếu chuyển kho pending hoặc dispatched
    $pendingTransfer = $stockTransfers->whereIn('status', ['pending', 'dispatched'])->first();
    if ($pendingTransfer) {
        echo "Tìm thấy phiếu chuyển kho chưa hoàn thành: {$pendingTransfer->transfer_code} (Trạng thái: {$pendingTransfer->status})\n";
        echo "Cần hoàn thành quy trình dispatch và receive để cập nhật fulfillment\n";
        
        // Thử dispatch và receive tự động
        echo "\n4. Thử hoàn thành quy trình chuyển kho...\n";
        
        if ($pendingTransfer->status === 'pending') {
            echo "Dispatch phiếu chuyển kho...\n";
            $dispatchResult = $workflowService->dispatchTransfer($pendingTransfer);
            echo "Kết quả dispatch: " . ($dispatchResult['success'] ? 'Thành công' : 'Thất bại - ' . $dispatchResult['message']) . "\n";
            
            if ($dispatchResult['success']) {
                $pendingTransfer->refresh();
            }
        }
        
        if ($pendingTransfer->status === 'dispatched') {
            echo "Receive phiếu chuyển kho...\n";
            $receiveResult = $workflowService->receiveTransfer($pendingTransfer);
            echo "Kết quả receive: " . ($receiveResult['success'] ? 'Thành công' : 'Thất bại - ' . $receiveResult['message']) . "\n";
            
            if ($receiveResult['success']) {
                $pendingTransfer->refresh();
                echo "Trạng thái phiếu chuyển kho sau khi receive: {$pendingTransfer->status}\n";
                
                // Kiểm tra lại trạng thái fulfillment
                $fulfillment->refresh();
                echo "Trạng thái fulfillment sau khi receive: {$fulfillment->status}\n";
            }
        }
    }
}

echo "\n=== KẾT THÚC PHÂN TÍCH ===\n";