<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\User;
use App\Models\ProductInventory;
use App\Models\InventorySerial;
use App\Models\InventoryMovement;
use App\Models\ShippingTransitTime;
use App\Models\ProvinceOld;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service quản lý workflow chuyển kho tự động
 * Xử lý quy trình: Tạo phiếu chuyển -> Chờ hàng về -> Gán shipper
 */
class StockTransferWorkflowService
{
    /**
     * Xử lý workflow hoàn chỉnh cho một phiếu chuyển kho
     *
     * @param StockTransfer $stockTransfer
     * @return array
     */
    public function processTransferWorkflow(StockTransfer $stockTransfer): array
    {
        try {
            DB::transaction(function () use ($stockTransfer) {
                // Bước 1: Xuất hàng từ kho nguồn
                $this->processDispatch($stockTransfer);
                
                // Kiểm tra xem có cùng tỉnh thành không
                $fromLocation = StoreLocation::find($stockTransfer->from_location_id);
                $toLocation = StoreLocation::find($stockTransfer->to_location_id);
                
                if ($fromLocation->province_code === $toLocation->province_code) {
                    // Cùng tỉnh: Xử lý ngay lập tức
                    $this->processReceive($stockTransfer);
                } else {
                    // Khác tỉnh: Chỉ chuyển sang trạng thái vận chuyển, không nhận hàng ngay
                    $this->processInTransit($stockTransfer);
                    // Hàng sẽ được nhận tự động bởi ProcessStockTransferArrival job
                    // dựa trên thời gian vận chuyển thực tế
                }
                
                // Bước 3: Gán shipper nếu cùng tỉnh với đơn hàng
                $this->assignShipperIfSameProvince($stockTransfer);
            });
            
            return [
                'success' => true,
                'message' => 'Xử lý workflow chuyển kho thành công',
                'transfer_id' => $stockTransfer->id
            ];
            
        } catch (Exception $e) {
            Log::error('Error processing transfer workflow: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi xử lý workflow: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Xử lý xuất hàng từ kho nguồn
     *
     * @param StockTransfer $stockTransfer
     * @return void
     */
    private function processDispatch(StockTransfer $stockTransfer): void
    {
        if ($stockTransfer->status !== 'pending') {
            throw new Exception('Phiếu chuyển kho không ở trạng thái chờ xử lý');
        }
        
        $fromLocationId = $stockTransfer->from_location_id;
        
        // Kiểm tra tồn kho và xử lý từng item
        foreach ($stockTransfer->items as $stItem) {
            $productVariant = $stItem->productVariant;
            $quantity = $stItem->quantity;
            
            // Kiểm tra tồn kho tại kho nguồn
            $inventory = ProductInventory::where('product_variant_id', $productVariant->id)
                ->where('store_location_id', $fromLocationId)
                ->where('inventory_type', 'new')
                ->first();
                
            if (!$inventory || $inventory->quantity < $quantity) {
                throw new Exception("Không đủ hàng tại kho nguồn cho sản phẩm {$productVariant->sku}");
            }
            
            // Xử lý IMEI/Serial tracking nếu sản phẩm có theo dõi serial
            if ($productVariant->has_serial_tracking) {
                // Tự động lấy serial có sẵn tại kho nguồn
                $availableSerials = InventorySerial::where('product_variant_id', $productVariant->id)
                    ->where('store_location_id', $fromLocationId)
                    ->where('status', 'available')
                    ->limit($quantity)
                    ->get();
                    
                if ($availableSerials->count() < $quantity) {
                    throw new Exception("Không đủ serial có sẵn cho sản phẩm {$productVariant->sku}. Cần {$quantity}, chỉ có {$availableSerials->count()}");
                }
                
                // Cập nhật trạng thái serial thành 'transferred'
                $serialIds = $availableSerials->pluck('id');
                InventorySerial::whereIn('id', $serialIds)
                    ->update(['status' => 'transferred']);
                
                // Lưu thông tin serial vào bảng trung gian
                foreach ($availableSerials as $serial) {
                    $stItem->serials()->create([
                        'inventory_serial_id' => $serial->id,
                        'status' => 'in_transit'
                    ]);
                }
                
                Log::info("Assigned {$availableSerials->count()} serials for product {$productVariant->sku} in transfer {$stockTransfer->id}");
            }
            
            // Trừ tồn kho tại kho nguồn
            $inventory->decrement('quantity', $quantity);
            
            // Ghi lại lịch sử xuất kho
            InventoryMovement::create([
                'product_variant_id' => $productVariant->id,
                'store_location_id' => $fromLocationId,
                'inventory_type' => 'new',
                'quantity_change' => -$quantity,
                'quantity_after_change' => $inventory->quantity,
                'reason' => 'stock_transfer_out',
                'reference_type' => 'stock_transfer',
                'reference_id' => $stockTransfer->id,
                'notes' => "Xuất hàng cho phiếu chuyển kho {$stockTransfer->transfer_code}"
            ]);
        }
        
        // Cập nhật trạng thái
        $stockTransfer->update([
            'status' => 'dispatched',
            'dispatched_at' => now(),
            'dispatched_by' => auth()->id() ?? 1 // System user
        ]);
        
        Log::info("Stock transfer {$stockTransfer->id} dispatched successfully with serial tracking");
    }
    
    /**
     * Xử lý trạng thái vận chuyển (cho trường hợp khác tỉnh thành)
     *
     * @param StockTransfer $stockTransfer
     * @return void
     */
    private function processInTransit(StockTransfer $stockTransfer): void
    {
        if ($stockTransfer->status !== 'dispatched') {
            throw new Exception('Phiếu chuyển kho chưa được xuất hàng');
        }
        
        $fromLocation = StoreLocation::find($stockTransfer->from_location_id);
        $toLocation = StoreLocation::find($stockTransfer->to_location_id);
        
        // Tính thời gian vận chuyển sử dụng ShippingTransitTime
        $transitTime = ShippingTransitTime::getTransitTime(
            'store_shipper',
            $fromLocation->province_code,
            $toLocation->province_code
        );
        
        // Nếu không có dữ liệu, sử dụng giá trị mặc định dựa trên vùng miền
        if (!$transitTime) {
            $fromProvince = ProvinceOld::where('code', $fromLocation->province_code)->first();
            $toProvince = ProvinceOld::where('code', $toLocation->province_code)->first();
            
            $transitDays = $this->calculateDefaultTransitTime($fromProvince, $toProvince);
        } else {
            $transitDays = $transitTime->transit_days_max; // Sử dụng thời gian tối đa để đảm bảo
        }
        
        // Tính thời gian dự kiến đến
        $expectedArrivalTime = now()->addDays($transitDays);
        
        // Cập nhật trạng thái vận chuyển
        $stockTransfer->update([
            'status' => 'in_transit',
            'shipped_at' => now()
        ]);
        
        Log::info("Stock transfer {$stockTransfer->id} is now in transit, expected arrival: {$expectedArrivalTime}");
    }
    
    /**
     * Xử lý nhận hàng tại kho đích
     *
     * @param StockTransfer $stockTransfer
     * @return void
     */
    private function processReceive(StockTransfer $stockTransfer): void
    {
        if (!in_array($stockTransfer->status, ['dispatched', 'in_transit'])) {
            throw new Exception('Phiếu chuyển kho chưa được xuất hàng hoặc đang vận chuyển');
        }
        
        $toLocationId = $stockTransfer->to_location_id;
        
        // Xử lý từng item trong phiếu chuyển kho
        foreach ($stockTransfer->items as $stItem) {
            $productVariant = $stItem->productVariant;
            $quantity = $stItem->quantity;
            
            // Xử lý IMEI/Serial tracking nếu sản phẩm có theo dõi serial
            if ($productVariant->has_serial_tracking) {
                // Lấy ra các serial đã được gửi đi cho item này
                $shippedSerials = $stItem->serials()->with('inventorySerial')->get();
                
                if ($shippedSerials->count() > 0) {
                    // Cập nhật trạng thái và vị trí mới cho serial
                    $shippedSerialIds = $shippedSerials->pluck('inventory_serial_id');
                    InventorySerial::whereIn('id', $shippedSerialIds)
                                    ->update([
                                        'status' => 'available',
                                        'store_location_id' => $toLocationId
                                    ]);
                    
                    // Cập nhật trạng thái trong bảng trung gian
                    $stItem->serials()->update(['status' => 'received']);
                    
                    Log::info("Updated {$shippedSerials->count()} serials for product {$productVariant->sku} in transfer {$stockTransfer->id}");
                }
            }
            
            // Tăng tồn kho tại kho nhận cho TẤT CẢ sản phẩm
            $inventory = ProductInventory::firstOrCreate(
                [
                    'product_variant_id' => $productVariant->id,
                    'store_location_id' => $toLocationId,
                    'inventory_type' => 'new',
                ],
                ['quantity' => 0]
            );
            $inventory->increment('quantity', $quantity);
            
            // Ghi lại lịch sử nhập kho
            InventoryMovement::create([
                'product_variant_id' => $productVariant->id,
                'store_location_id' => $toLocationId,
                'inventory_type' => 'new',
                'quantity_change' => $quantity,
                'quantity_after_change' => $inventory->quantity,
                'reason' => 'stock_transfer_in',
                'reference_type' => 'stock_transfer',
                'reference_id' => $stockTransfer->id,
                'notes' => "Nhận hàng từ phiếu chuyển kho {$stockTransfer->transfer_code}"
            ]);
        }
        
        // Cập nhật trạng thái
        $stockTransfer->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by' => auth()->id() ?? 1 // System user
        ]);
        
        Log::info("Stock transfer {$stockTransfer->id} received successfully with serial tracking");
    }
    
    /**
     * Gán shipper nếu kho đích cùng tỉnh với đơn hàng
     *
     * @param StockTransfer $stockTransfer
     * @return void
     */
    private function assignShipperIfSameProvince(StockTransfer $stockTransfer): void
    {
        if ($stockTransfer->status !== 'received') {
            return;
        }
        
        // Lấy thông tin đơn hàng liên quan (nếu có)
        $relatedOrder = $this->findRelatedOrder($stockTransfer);
        if (!$relatedOrder) {
            return;
        }
        
        $destinationLocation = StoreLocation::find($stockTransfer->to_location_id);
        
        // Kiểm tra xem kho đích có cùng tỉnh với địa chỉ giao hàng không
        if ($destinationLocation->province_code === $relatedOrder->shipping_old_province_code) {
            $shipper = $this->findAvailableShipper($destinationLocation->province_code);
            
            if ($shipper) {
                // Gán shipper cho đơn hàng
                $relatedOrder->update([
                    'assigned_shipper_id' => $shipper->id,
                    'fulfillment_location_id' => $destinationLocation->id,
                    'shipping_method' => 'internal'
                ]);
                
                Log::info("Assigned shipper {$shipper->id} to order {$relatedOrder->id} after stock transfer");
            }
        }
    }
    
    /**
     * Tìm đơn hàng liên quan đến phiếu chuyển kho
     *
     * @param StockTransfer $stockTransfer
     * @return Order|null
     */
    private function findRelatedOrder(StockTransfer $stockTransfer): ?Order
    {
        // Tìm đơn hàng dựa trên ghi chú hoặc metadata
        if ($stockTransfer->notes && str_contains($stockTransfer->notes, 'Order:')) {
            $orderCode = trim(str_replace('Order:', '', $stockTransfer->notes));
            return Order::where('order_code', $orderCode)->first();
        }
        
        return null;
    }
    
    /**
     * Tìm shipper khả dụng trong tỉnh
     *
     * @param string $provinceCode
     * @return User|null
     */
    private function findAvailableShipper(string $provinceCode): ?User
    {
        return User::where('role', 'shipper')
            ->where('province_code', $provinceCode)
            ->where('is_active', true)
            ->whereDoesntHave('assignedOrders', function($query) {
                $query->whereIn('status', ['pending', 'processing', 'shipped']);
            })
            ->first();
    }
    
    /**
     * Kiểm tra xem có thể tự động xử lý phiếu chuyển kho không
     *
     * @param StockTransfer $stockTransfer
     * @return bool
     */
    public function canAutoProcess(StockTransfer $stockTransfer): bool
    {
        // Cho phép tự động xử lý tất cả phiếu chuyển kho hợp lệ
        $fromLocation = StoreLocation::find($stockTransfer->from_location_id);
        $toLocation = StoreLocation::find($stockTransfer->to_location_id);
        
        // Chỉ cần kiểm tra tồn tại của cả 2 địa điểm
        return $fromLocation && $toLocation;
    }
    
    /**
     * Nhận hàng ngay lập tức (public method)
     *
     * @param StockTransfer $stockTransfer
     * @return array
     */
    public function receiveTransfer(StockTransfer $stockTransfer): array
    {
        try {
            DB::transaction(function () use ($stockTransfer) {
                $this->processReceive($stockTransfer);
            });
            
            return [
                'success' => true,
                'message' => 'Nhận hàng thành công'
            ];
            
        } catch (Exception $e) {
            Log::error('Error receiving transfer: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi nhận hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thống kê workflow chuyển kho
     *
     * @param array $filters
     * @return array
     */
    public function getWorkflowStatistics(array $filters = []): array
    {
        $query = StockTransfer::query();
        
        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        
        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        
        $stats = [
            'total_transfers' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'dispatched' => (clone $query)->where('status', 'dispatched')->count(),
            'received' => (clone $query)->where('status', 'received')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'auto_processed' => (clone $query)->where('is_auto_processed', true)->count(),
        ];
        
        return $stats;
    }
    
    /**
     * Hủy phiếu chuyển kho và hoàn tồn kho
     *
     * @param StockTransfer $stockTransfer
     * @return array
     */
    public function cancelTransfer(StockTransfer $stockTransfer): array
    {
        try {
            DB::transaction(function () use ($stockTransfer) {
                if ($stockTransfer->status === 'dispatched') {
                    // Hoàn tồn kho về kho nguồn
                    foreach ($stockTransfer->items as $item) {
                        $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
                            ->where('store_location_id', $stockTransfer->from_location_id)
                            ->where('inventory_type', 'new')
                            ->first();
                            
                        if ($inventory) {
                            $inventory->increment('quantity', $item->quantity);
                        }
                    }
                } elseif ($stockTransfer->status === 'received') {
                    // Trừ tồn kho tại kho đích
                    foreach ($stockTransfer->items as $item) {
                        $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
                            ->where('store_location_id', $stockTransfer->to_location_id)
                            ->where('inventory_type', 'new')
                            ->first();
                            
                        if ($inventory) {
                            $inventory->decrement('quantity', $item->quantity);
                        }
                    }
                }
                
                $stockTransfer->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id()
                ]);
            });
            
            return [
                'success' => true,
                'message' => 'Hủy phiếu chuyển kho thành công'
            ];
            
        } catch (Exception $e) {
            Log::error('Error cancelling transfer: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi hủy phiếu chuyển kho: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Tính thời gian vận chuyển mặc định dựa trên vùng miền
     *
     * @param $fromProvince
     * @param $toProvince
     * @return int
     */
    private function calculateDefaultTransitTime($fromProvince, $toProvince): int
    {
        if (!$fromProvince || !$toProvince) {
            return 3; // Mặc định 3 ngày nếu không tìm thấy thông tin tỉnh
        }
        
        // Cùng vùng miền: 1-2 ngày
        if ($fromProvince->region === $toProvince->region) {
            return 2;
        }
        
        // Khác vùng miền: 3-5 ngày
        return 4;
    }
}