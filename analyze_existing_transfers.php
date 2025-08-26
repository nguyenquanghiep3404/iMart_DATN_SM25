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

// 1. Tìm tất cả phiếu chuyển kho
echo "1. Danh sách tất cả phiếu chuyển kho...\n";
$allTransfers = StockTransfer::with(['fromLocation', 'toLocation', 'items'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Tổng số phiếu chuyển kho (10 gần nhất): {$allTransfers->count()}\n\n";

foreach ($allTransfers as $transfer) {
    echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
    echo "Trạng thái: {$transfer->status}\n";
    echo "Từ kho: {$transfer->fromLocation->name} (ID: {$transfer->from_location_id})\n";
    echo "Đến kho: {$transfer->toLocation->name} (ID: {$transfer->to_location_id})\n";
    echo "Ghi chú: {$transfer->notes}\n";
    echo "Ngày tạo: {$transfer->created_at}\n";
    echo "Ngày gửi: {$transfer->shipped_at}\n";
    echo "Ngày nhận: {$transfer->received_at}\n";
    
    // Tìm đơn hàng liên quan
    $relatedOrder = null;
    if ($transfer->notes && str_contains($transfer->notes, 'Order:')) {
        $orderCode = trim(str_replace('Order:', '', $transfer->notes));
        $relatedOrder = Order::where('order_code', $orderCode)->with(['fulfillments.storeLocation'])->first();
        
        if ($relatedOrder) {
            echo "Đơn hàng liên quan: {$relatedOrder->order_code}\n";
            echo "Trạng thái đơn hàng: {$relatedOrder->status}\n";
            echo "Địa chỉ giao hàng: {$relatedOrder->shipping_old_province_code}\n";
            
            foreach ($relatedOrder->fulfillments as $fulfillment) {
                echo "  - Fulfillment ID: {$fulfillment->id}, Trạng thái: {$fulfillment->status}\n";
                echo "    Kho: {$fulfillment->storeLocation->name} (Tỉnh: {$fulfillment->storeLocation->province_code})\n";
            }
        } else {
            echo "Không tìm thấy đơn hàng: {$orderCode}\n";
        }
    } else {
        echo "Không có thông tin đơn hàng trong ghi chú\n";
    }
    
    echo "---\n";
}

// 2. Phân tích các trạng thái
echo "\n2. Thống kê trạng thái phiếu chuyển kho...\n";
$statusStats = StockTransfer::select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statusStats as $stat) {
    echo "Trạng thái '{$stat->status}': {$stat->count} phiếu\n";
}

// 3. Tìm phiếu chuyển kho có trạng thái 'received' để test logic
echo "\n3. Phân tích phiếu chuyển kho đã nhận...\n";
$receivedTransfers = StockTransfer::where('status', 'received')
    ->with(['fromLocation', 'toLocation', 'items'])
    ->limit(5)
    ->get();

if ($receivedTransfers->count() > 0) {
    echo "Tìm thấy {$receivedTransfers->count()} phiếu chuyển kho đã nhận\n\n";
    
    foreach ($receivedTransfers as $transfer) {
        echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
        echo "Từ kho: {$transfer->fromLocation->name} (Tỉnh: {$transfer->fromLocation->province_code})\n";
        echo "Đến kho: {$transfer->toLocation->name} (Tỉnh: {$transfer->toLocation->province_code})\n";
        echo "Ghi chú: {$transfer->notes}\n";
        
        // Tìm đơn hàng liên quan
        if ($transfer->notes && str_contains($transfer->notes, 'Order:')) {
            $orderCode = trim(str_replace('Order:', '', $transfer->notes));
            $relatedOrder = Order::where('order_code', $orderCode)
                ->with(['fulfillments.storeLocation'])
                ->first();
            
            if ($relatedOrder) {
                echo "Đơn hàng: {$relatedOrder->order_code}\n";
                echo "Tỉnh giao hàng: {$relatedOrder->shipping_old_province_code}\n";
                
                // Kiểm tra logic cập nhật fulfillment
                $destinationProvince = $transfer->toLocation->province_code;
                $shippingProvince = $relatedOrder->shipping_old_province_code;
                $sameProvince = $destinationProvince === $shippingProvince;
                
                echo "Kho đích cùng tỉnh với địa chỉ giao hàng: " . ($sameProvince ? 'Có' : 'Không') . "\n";
                
                if ($sameProvince) {
                    echo "Điều kiện cùng tỉnh thỏa mãn - Kiểm tra fulfillment...\n";
                    
                    foreach ($relatedOrder->fulfillments as $fulfillment) {
                        echo "  Fulfillment ID: {$fulfillment->id}\n";
                        echo "  Trạng thái hiện tại: {$fulfillment->status}\n";
                        echo "  Kho hiện tại: {$fulfillment->storeLocation->name} (Tỉnh: {$fulfillment->storeLocation->province_code})\n";
                        
                        if ($fulfillment->status === 'processing') {
                            echo "  *** PHÁT HIỆN VẤN ĐỀ: Fulfillment vẫn ở trạng thái 'processing' ***\n";
                            echo "  Lý do có thể:\n";
                            echo "  - Logic updateRelatedFulfillmentStatus không được gọi\n";
                            echo "  - Điều kiện kiểm tra tồn kho không thỏa mãn\n";
                            echo "  - Lỗi trong quá trình cập nhật database\n";
                        } elseif ($fulfillment->status === 'packed') {
                            echo "  ✓ Fulfillment đã được cập nhật thành 'packed' đúng\n";
                        }
                    }
                } else {
                    echo "Kho đích khác tỉnh - Không cập nhật fulfillment\n";
                }
            }
        }
        
        echo "---\n";
    }
} else {
    echo "Không tìm thấy phiếu chuyển kho nào có trạng thái 'received'\n";
}

// 4. Kiểm tra fulfillment có trạng thái processing
echo "\n4. Kiểm tra fulfillment trạng thái 'processing'...\n";
$processingFulfillments = OrderFulfillment::where('status', 'processing')
    ->with(['order', 'storeLocation'])
    ->limit(10)
    ->get();

echo "Tìm thấy {$processingFulfillments->count()} fulfillment đang xử lý\n\n";

foreach ($processingFulfillments as $fulfillment) {
    echo "Fulfillment ID: {$fulfillment->id}\n";
    echo "Đơn hàng: {$fulfillment->order->order_code}\n";
    echo "Trạng thái: {$fulfillment->status}\n";
    echo "Kho: {$fulfillment->storeLocation->name} (Tỉnh: {$fulfillment->storeLocation->province_code})\n";
    echo "Địa chỉ giao hàng: {$fulfillment->order->shipping_old_province_code}\n";
    
    // Tìm phiếu chuyển kho liên quan
    $relatedTransfers = StockTransfer::where('notes', 'like', '%Order:' . $fulfillment->order->order_code . '%')
        ->get();
    
    if ($relatedTransfers->count() > 0) {
        echo "Có {$relatedTransfers->count()} phiếu chuyển kho liên quan:\n";
        foreach ($relatedTransfers as $rt) {
            echo "  - {$rt->transfer_code} (Trạng thái: {$rt->status})\n";
        }
    } else {
        echo "Không có phiếu chuyển kho liên quan\n";
    }
    
    echo "---\n";
}

echo "\n=== KẾT THÚC PHÂN TÍCH ===\n";