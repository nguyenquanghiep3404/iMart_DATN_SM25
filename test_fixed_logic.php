<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\OrderFulfillment;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\Log;

echo "=== TEST LOGIC SỬA ĐỔI ===\n\n";

// Lấy phiếu chuyển kho cụ thể
$transferCode = 'AUTO-IM250826000076IVVO-0AFB';
$transfer = StockTransfer::where('transfer_code', $transferCode)->first();

if (!$transfer) {
    echo "Không tìm thấy phiếu chuyển kho: {$transferCode}\n";
    exit;
}

echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
echo "Trạng thái: {$transfer->status}\n";
echo "Từ kho: {$transfer->from_location_id}\n";
echo "Đến kho: {$transfer->to_location_id}\n\n";

// Kiểm tra fulfillment ID 32 trước khi test
$fulfillment32 = OrderFulfillment::find(32);
if ($fulfillment32) {
    echo "=== TRẠNG THÁI TRƯỚC KHI TEST ===\n";
    echo "Fulfillment ID 32:\n";
    echo "- Store Location: {$fulfillment32->store_location_id}\n";
    echo "- Status: {$fulfillment32->status}\n";
    echo "- Tracking: {$fulfillment32->tracking_code}\n\n";
} else {
    echo "Không tìm thấy fulfillment ID 32\n";
    exit;
}

// Gọi logic cập nhật
echo "=== CHẠY LOGIC CẬP NHẬT ===\n";
$service = new StockTransferWorkflowService();

try {
    // Sử dụng reflection để gọi private method
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('updateRelatedFulfillmentStatus');
    $method->setAccessible(true);
    
    echo "Gọi updateRelatedFulfillmentStatus...\n";
    $method->invoke($service, $transfer);
    echo "Logic đã chạy xong\n\n";
    
} catch (Exception $e) {
    echo "Lỗi khi gọi updateRelatedFulfillmentStatus: {$e->getMessage()}\n";
    exit;
}

// Kiểm tra kết quả
echo "=== KIỂM TRA KẾT QUẢ ===\n";
$fulfillment32->refresh();

echo "Fulfillment ID 32 sau khi cập nhật:\n";
echo "- Store Location: {$fulfillment32->store_location_id}\n";
echo "- Status: {$fulfillment32->status}\n";
echo "- Tracking: {$fulfillment32->tracking_code}\n\n";

if ($fulfillment32->store_location_id == 6 && $fulfillment32->status == 'packed') {
    echo "✓ THÀNH CÔNG! Fulfillment đã được chuyển sang kho đích và đổi trạng thái thành 'packed'\n";
} else {
    echo "✗ THẤT BẠI! Fulfillment chưa được cập nhật đúng\n";
    echo "Mong đợi: store_location_id = 6, status = 'packed'\n";
    echo "Thực tế: store_location_id = {$fulfillment32->store_location_id}, status = {$fulfillment32->status}\n";
}

echo "\n=== KẾT THÚC TEST ===\n";