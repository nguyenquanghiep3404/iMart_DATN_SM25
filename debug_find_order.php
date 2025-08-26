<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\Order;
use App\Services\StockTransferWorkflowService;

echo "=== DEBUG FIND RELATED ORDER ===\n\n";

// Lấy phiếu chuyển kho cụ thể
$transferCode = 'AUTO-IM250826000076IVVO-0AFB';
$transfer = StockTransfer::where('transfer_code', $transferCode)->first();

if (!$transfer) {
    echo "Không tìm thấy phiếu chuyển kho: {$transferCode}\n";
    exit;
}

echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
echo "Ghi chú: '{$transfer->notes}'\n\n";

// Test logic findRelatedOrder
echo "=== TEST LOGIC FIND RELATED ORDER ===\n";

if ($transfer->notes && str_contains($transfer->notes, 'Order:')) {
    echo "✓ Ghi chú chứa 'Order:'\n";
    
    // Logic hiện tại trong service
    $orderCode = trim(str_replace('Order:', '', $transfer->notes));
    echo "Mã đơn hàng sau khi str_replace: '{$orderCode}'\n";
    
    $order = Order::where('order_code', $orderCode)->first();
    if ($order) {
        echo "✓ Tìm thấy đơn hàng với mã: {$orderCode}\n";
    } else {
        echo "✗ KHÔNG tìm thấy đơn hàng với mã: '{$orderCode}'\n";
        
        // Thử các cách parse khác
        echo "\n=== THỬ CÁC CÁCH PARSE KHÁC ===\n";
        
        // Cách 1: Tách theo khoảng trắng
        $parts = explode('Order:', $transfer->notes);
        if (count($parts) > 1) {
            $orderPart = trim($parts[1]);
            echo "Phần sau 'Order:': '{$orderPart}'\n";
            
            // Lấy phần trước dấu cách
            $orderCode1 = explode(' ', $orderPart)[0];
            echo "Mã đơn hàng cách 1 (trước dấu cách): '{$orderCode1}'\n";
            
            $order1 = Order::where('order_code', $orderCode1)->first();
            if ($order1) {
                echo "✓ Tìm thấy đơn hàng với cách 1: {$orderCode1}\n";
            } else {
                echo "✗ Không tìm thấy đơn hàng với cách 1: '{$orderCode1}'\n";
            }
            
            // Lấy phần trước dấu ngoặc
            $orderCode2 = explode('(', $orderPart)[0];
            $orderCode2 = trim($orderCode2);
            echo "Mã đơn hàng cách 2 (trước dấu ngoặc): '{$orderCode2}'\n";
            
            $order2 = Order::where('order_code', $orderCode2)->first();
            if ($order2) {
                echo "✓ Tìm thấy đơn hàng với cách 2: {$orderCode2}\n";
            } else {
                echo "✗ Không tìm thấy đơn hàng với cách 2: '{$orderCode2}'\n";
            }
        }
        
        // Kiểm tra tất cả đơn hàng có mã tương tự
        echo "\n=== TÌM KIẾM TƯƠNG TỰ ===\n";
        $similarOrders = Order::where('order_code', 'like', '%DH-PMSHCHKQGA%')->get();
        echo "Số đơn hàng có mã chứa 'DH-PMSHCHKQGA': {$similarOrders->count()}\n";
        foreach ($similarOrders as $o) {
            echo "- {$o->order_code}\n";
        }
    }
} else {
    echo "✗ Ghi chú KHÔNG chứa 'Order:'\n";
}

echo "\n=== KẾT THÚC DEBUG ===\n";