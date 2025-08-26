<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\ProductInventory;
use App\Services\StockTransferWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG UPDATE STATUS ===\n\n";

// Lấy phiếu chuyển kho
$stockTransfer = StockTransfer::where('transfer_code', 'AUTO-IM250826000076IVVO-0AFB')->first();

if (!$stockTransfer) {
    echo "Không tìm thấy phiếu chuyển kho\n";
    exit;
}

echo "Phiếu chuyển kho: {$stockTransfer->transfer_code}\n";
echo "Trạng thái: {$stockTransfer->status}\n";
echo "Từ kho: {$stockTransfer->from_location_id}\n";
echo "Đến kho: {$stockTransfer->to_location_id}\n\n";

// Tìm đơn hàng liên quan
if ($stockTransfer->notes && str_contains($stockTransfer->notes, 'Order:')) {
    $parts = explode('Order:', $stockTransfer->notes);
    if (count($parts) > 1) {
        $orderPart = trim($parts[1]);
        $orderCode = explode(' ', $orderPart)[0];
        $orderCode = explode('(', $orderCode)[0];
        $orderCode = trim($orderCode);
        
        $relatedOrder = Order::where('order_code', $orderCode)->first();
        
        if ($relatedOrder) {
            echo "Đơn hàng liên quan: {$relatedOrder->order_code}\n";
            echo "Tỉnh giao hàng: {$relatedOrder->shipping_old_province_code}\n\n";
            
            $destinationLocation = StoreLocation::find($stockTransfer->to_location_id);
            echo "Kho đích: {$destinationLocation->name}\n";
            echo "Tỉnh kho đích: {$destinationLocation->province_code}\n";
            echo "Cùng tỉnh: " . ($destinationLocation->province_code === $relatedOrder->shipping_old_province_code ? 'Có' : 'Không') . "\n\n";
            
            if ($destinationLocation->province_code === $relatedOrder->shipping_old_province_code) {
                // Tìm fulfillments
                $fulfillments = $relatedOrder->fulfillments()
                    ->where('store_location_id', $stockTransfer->from_location_id)
                    ->where('status', 'processing')
                    ->get();
                    
                echo "Số fulfillments tìm thấy: {$fulfillments->count()}\n";
                
                foreach ($fulfillments as $fulfillment) {
                    echo "\n--- Fulfillment ID: {$fulfillment->id} ---\n";
                    echo "Store Location hiện tại: {$fulfillment->store_location_id}\n";
                    echo "Status hiện tại: {$fulfillment->status}\n";
                    echo "Tracking: {$fulfillment->tracking_code}\n";
                    
                    // Kiểm tra tồn kho
                    $hasStock = true;
                    foreach ($fulfillment->items as $item) {
                        $inventory = ProductInventory::where('product_variant_id', $item->orderItem->product_variant_id)
                            ->where('store_location_id', $destinationLocation->id)
                            ->where('inventory_type', 'new')
                            ->first();
                            
                        $availableQty = $inventory ? $inventory->quantity : 0;
                        $requiredQty = $item->quantity;
                        
                        echo "  Sản phẩm {$item->orderItem->productVariant->sku}: Cần {$requiredQty}, Có {$availableQty}\n";
                        
                        if ($availableQty < $requiredQty) {
                            $hasStock = false;
                        }
                    }
                    
                    echo "Đủ tồn kho: " . ($hasStock ? 'Có' : 'Không') . "\n";
                    
                    if ($hasStock) {
                        echo "\n=== THỬ CẬP NHẬT FULFILLMENT ===\n";
                        
                        try {
                            DB::beginTransaction();
                            
                            $oldStoreLocation = $fulfillment->store_location_id;
                            $oldStatus = $fulfillment->status;
                            
                            $result = $fulfillment->update([
                                'store_location_id' => $destinationLocation->id,
                                'status' => 'packed'
                            ]);
                            
                            echo "Kết quả update: " . ($result ? 'Thành công' : 'Thất bại') . "\n";
                            
                            // Kiểm tra lại
                            $fulfillment->refresh();
                            echo "Store Location sau update: {$fulfillment->store_location_id}\n";
                            echo "Status sau update: {$fulfillment->status}\n";
                            
                            if ($fulfillment->store_location_id == $destinationLocation->id && $fulfillment->status == 'packed') {
                                echo "✓ CẬP NHẬT THÀNH CÔNG!\n";
                                DB::commit();
                            } else {
                                echo "✗ CẬP NHẬT THẤT BẠI - Rollback\n";
                                DB::rollback();
                            }
                            
                        } catch (Exception $e) {
                            DB::rollback();
                            echo "Lỗi khi cập nhật: {$e->getMessage()}\n";
                        }
                    }
                }
            }
        } else {
            echo "Không tìm thấy đơn hàng với mã: {$orderCode}\n";
        }
    }
}

echo "\n=== KẾT THÚC DEBUG ===\n";