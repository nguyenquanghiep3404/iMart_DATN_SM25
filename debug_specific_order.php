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

echo "=== DEBUG: Kiểm tra đơn hàng cụ thể ===\n\n";

// Kiểm tra đơn hàng DH-P8IEQ4BD6H (đã có phiếu chuyển kho)
echo "1. KIỂM TRA ĐƠN HÀNG DH-P8IEQ4BD6H:\n";
$order1 = Order::where('order_code', 'DH-P8IEQ4BD6H')->with(['items.productVariant'])->first();

if ($order1) {
    echo "- Trạng thái: {$order1->status}\n";
    echo "- Thanh toán: {$order1->payment_status}\n";
    echo "- Tỉnh giao hàng: {$order1->shipping_old_province_code}\n";
    
    // Kiểm tra phiếu chuyển kho liên quan
    $transfers = StockTransfer::where('notes', 'LIKE', '%' . $order1->order_code . '%')->get();
    echo "- Số phiếu chuyển kho: " . $transfers->count() . "\n";
    foreach ($transfers as $transfer) {
        echo "  + {$transfer->transfer_code}: {$transfer->status}\n";
    }
    
    // Kiểm tra tỉnh đích
    $destinationProvince = ProvinceOld::find($order1->shipping_old_province_code);
    if ($destinationProvince) {
        echo "- Tỉnh đích: {$destinationProvince->name} (Vùng: {$destinationProvince->region})\n";
        
        // Kiểm tra warehouse trong tỉnh
        $warehouseInProvince = StoreLocation::where('type', 'warehouse')
            ->where('province_code', $destinationProvince->code)
            ->where('is_active', true)
            ->first();
            
        if ($warehouseInProvince) {
            echo "- Warehouse trong tỉnh: {$warehouseInProvince->name}\n";
            
            // Kiểm tra tồn kho tại warehouse này
            foreach ($order1->items as $item) {
                $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
                    ->where('store_location_id', $warehouseInProvince->id)
                    ->where('inventory_type', 'new')
                    ->first();
                    
                $currentStock = $inventory ? $inventory->quantity : 0;
                echo "  + Sản phẩm " . ($item->productVariant ? $item->productVariant->sku : 'N/A') . ": cần {$item->quantity}, có {$currentStock}\n";
                
                if ($currentStock < $item->quantity) {
                    echo "    → THIẾU HÀNG: cần chuyển " . ($item->quantity - $currentStock) . " cái\n";
                }
            }
        } else {
            echo "- KHÔNG có warehouse trong tỉnh\n";
        }
    }
} else {
    echo "- KHÔNG tìm thấy đơn hàng\n";
}

echo "\n\n2. KIỂM TRA ĐƠN HÀNG KHÁC CHƯA CÓ PHIẾU CHUYỂN KHO:\n";

// Tìm đơn hàng chưa có phiếu chuyển kho
$ordersWithoutTransfer = Order::whereNotIn('id', function($query) {
    $query->select('orders.id')
        ->from('orders')
        ->join('stock_transfers', 'stock_transfers.notes', 'LIKE', DB::raw("CONCAT('%', orders.order_code, '%')"))
        ->where('stock_transfers.transfer_code', 'LIKE', 'AUTO-%');
})
->where('status', '!=', 'cancelled')
->where('payment_status', 'paid')
->with(['items.productVariant'])
->orderBy('created_at', 'desc')
->limit(3)
->get();

if ($ordersWithoutTransfer->isEmpty()) {
    echo "- Tất cả đơn hàng đã thanh toán đều có phiếu chuyển kho hoặc không cần\n";
} else {
    foreach ($ordersWithoutTransfer as $order) {
        echo "\n- Đơn hàng: {$order->order_code}\n";
        echo "  Trạng thái: {$order->status}\n";
        echo "  Thanh toán: {$order->payment_status}\n";
        echo "  Tỉnh giao hàng: {$order->shipping_old_province_code}\n";
        
        // Test tạo phiếu chuyển kho
        try {
            $autoTransferService = new AutoStockTransferService();
            $result = $autoTransferService->checkAndCreateAutoTransfer($order);
            
            echo "  Kết quả tạo phiếu: " . ($result['success'] ? 'THÀNH CÔNG' : 'THẤT BẠI') . "\n";
            echo "  Lý do: {$result['message']}\n";
            
            if (!empty($result['transfers_created'])) {
                echo "  Đã tạo " . count($result['transfers_created']) . " phiếu:\n";
                foreach ($result['transfers_created'] as $transfer) {
                    echo "    + {$transfer['transfer_code']}\n";
                }
            }
        } catch (Exception $e) {
            echo "  LỖI: {$e->getMessage()}\n";
        }
    }
}

echo "\n\n3. KIỂM TRA LOGIC TẠO PHIẾU CHUYỂN KHO:\n";

// Kiểm tra điều kiện tạo phiếu chuyển kho
echo "- Điều kiện để tạo phiếu chuyển kho tự động:\n";
echo "  1. Đơn hàng đã thanh toán (payment_status = 'paid')\n";
echo "  2. Tỉnh giao hàng không có warehouse hoặc warehouse không đủ hàng\n";
echo "  3. Có nguồn hàng từ warehouse/store khác\n";
echo "  4. Chưa có phiếu chuyển kho cho đơn hàng này\n";

// Thống kê
echo "\n\n4. THỐNG KÊ:\n";
$totalOrders = Order::count();
$paidOrders = Order::where('payment_status', 'paid')->count();
$autoTransfers = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')->count();

echo "- Tổng đơn hàng: {$totalOrders}\n";
echo "- Đơn hàng đã thanh toán: {$paidOrders}\n";
echo "- Phiếu chuyển kho tự động: {$autoTransfers}\n";

echo "\n=== KẾT THÚC DEBUG ===\n";