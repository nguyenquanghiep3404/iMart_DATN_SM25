<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM THỬ CHỨC NĂNG TỰ ĐỘNG XỬ LÝ KHÁC TỈNH THÀNH ===\n\n";

try {
    // Tìm 2 địa điểm khác tỉnh thành
    $locations = StoreLocation::select('id', 'name', 'province_code')
        ->whereNotNull('province_code')
        ->get()
        ->groupBy('province_code');
    
    if ($locations->count() < 2) {
        echo "Không đủ địa điểm khác tỉnh để kiểm thử\n";
        exit;
    }
    
    $provinces = $locations->keys()->take(2);
    $fromLocation = $locations[$provinces[0]]->first();
    $toLocation = $locations[$provinces[1]]->first();
    
    echo "Địa điểm nguồn: {$fromLocation->name} (Tỉnh: {$fromLocation->province_code})\n";
    echo "Địa điểm đích: {$toLocation->name} (Tỉnh: {$toLocation->province_code})\n\n";
    
    // Tạo phiếu chuyển kho test
    $transfer = StockTransfer::create([
        'transfer_code' => 'TEST-' . strtoupper(uniqid()),
        'from_location_id' => $fromLocation->id,
        'to_location_id' => $toLocation->id,
        'status' => 'pending',
        'notes' => 'Test phiếu chuyển kho khác tỉnh thành',
        'created_by' => 1
    ]);
    
    echo "Đã tạo phiếu chuyển kho test: {$transfer->transfer_code}\n";
    echo "Trạng thái ban đầu: {$transfer->status}\n\n";
    
    // Kiểm tra canAutoProcess
    $workflowService = new StockTransferWorkflowService();
    $canAutoProcess = $workflowService->canAutoProcess($transfer);
    
    echo "Có thể tự động xử lý: " . ($canAutoProcess ? 'CÓ' : 'KHÔNG') . "\n\n";
    
    if ($canAutoProcess) {
        echo "Bắt đầu xử lý workflow tự động...\n";
        
        $result = $workflowService->processTransferWorkflow($transfer);
        
        if ($result['success']) {
            echo "✓ Xử lý workflow thành công!\n";
            
            // Reload để xem trạng thái mới
            $transfer->refresh();
            echo "Trạng thái cuối cùng: {$transfer->status}\n";
            echo "Thời gian xuất: {$transfer->dispatched_at}\n";
            echo "Thời gian vận chuyển: {$transfer->shipped_at}\n";
            echo "Thời gian nhận: {$transfer->received_at}\n";
        } else {
            echo "✗ Lỗi xử lý workflow: {$result['message']}\n";
        }
    } else {
        echo "Không thể tự động xử lý phiếu chuyển kho này\n";
    }
    
    // Dọn dẹp
    echo "\nDọn dẹp phiếu test...\n";
    $transfer->delete();
    echo "Đã xóa phiếu test\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== KẾT THÚC KIỂM THỬ ===\n";