<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== TEST WORKFLOW SỬA LỖI ===\n\n";

// Tìm đơn hàng test
$order = Order::where('order_code', 'DH-JIHM4GDU0X')->first();
if (!$order) {
    echo "Không tìm thấy đơn hàng test\n";
    exit;
}

echo "Đơn hàng: {$order->order_code}\n";
echo "Trạng thái: {$order->status}\n\n";

// Tìm fulfillment
$fulfillment = $order->fulfillments()->first();
if (!$fulfillment) {
    echo "Không tìm thấy fulfillment\n";
    exit;
}

echo "=== TRẠNG THÁI HIỆN TẠI ===\n";
echo "Fulfillment ID: {$fulfillment->id}\n";
echo "Trạng thái: {$fulfillment->status}\n";
echo "Kho hiện tại: {$fulfillment->store_location_id}\n\n";

// Reset fulfillment về trạng thái ban đầu để test
echo "Reset fulfillment về trạng thái 'processing' tại kho 7 để test...\n";
$fulfillment->update([
    'status' => 'processing',
    'store_location_id' => 7
]);

echo "✅ Đã reset fulfillment\n\n";

// Tạo phiếu chuyển kho mới để test
echo "Tạo phiếu chuyển kho mới để test...\n";

$transferCode = 'TEST-FULFILL-' . $fulfillment->id . '-' . time();

$stockTransfer = StockTransfer::create([
    'transfer_code' => $transferCode,
    'from_location_id' => 7, // Kho Hồ Chí Minh
    'to_location_id' => 6,   // Kho Hà Nội
    'status' => 'pending',
    'notes' => "Order:{$order->order_code} - Chuyển kho tự động cho fulfillment #{$fulfillment->id}",
    'created_by' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✅ Đã tạo phiếu chuyển kho: {$stockTransfer->transfer_code}\n\n";

// Thêm items vào phiếu chuyển kho
foreach ($fulfillment->items as $fulfillmentItem) {
    $stockTransfer->items()->create([
        'product_variant_id' => $fulfillmentItem->orderItem->product_variant_id,
        'quantity' => $fulfillmentItem->quantity,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✅ Đã thêm items vào phiếu chuyển kho\n\n";

// Test workflow: processTransferWorkflow
echo "=== TEST WORKFLOW ===\n\n";

$workflowService = new StockTransferWorkflowService();

// Sử dụng processTransferWorkflow để test toàn bộ workflow
echo "1. Xử lý workflow hoàn chỉnh...\n";
try {
    $result = $workflowService->processTransferWorkflow($stockTransfer);
    $stockTransfer->refresh();
    
    if ($result['success']) {
        echo "✅ Workflow thành công. Trạng thái: {$stockTransfer->status}\n\n";
    } else {
        echo "❌ Workflow thất bại: {$result['message']}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi workflow: {$e->getMessage()}\n\n";
}

// Kiểm tra kết quả
echo "=== KẾT QUẢ SAU KHI RECEIVE ===\n";
$fulfillment->refresh();
echo "Fulfillment ID: {$fulfillment->id}\n";
echo "Trạng thái: {$fulfillment->status}\n";
echo "Kho: {$fulfillment->store_location_id}\n";
echo "Cập nhật lần cuối: {$fulfillment->updated_at}\n\n";

if ($fulfillment->status === 'packed' && $fulfillment->store_location_id == 6) {
    echo "🎉 SUCCESS: Workflow hoạt động đúng!\n";
    echo "- Fulfillment đã được chuyển từ kho 7 sang kho 6\n";
    echo "- Trạng thái đã được cập nhật từ 'processing' thành 'packed'\n";
} else {
    echo "❌ FAILED: Workflow chưa hoạt động đúng\n";
    echo "- Trạng thái mong đợi: packed, thực tế: {$fulfillment->status}\n";
    echo "- Kho mong đợi: 6, thực tế: {$fulfillment->store_location_id}\n";
}

echo "\n=== HOÀN THÀNH TEST ===\n";