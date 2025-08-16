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

echo "=== DEBUG: Phân tích chi tiết logic tạo phiếu chuyển kho ===\n\n";

// Lấy một đơn hàng đã thanh toán nhưng chưa có phiếu chuyển kho
$testOrder = Order::whereNotIn('id', function($query) {
    $query->select('orders.id')
        ->from('orders')
        ->join('stock_transfers', 'stock_transfers.notes', 'LIKE', DB::raw("CONCAT('%', orders.order_code, '%')"))
        ->where('stock_transfers.transfer_code', 'LIKE', 'AUTO-%');
})
->where('payment_status', 'paid')
->with(['items.productVariant'])
->first();

if (!$testOrder) {
    echo "Không tìm thấy đơn hàng đã thanh toán mà chưa có phiếu chuyển kho\n";
    exit;
}

echo "PHÂN TÍCH ĐƠN HÀNG: {$testOrder->order_code}\n";
echo "- Trạng thái: {$testOrder->status}\n";
echo "- Thanh toán: {$testOrder->payment_status}\n";
echo "- Tỉnh giao hàng: {$testOrder->shipping_old_province_code}\n\n";

// Bước 1: Kiểm tra tỉnh đích
$destinationProvince = ProvinceOld::find($testOrder->shipping_old_province_code);
if (!$destinationProvince) {
    echo "❌ KHÔNG tìm thấy thông tin tỉnh đích\n";
    exit;
}

echo "✅ Tỉnh đích: {$destinationProvince->name} (Vùng: {$destinationProvince->region})\n\n";

// Bước 2: Kiểm tra warehouse tại tỉnh đích
echo "BƯỚC 2: Kiểm tra warehouse tại tỉnh đích\n";
$destinationWarehouse = StoreLocation::where('type', 'warehouse')
    ->where('province_code', $destinationProvince->code)
    ->where('is_active', true)
    ->first();

if ($destinationWarehouse) {
    echo "✅ Có warehouse tại tỉnh đích: {$destinationWarehouse->name}\n";
    
    // Bước 3: Kiểm tra tồn kho tại warehouse đích
    echo "\nBƯỚC 3: Kiểm tra tồn kho tại warehouse đích\n";
    $needTransfer = false;
    
    foreach ($testOrder->items as $item) {
        echo "- Sản phẩm: " . ($item->productVariant ? $item->productVariant->sku : 'N/A') . " (cần: {$item->quantity})\n";
        
        $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
            ->where('store_location_id', $destinationWarehouse->id)
            ->where('inventory_type', 'new')
            ->first();
            
        $currentStock = $inventory ? $inventory->quantity : 0;
        echo "  Tồn kho hiện tại: {$currentStock}\n";
        
        if ($currentStock < $item->quantity) {
            echo "  ❌ THIẾU HÀNG: cần chuyển " . ($item->quantity - $currentStock) . " cái\n";
            $needTransfer = true;
            
            // Bước 4: Tìm nguồn hàng
            echo "\n  BƯỚC 4: Tìm nguồn hàng cho sản phẩm này\n";
            
            // 4a. Tìm warehouse khác có hàng (loại trừ tỉnh đích)
            echo "  4a. Tìm warehouse khác có hàng:\n";
            $warehouseWithStock = ProductInventory::where('product_variant_id', $item->product_variant_id)
                ->where('quantity', '>=', $item->quantity)
                ->where('inventory_type', 'new')
                ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
                ->where('store_locations.type', 'warehouse')
                ->where('store_locations.is_active', true)
                ->where('store_locations.province_code', '!=', $destinationProvince->code)
                ->select('product_inventories.*', 'store_locations.name as warehouse_name', 'store_locations.province_code')
                ->get();
                
            if ($warehouseWithStock->isNotEmpty()) {
                echo "    ✅ Tìm thấy " . $warehouseWithStock->count() . " warehouse có hàng:\n";
                foreach ($warehouseWithStock as $wh) {
                    echo "      - {$wh->warehouse_name} ({$wh->province_code}): {$wh->quantity} cái\n";
                }
            } else {
                echo "    ❌ Không có warehouse nào khác có đủ hàng\n";
                
                // 4b. Tìm store có hàng
                echo "  4b. Tìm store có hàng:\n";
                $storeWithStock = ProductInventory::where('product_variant_id', $item->product_variant_id)
                    ->where('quantity', '>=', $item->quantity)
                    ->where('inventory_type', 'new')
                    ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
                    ->where('store_locations.type', 'store')
                    ->where('store_locations.is_active', true)
                    ->select('product_inventories.*', 'store_locations.name as store_name', 'store_locations.province_code')
                    ->get();
                    
                if ($storeWithStock->isNotEmpty()) {
                    echo "    ✅ Tìm thấy " . $storeWithStock->count() . " store có hàng:\n";
                    foreach ($storeWithStock as $st) {
                        echo "      - {$st->store_name} ({$st->province_code}): {$st->quantity} cái\n";
                    }
                } else {
                    echo "    ❌ Không có store nào có đủ hàng\n";
                }
            }
        } else {
            echo "  ✅ ĐỦ HÀNG: không cần chuyển\n";
        }
        echo "\n";
    }
    
    if (!$needTransfer) {
        echo "🎉 KẾT LUẬN: Warehouse đích có đủ hàng cho tất cả sản phẩm → KHÔNG CẦN tạo phiếu chuyển kho\n";
    } else {
        echo "⚠️ KẾT LUẬN: Cần tạo phiếu chuyển kho cho một số sản phẩm\n";
    }
    
} else {
    echo "❌ KHÔNG có warehouse tại tỉnh đích\n";
    
    // Bước 3 (alternative): Tìm warehouse gần nhất có hàng
    echo "\nBƯỚC 3 (alternative): Tìm warehouse gần nhất có hàng\n";
    
    foreach ($testOrder->items as $item) {
        echo "- Sản phẩm: " . ($item->productVariant ? $item->productVariant->sku : 'N/A') . " (cần: {$item->quantity})\n";
        
        // Tìm warehouse có hàng (ưu tiên cùng vùng miền)
        $warehouseWithStock = ProductInventory::where('product_variant_id', $item->product_variant_id)
            ->where('quantity', '>=', $item->quantity)
            ->where('inventory_type', 'new')
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->join('province_olds', 'store_locations.province_code', '=', 'province_olds.code')
            ->where('store_locations.type', 'warehouse')
            ->where('store_locations.is_active', true)
            ->select('product_inventories.*', 'store_locations.name as warehouse_name', 'store_locations.province_code', 'province_olds.region')
            ->orderByRaw("CASE WHEN province_olds.region = '{$destinationProvince->region}' THEN 1 ELSE 2 END")
            ->get();
            
        if ($warehouseWithStock->isNotEmpty()) {
            echo "  ✅ Tìm thấy " . $warehouseWithStock->count() . " warehouse có hàng:\n";
            foreach ($warehouseWithStock as $wh) {
                $priority = $wh->region == $destinationProvince->region ? '(cùng vùng)' : '(khác vùng)';
                echo "    - {$wh->warehouse_name} ({$wh->province_code}) {$priority}: {$wh->quantity} cái\n";
            }
        } else {
            echo "  ❌ Không có warehouse nào có hàng\n";
        }
    }
}

echo "\n\n=== TEST THỰC TẾ VỚI AutoStockTransferService ===\n";
try {
    $autoTransferService = new AutoStockTransferService();
    $result = $autoTransferService->checkAndCreateAutoTransfer($testOrder);
    
    echo "Kết quả: " . ($result['success'] ? 'THÀNH CÔNG' : 'THẤT BẠI') . "\n";
    echo "Thông báo: {$result['message']}\n";
    
    if (!empty($result['transfers_created'])) {
        echo "Đã tạo " . count($result['transfers_created']) . " phiếu chuyển kho:\n";
        foreach ($result['transfers_created'] as $transfer) {
            echo "- {$transfer['transfer_code']}: {$transfer['from_store']} → {$transfer['to_warehouse']}\n";
        }
    }
} catch (Exception $e) {
    echo "LỖI: {$e->getMessage()}\n";
}

echo "\n=== KẾT THÚC DEBUG ===\n";