<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM TRA VẤN ĐỀ CẬP NHẬT FULFILLMENT ===\n\n";

// Tìm phiếu chuyển kho đã received
$stockTransfer = StockTransfer::where('transfer_code', 'FULFILL-DH-JIHM4GDU0X-51-48AA')->first();

if (!$stockTransfer) {
    echo "Không tìm thấy phiếu chuyển kho\n";
    exit;
}

echo "Phiếu chuyển kho: {$stockTransfer->transfer_code}\n";
echo "Trạng thái: {$stockTransfer->status}\n";
echo "Từ kho: {$stockTransfer->from_location_id}\n";
echo "Đến kho: {$stockTransfer->to_location_id}\n";
echo "Ngày nhận: {$stockTransfer->received_at}\n";
echo "Ghi chú: {$stockTransfer->notes}\n\n";

// Kiểm tra fulfillment hiện tại
$fulfillment = OrderFulfillment::find(51);
echo "=== FULFILLMENT HIỆN TẠI ===\n";
echo "ID: {$fulfillment->id}\n";
echo "Trạng thái: {$fulfillment->status}\n";
echo "Kho: {$fulfillment->store_location_id}\n";
echo "Cập nhật lần cuối: {$fulfillment->updated_at}\n\n";

// Kiểm tra xem có logic cập nhật fulfillment không
echo "=== KIỂM TRA LOGIC CẬP NHẬT ===\n";

// Thử gọi logic cập nhật thủ công
try {
    $workflowService = new StockTransferWorkflowService();
    
    echo "Đang thử cập nhật fulfillment thủ công...\n";
    
    // Gọi phương thức nhận hàng (sẽ trigger cập nhật fulfillment)
    $result = $workflowService->receiveTransfer($stockTransfer);
    
    echo "Cập nhật thành công!\n\n";
    
    // Kiểm tra lại fulfillment sau khi cập nhật
    $fulfillment->refresh();
    echo "=== FULFILLMENT SAU KHI CẬP NHẬT ===\n";
    echo "ID: {$fulfillment->id}\n";
    echo "Trạng thái: {$fulfillment->status}\n";
    echo "Kho: {$fulfillment->store_location_id}\n";
    echo "Cập nhật lần cuối: {$fulfillment->updated_at}\n";
    
} catch (Exception $e) {
    echo "Lỗi khi cập nhật: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";