<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransfer;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\ProductInventory;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG LOGIC CẬP NHẬT FULFILLMENT ===\n\n";

// Lấy phiếu chuyển kho cụ thể
$transferCode = 'AUTO-IM250826000076IVVO-0AFB';
$transfer = StockTransfer::where('transfer_code', $transferCode)->first();

if (!$transfer) {
    echo "Không tìm thấy phiếu chuyển kho: {$transferCode}\n";
    exit;
}

echo "Phiếu chuyển kho: {$transfer->transfer_code}\n";
echo "Trạng thái: {$transfer->status}\n";
echo "Ghi chú: {$transfer->notes}\n\n";

// Bước 1: Tìm đơn hàng liên quan
echo "=== BƯỚC 1: TÌM ĐƠN HÀNG LIÊN QUAN ===\n";
if ($transfer->notes && str_contains($transfer->notes, 'Order:')) {
    // Tách mã đơn hàng từ ghi chú
    $parts = explode('Order:', $transfer->notes);
    if (count($parts) > 1) {
        $orderPart = trim($parts[1]);
        // Lấy phần trước dấu cách hoặc dấu ngoặc
        $orderCode = explode(' ', $orderPart)[0];
        $orderCode = explode('(', $orderCode)[0];
        $orderCode = trim($orderCode);
        
        echo "Mã đơn hàng từ ghi chú: {$orderCode}\n";
        
        $order = Order::where('order_code', $orderCode)->first();
        if ($order) {
            echo "✓ Tìm thấy đơn hàng: {$order->order_code}\n";
            echo "Trạng thái đơn hàng: {$order->status}\n";
            echo "Tỉnh giao hàng: {$order->shipping_old_province_code}\n\n";
        } else {
            echo "✗ Không tìm thấy đơn hàng với mã: {$orderCode}\n";
            exit;
        }
    } else {
        echo "✗ Không thể tách mã đơn hàng từ ghi chú\n";
        exit;
    }
} else {
    echo "✗ Ghi chú không chứa thông tin đơn hàng\n";
    exit;
}

// Bước 2: Kiểm tra kho đích
echo "=== BƯỚC 2: KIỂM TRA KHO ĐÍCH ===\n";
$destinationLocation = StoreLocation::find($transfer->to_location_id);
if (!$destinationLocation) {
    echo "✗ Không tìm thấy kho đích\n";
    exit;
}

echo "Kho đích: {$destinationLocation->name} (ID: {$destinationLocation->id})\n";
echo "Tỉnh kho đích: {$destinationLocation->province_code}\n";
echo "Tỉnh giao hàng: {$order->shipping_old_province_code}\n";

if ($destinationLocation->province_code === $order->shipping_old_province_code) {
    echo "✓ Kho đích cùng tỉnh với địa chỉ giao hàng\n\n";
} else {
    echo "✗ Kho đích KHÔNG cùng tỉnh với địa chỉ giao hàng\n";
    echo "Logic sẽ không chạy vì điều kiện cùng tỉnh không thỏa mãn\n";
    exit;
}

// Bước 3: Tìm fulfillments
echo "=== BƯỚC 3: TÌM FULFILLMENTS ===\n";
$fulfillments = $order->fulfillments()
    ->where('store_location_id', $destinationLocation->id)
    ->where('status', '!=', 'packed')
    ->with(['items.orderItem.productVariant'])
    ->get();

echo "Số fulfillments tìm thấy: {$fulfillments->count()}\n";

if ($fulfillments->isEmpty()) {
    echo "✗ Không tìm thấy fulfillment nào phù hợp\n";
    echo "Điều kiện: store_location_id = {$destinationLocation->id} AND status != 'packed'\n";
    
    // Kiểm tra tất cả fulfillments của đơn hàng
    $allFulfillments = $order->fulfillments()->get();
    echo "\nTất cả fulfillments của đơn hàng:\n";
    foreach ($allFulfillments as $f) {
        echo "- ID: {$f->id}, Store: {$f->store_location_id}, Status: {$f->status}\n";
    }
    exit;
}

foreach ($fulfillments as $fulfillment) {
    echo "\n--- Fulfillment ID: {$fulfillment->id} ---\n";
    echo "Trạng thái hiện tại: {$fulfillment->status}\n";
    echo "Kho: {$fulfillment->store_location_id}\n";
    echo "Tracking: {$fulfillment->tracking_code}\n";
    
    // Bước 4: Kiểm tra tồn kho
    echo "\n=== BƯỚC 4: KIỂM TRA TỒN KHO ===\n";
    $hasStock = true;
    
    foreach ($fulfillment->items as $item) {
        $productVariantId = $item->orderItem->product_variant_id;
        $inventory = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('store_location_id', $destinationLocation->id)
            ->where('inventory_type', 'new')
            ->first();
            
        $availableQty = $inventory ? $inventory->quantity : 0;
        $requiredQty = $item->quantity;
        
        echo "Sản phẩm {$item->orderItem->productVariant->sku}:\n";
        echo "  Cần: {$requiredQty}\n";
        echo "  Có: {$availableQty}\n";
        echo "  Đủ: " . ($availableQty >= $requiredQty ? 'Có' : 'Không') . "\n";
        
        if ($availableQty < $requiredQty) {
            $hasStock = false;
        }
    }
    
    echo "\nTổng kết tồn kho: " . ($hasStock ? 'Đủ hàng' : 'Thiếu hàng') . "\n";
    
    if ($hasStock) {
        echo "\n=== BƯỚC 5: CẬP NHẬT TRẠNG THÁI ===\n";
        echo "Trạng thái trước khi cập nhật: {$fulfillment->status}\n";
        
        // Thử cập nhật
        $result = $fulfillment->update(['status' => 'packed']);
        
        // Reload để kiểm tra
        $fulfillment->refresh();
        
        echo "Kết quả update: " . ($result ? 'Thành công' : 'Thất bại') . "\n";
        echo "Trạng thái sau khi cập nhật: {$fulfillment->status}\n";
        
        if ($fulfillment->status === 'packed') {
            echo "✓ Cập nhật thành công!\n";
        } else {
            echo "✗ Cập nhật thất bại - trạng thái không thay đổi\n";
        }
    } else {
        echo "\n✗ Không đủ tồn kho - bỏ qua fulfillment này\n";
    }
}

echo "\n=== KẾT THÚC DEBUG ===\n";