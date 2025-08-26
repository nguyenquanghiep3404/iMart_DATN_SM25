<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel đã được khởi tạo thành công!\n";

use App\Models\Order;
use Illuminate\Support\Facades\Log;

// Tìm đơn hàng đầu tiên có trạng thái 'pending_confirmation'
$order = Order::where('status', 'pending_confirmation')->first();

// Nếu không có đơn hàng pending_confirmation, tìm đơn hàng processing để reset về pending_confirmation
if (!$order) {
    $order = Order::where('status', 'processing')->first();
    if ($order) {
        echo "Không tìm thấy đơn hàng pending_confirmation, reset đơn hàng {$order->order_code} về pending_confirmation\n";
        $order->status = 'pending_confirmation';
        $order->save();
    }
}

if (!$order) {
    echo "Không tìm thấy đơn hàng nào có trạng thái 'pending_confirmation'\n";
    
    // Tạo đơn hàng test nếu cần
    echo "Danh sách các đơn hàng hiện có:\n";
    $orders = Order::select('id', 'order_code', 'status')->take(5)->get();
    foreach ($orders as $o) {
        echo "- ID: {$o->id}, Code: {$o->order_code}, Status: {$o->status}\n";
    }
    exit;
}

echo "Tìm thấy đơn hàng: {$order->order_code} (ID: {$order->id}) - Trạng thái: {$order->status}\n";

// Kiểm tra fulfillments của đơn hàng
$fulfillments = $order->fulfillments;
echo "Số lượng fulfillments: {$fulfillments->count()}\n";

foreach ($fulfillments as $fulfillment) {
    echo "- Fulfillment ID: {$fulfillment->id}, Status: {$fulfillment->status}, Type: {$fulfillment->fulfillment_type}\n";
}

// Cập nhật trạng thái đơn hàng để trigger Observer
echo "\nCập nhật trạng thái đơn hàng từ '{$order->status}' sang 'processing'...\n";

// Thêm listener để kiểm tra xem Observer có được gọi không
\Illuminate\Support\Facades\Event::listen('eloquent.updated: App\\Models\\Order', function($event) {
    echo "OrderObserver updated event được trigger!\n";
});

$order->status = 'processing';
$order->save();

echo "Đã cập nhật trạng thái đơn hàng thành công!\n";
echo "Kiểm tra log để xem kết quả tạo phiếu chuyển kho.\n";

// Kiểm tra lại fulfillments sau khi cập nhật
$order->refresh();
$fulfillments = $order->fulfillments;
echo "\nSau khi cập nhật:";
foreach ($fulfillments as $fulfillment) {
    echo "- Fulfillment ID: {$fulfillment->id}, Status: {$fulfillment->status}\n";
}

// Kiểm tra phiếu chuyển kho (tìm theo thời gian tạo gần đây)
$transfers = \App\Models\StockTransfer::where('created_at', '>=', now()->subMinutes(5))->get();
echo "\nSố lượng phiếu chuyển kho được tạo trong 5 phút qua: {$transfers->count()}\n";
foreach ($transfers as $transfer) {
    echo "- Transfer ID: {$transfer->id}, Code: {$transfer->transfer_code}, Status: {$transfer->status}, Created: {$transfer->created_at}\n";
}