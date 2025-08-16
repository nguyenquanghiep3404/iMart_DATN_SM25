<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\StockTransfer;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Services\AutoStockTransferService;
use App\Services\OrderFulfillmentCheckService;

echo "=== DEBUG: Kiểm tra đơn hàng và phiếu chuyển kho tự động ===\n\n";

// 1. Lấy 5 đơn hàng gần đây
echo "1. DANH SÁCH ĐỚN HÀNG GẦN ĐÂY:\n";
$recentOrders = Order::with(['items.productVariant'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentOrders as $order) {
    echo "- Đơn hàng: {$order->order_code}\n";
    echo "  Trạng thái: {$order->status}\n";
    echo "  Thanh toán: {$order->payment_status}\n";
    echo "  Tỉnh giao hàng: {$order->shipping_old_province_code}\n";
    echo "  Ngày tạo: {$order->created_at}\n";
    echo "  Số sản phẩm: " . $order->items->count() . "\n\n";
}

// 2. Kiểm tra phiếu chuyển kho tự động
echo "\n2. PHIẾU CHUYỂN KHO TỰ ĐỘNG:\n";
$autoTransfers = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($autoTransfers->isEmpty()) {
    echo "- Không có phiếu chuyển kho tự động nào\n";
} else {
    foreach ($autoTransfers as $transfer) {
        echo "- Phiếu: {$transfer->transfer_code}\n";
        echo "  Trạng thái: {$transfer->status}\n";
        echo "  Từ: " . ($transfer->fromLocation ? $transfer->fromLocation->name : 'N/A') . "\n";
        echo "  Đến: " . ($transfer->toLocation ? $transfer->toLocation->name : 'N/A') . "\n";
        echo "  Ghi chú: {$transfer->notes}\n";
        echo "  Ngày tạo: {$transfer->created_at}\n\n";
    }
}

// 3. Kiểm tra một đơn hàng cụ thể
if ($recentOrders->isNotEmpty()) {
    $testOrder = $recentOrders->first();
    echo "\n3. KIỂM TRA CHI TIẾT ĐƠN HÀNG: {$testOrder->order_code}\n";
    
    // Kiểm tra tỉnh đích
    $destinationProvince = ProvinceOld::find($testOrder->shipping_old_province_code);
    if ($destinationProvince) {
        echo "- Tỉnh đích: {$destinationProvince->name} (Vùng: {$destinationProvince->region})\n";
        
        // Kiểm tra warehouse trong tỉnh
        $warehouseInProvince = StoreLocation::where('type', 'warehouse')
            ->where('province_code', $destinationProvince->code)
            ->where('is_active', true)
            ->first();
            
        if ($warehouseInProvince) {
            echo "- Có warehouse trong tỉnh: {$warehouseInProvince->name}\n";
        } else {
            echo "- KHÔNG có warehouse trong tỉnh\n";
        }
    } else {
        echo "- KHÔNG tìm thấy thông tin tỉnh đích\n";
    }
    
    // Kiểm tra tồn kho cho từng sản phẩm
    echo "\n- KIỂM TRA TỒN KHO:\n";
    foreach ($testOrder->items as $item) {
        echo "  + Sản phẩm: " . ($item->productVariant ? $item->productVariant->sku : 'N/A') . " (SL: {$item->quantity})\n";
        
        // Tìm tồn kho ở warehouse
        $warehouseInventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
            ->where('inventory_type', 'new')
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->where('store_locations.type', 'warehouse')
            ->where('store_locations.is_active', true)
            ->select('product_inventories.*', 'store_locations.name as warehouse_name', 'store_locations.province_code')
            ->get();
            
        if ($warehouseInventory->isNotEmpty()) {
            echo "    Warehouse có hàng:\n";
            foreach ($warehouseInventory as $inv) {
                echo "    - {$inv->warehouse_name} ({$inv->province_code}): {$inv->quantity} cái\n";
            }
        } else {
            echo "    KHÔNG có warehouse nào có hàng\n";
        }
        
        // Tìm tồn kho ở store
        $storeInventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
            ->where('inventory_type', 'new')
            ->where('quantity', '>=', $item->quantity)
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->where('store_locations.type', 'store')
            ->where('store_locations.is_active', true)
            ->select('product_inventories.*', 'store_locations.name as store_name', 'store_locations.province_code')
            ->get();
            
        if ($storeInventory->isNotEmpty()) {
            echo "    Store có đủ hàng:\n";
            foreach ($storeInventory as $inv) {
                echo "    - {$inv->store_name} ({$inv->province_code}): {$inv->quantity} cái\n";
            }
        } else {
            echo "    KHÔNG có store nào có đủ hàng\n";
        }
    }
    
    // 4. Test logic tạo phiếu chuyển kho
    echo "\n4. TEST TẠO PHIẾU CHUYỂN KHO TỰ ĐỘNG:\n";
    try {
        $autoTransferService = new AutoStockTransferService();
        $result = $autoTransferService->checkAndCreateAutoTransfer($testOrder);
        
        echo "- Kết quả: " . ($result['success'] ? 'THÀNH CÔNG' : 'THẤT BẠI') . "\n";
        echo "- Thông báo: {$result['message']}\n";
        
        if (!empty($result['transfers_created'])) {
            echo "- Đã tạo " . count($result['transfers_created']) . " phiếu chuyển kho:\n";
            foreach ($result['transfers_created'] as $transfer) {
                echo "  + {$transfer['transfer_code']}: {$transfer['from_store']} → {$transfer['to_warehouse']}\n";
            }
        }
    } catch (Exception $e) {
        echo "- LỖI: {$e->getMessage()}\n";
    }
    
    // 5. Test OrderFulfillmentCheckService
    echo "\n5. TEST FULFILLMENT CHECK SERVICE:\n";
    try {
        $fulfillmentService = new OrderFulfillmentCheckService();
        $transferCheck = $fulfillmentService->createAutoTransferIfNeeded($testOrder);
        
        echo "- Đã tạo: " . ($transferCheck['created'] ? 'CÓ' : 'KHÔNG') . "\n";
        echo "- Lý do: {$transferCheck['reason']}\n";
    } catch (Exception $e) {
        echo "- LỖI: {$e->getMessage()}\n";
    }
}

echo "\n=== KẾT THÚC DEBUG ===\n";