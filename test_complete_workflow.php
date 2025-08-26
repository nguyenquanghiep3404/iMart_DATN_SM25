<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\Order;
use App\Services\StockTransferWorkflowService;

echo "=== TEST COMPLETE WORKFLOW ===\n\n";

// Kiểm tra trạng thái hiện tại của tất cả fulfillments
$order = Order::where('order_code', 'DH-PMSHCHKQGA')->first();

if (!$order) {
    echo "Không tìm thấy đơn hàng\n";
    exit;
}

echo "Đơn hàng: {$order->order_code}\n";
echo "Trạng thái đơn hàng: {$order->status}\n\n";

$fulfillments = $order->fulfillments()->get();

echo "=== TRẠNG THÁI FULFILLMENTS TRƯỚC KHI TEST ===\n";
foreach ($fulfillments as $fulfillment) {
    echo "Fulfillment ID {$fulfillment->id}:\n";
    echo "  Store Location: {$fulfillment->store_location_id}\n";
    echo "  Status: {$fulfillment->status}\n";
    echo "  Tracking: {$fulfillment->tracking_code}\n";
    echo "\n";
}

// Lấy phiếu chuyển kho và chạy logic cập nhật
$stockTransfer = StockTransfer::where('transfer_code', 'AUTO-IM250826000076IVVO-0AFB')->first();

if ($stockTransfer) {
    echo "=== CHẠY LOGIC CẬP NHẬT FULFILLMENT ===\n";
    echo "Phiếu chuyển kho: {$stockTransfer->transfer_code}\n";
    echo "Trạng thái: {$stockTransfer->status}\n\n";
    
    $service = new StockTransferWorkflowService();
    
    try {
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('updateRelatedFulfillmentStatus');
        $method->setAccessible(true);
        
        $method->invoke($service, $stockTransfer);
        echo "Logic cập nhật đã chạy xong\n\n";
        
    } catch (Exception $e) {
        echo "Lỗi khi chạy logic: {$e->getMessage()}\n\n";
    }
}

// Kiểm tra trạng thái sau khi cập nhật
echo "=== TRẠNG THÁI FULFILLMENTS SAU KHI CẬP NHẬT ===\n";
$fulfillments->each(function($f) { $f->refresh(); });

$allPacked = true;
foreach ($fulfillments as $fulfillment) {
    echo "Fulfillment ID {$fulfillment->id}:\n";
    echo "  Store Location: {$fulfillment->store_location_id}\n";
    echo "  Status: {$fulfillment->status}\n";
    echo "  Tracking: {$fulfillment->tracking_code}\n";
    
    if ($fulfillment->status !== 'packed') {
        $allPacked = false;
    }
    echo "\n";
}

echo "=== KẾT QUẢ TỔNG QUAN ===\n";
if ($allPacked) {
    echo "✓ THÀNH CÔNG! Tất cả fulfillments đã được cập nhật thành 'packed'\n";
    echo "✓ Workflow hoạt động đúng: Phiếu chuyển kho 'received' -> Fulfillments 'packed'\n";
} else {
    echo "✗ CHƯA HOÀN THÀNH! Vẫn có fulfillments chưa được cập nhật\n";
}

echo "\n=== KẾT THÚC TEST ===\n";