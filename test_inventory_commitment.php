<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\ProductInventory;
use App\Services\InventoryCommitmentService;
use Illuminate\Support\Facades\DB;

echo "=== TEST LOGIC QUẢN LÝ TỒN KHO MỚI ===\n\n";

try {
    // 1. Tìm một product variant có tồn kho
    $variant = ProductVariant::whereHas('inventories', function($query) {
        $query->where('quantity', '>', 0);
    })->first();
    
    if (!$variant) {
        echo "Không tìm thấy sản phẩm nào có tồn kho\n";
        exit;
    }
    
    echo "Sản phẩm test: {$variant->sku}\n";
    
    // 2. Kiểm tra tồn kho hiện tại
    echo "\n=== TÌNH TRẠNG TỒN KHO HIỆN TẠI ===\n";
    $inventories = $variant->inventories;
    foreach ($inventories as $inventory) {
        echo "Kho: {$inventory->storeLocation->name}\n";
        echo "- Tồn kho thực tế: {$inventory->quantity}\n";
        echo "- Đã tạm giữ: {$inventory->quantity_committed}\n";
        echo "- Có thể bán: {$inventory->available_quantity}\n\n";
    }
    
    // 3. Test tính toán tổng tồn kho có thể bán
    echo "=== TỔNG TỒN KHO CÓ THỂ BÁN ===\n";
    echo "Tồn kho cũ (sellable_stock): {$variant->sellable_stock}\n";
    echo "Tồn kho mới (available_stock): {$variant->available_stock}\n\n";
    
    // 4. Test InventoryCommitmentService
    echo "=== TEST INVENTORY COMMITMENT SERVICE ===\n";
    $service = new InventoryCommitmentService();
    
    // Kiểm tra có đủ tồn kho không
    $requestedQty = 2;
    $hasStock = $service->checkAvailableStock($variant->id, $requestedQty);
    echo "Kiểm tra có đủ {$requestedQty} sản phẩm: " . ($hasStock ? 'CÓ' : 'KHÔNG') . "\n";
    
    $totalAvailable = $service->getTotalAvailableStock($variant->id);
    echo "Tổng tồn kho có thể bán: {$totalAvailable}\n\n";
    
    // 5. Test chi tiết tồn kho theo kho
    echo "=== CHI TIẾT TỒN KHO THEO KHO ===\n";
    $details = $variant->getInventoryDetails();
    foreach ($details as $detail) {
        echo "Kho: {$detail->storeLocation->name}\n";
        echo "- Địa chỉ: {$detail->storeLocation->address}\n";
        echo "- Tồn kho thực tế: {$detail->quantity}\n";
        echo "- Đã tạm giữ: {$detail->quantity_committed}\n";
        echo "- Có thể bán: {$detail->available_quantity}\n\n";
    }
    
    echo "=== TEST HOÀN THÀNH ===\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}