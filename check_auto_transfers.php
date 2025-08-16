<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StockTransfer;
use App\Models\Order;

echo "=== KIỂM TRA PHIẾU CHUYỂN KHO TỰ ĐỘNG ===\n\n";

// Kiểm tra phiếu chuyển kho tự động
$autoTransfers = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get(['id', 'transfer_code', 'status', 'created_at', 'notes']);

echo "Số lượng phiếu chuyển kho tự động: " . $autoTransfers->count() . "\n\n";

if ($autoTransfers->count() > 0) {
    echo "Danh sách phiếu chuyển kho tự động gần đây:\n";
    foreach ($autoTransfers as $transfer) {
        echo "- ID: {$transfer->id}, Mã: {$transfer->transfer_code}, Trạng thái: {$transfer->status}, Ngày tạo: {$transfer->created_at}\n";
        echo "  Ghi chú: {$transfer->notes}\n\n";
    }
} else {
    echo "Không có phiếu chuyển kho tự động nào.\n\n";
}

// Kiểm tra đơn hàng gần đây
$recentOrders = Order::where('status', 'confirmed')
    ->orWhere('status', 'processing')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get(['id', 'order_code', 'status', 'created_at']);

echo "Đơn hàng gần đây (confirmed/processing):\n";
foreach ($recentOrders as $order) {
    echo "- ID: {$order->id}, Mã: {$order->order_code}, Trạng thái: {$order->status}, Ngày tạo: {$order->created_at}\n";
}

echo "\n=== KẾT THÚC KIỂM TRA ===\n";