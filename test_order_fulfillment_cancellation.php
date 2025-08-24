<?php

/**
 * Test script để kiểm tra logic hủy order_fulfillments khi đơn hàng bị hủy
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    echo "=== Test Order Fulfillment Cancellation Logic ===\n";
    
    // Kiểm tra tổng số đơn hàng và fulfillments
    $totalOrders = Order::count();
    $totalFulfillments = OrderFulfillment::count();
    echo "Tổng số đơn hàng: {$totalOrders}\n";
    echo "Tổng số fulfillments: {$totalFulfillments}\n";
    
    // Tìm đơn hàng có fulfillments
    $ordersWithFulfillments = Order::whereHas('fulfillments')->count();
    echo "Đơn hàng có fulfillments: {$ordersWithFulfillments}\n";
    
    // Tìm một đơn hàng bất kỳ có fulfillments (bao gồm cả đã hủy)
    $order = Order::with('fulfillments')
        ->whereHas('fulfillments')
        ->first();
    
    if (!$order) {
        echo "Không có đơn hàng nào có fulfillments.\n";
        exit;
    }
    
    echo "\nTìm thấy đơn hàng: {$order->order_code}\n";
    echo "Trạng thái hiện tại: {$order->status}\n";
    echo "Số lượng fulfillments: {$order->fulfillments->count()}\n";
    
    // Hiển thị trạng thái fulfillments hiện tại
    echo "\nTrạng thái fulfillments hiện tại:\n";
    foreach ($order->fulfillments as $fulfillment) {
        echo "- Fulfillment ID {$fulfillment->id}: {$fulfillment->status}\n";
    }
    
    // Test logic: Thay đổi trạng thái đơn hàng để kích hoạt Observer
    echo "\n=== BẮT ĐẦU TEST LOGIC ===\n";
    
    // Đầu tiên, đặt đơn hàng về trạng thái khác cancelled
    echo "Đặt đơn hàng về trạng thái 'processing'...\n";
    $order->update([
        'status' => 'processing',
        'cancelled_at' => null,
        'cancellation_reason' => null
    ]);
    
    // Đặt fulfillments về trạng thái 'pending'
    $order->fulfillments()->update(['status' => 'pending']);
    
    // Refresh để lấy dữ liệu mới
    $order->refresh();
    $order->load('fulfillments');
    
    echo "Trạng thái đơn hàng sau khi reset: {$order->status}\n";
    echo "Trạng thái fulfillments sau khi reset:\n";
    foreach ($order->fulfillments as $fulfillment) {
        echo "- Fulfillment ID {$fulfillment->id}: {$fulfillment->status}\n";
    }
    
    // Bây giờ test hủy đơn hàng để kích hoạt Observer
    echo "\nĐang hủy đơn hàng để test logic Observer...\n";
    $order->update([
        'status' => Order::STATUS_CANCELLED,
        'cancelled_at' => now(),
        'cancellation_reason' => 'Test logic hủy order_fulfillments'
    ]);
    
    // Đợi một chút để Observer xử lý
    sleep(1);
    
    // Refresh để lấy dữ liệu mới nhất
    $order->refresh();
    $order->load('fulfillments');
    
    echo "Đơn hàng đã được hủy.\n";
    echo "Trạng thái đơn hàng: {$order->status}\n";
    
    // Kiểm tra trạng thái fulfillments sau khi hủy
    echo "\nTrạng thái fulfillments sau khi hủy:\n";
    $cancelledCount = 0;
    foreach ($order->fulfillments as $fulfillment) {
        echo "- Fulfillment ID {$fulfillment->id}: {$fulfillment->status}\n";
        if ($fulfillment->status === 'cancelled') {
            $cancelledCount++;
        }
    }
    
    // Kết quả
    echo "\n=== KẾT QUẢ TEST ===\n";
    if ($cancelledCount === $order->fulfillments->count()) {
        echo "✅ THÀNH CÔNG: Tất cả {$cancelledCount} fulfillments đã được hủy.\n";
        echo "✅ Logic OrderObserver hoạt động đúng!\n";
    } else {
        echo "❌ THẤT BẠI: Chỉ {$cancelledCount}/{$order->fulfillments->count()} fulfillments được hủy.\n";
        echo "❌ Logic OrderObserver chưa hoạt động đúng.\n";
        
        // Thử hủy thủ công để kiểm tra
        echo "\nThử hủy fulfillments thủ công...\n";
        $order->fulfillments()->update(['status' => 'cancelled']);
        $order->refresh();
        $order->load('fulfillments');
        
        $manualCancelledCount = $order->fulfillments->where('status', 'cancelled')->count();
        echo "Sau khi hủy thủ công: {$manualCancelledCount}/{$order->fulfillments->count()} fulfillments đã hủy.\n";
    }
    
    // Kiểm tra log để xem Observer có chạy không
    echo "\n=== KIỂM TRA LOG ===\n";
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        if (strpos($logContent, "Đã hủy tất cả order_fulfillments cho đơn hàng {$order->order_code}") !== false) {
            echo "✅ Tìm thấy log từ OrderObserver.\n";
        } else {
            echo "❌ Không tìm thấy log từ OrderObserver.\n";
        }
    } else {
        echo "❌ Không tìm thấy file log.\n";
    }
    
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test hoàn thành ===\n";