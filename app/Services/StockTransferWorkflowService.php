<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\User;
use App\Models\ProductInventory;
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
                
                // Bước 2: Nhận hàng tại kho đích
                $this->processReceive($stockTransfer);
                
                // Bước 3: Gán shipper nếu cùng tỉnh
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
        
        // Kiểm tra tồn kho tại kho nguồn
        foreach ($stockTransfer->items as $item) {
            $inventory = ProductInventory::where('product_id', $item->product_id)
                ->where('store_location_id', $stockTransfer->from_location_id)
                ->first();
                
            if (!$inventory || $inventory->quantity < $item->quantity) {
                throw new Exception("Không đủ hàng tại kho nguồn cho sản phẩm {$item->product->name}");
            }
        }
        
        // Trừ tồn kho tại kho nguồn
        foreach ($stockTransfer->items as $item) {
            $inventory = ProductInventory::where('product_id', $item->product_id)
                ->where('store_location_id', $stockTransfer->from_location_id)
                ->first();
                
            $inventory->decrement('quantity', $item->quantity);
        }
        
        // Cập nhật trạng thái
        $stockTransfer->update([
            'status' => 'dispatched',
            'dispatched_at' => now(),
            'dispatched_by' => auth()->id() ?? 1 // System user
        ]);
        
        Log::info("Stock transfer {$stockTransfer->id} dispatched successfully");
    }
    
    /**
     * Xử lý nhận hàng tại kho đích
     *
     * @param StockTransfer $stockTransfer
     * @return void
     */
    private function processReceive(StockTransfer $stockTransfer): void
    {
        if ($stockTransfer->status !== 'dispatched') {
            throw new Exception('Phiếu chuyển kho chưa được xuất hàng');
        }
        
        // Cộng tồn kho tại kho đích
        foreach ($stockTransfer->items as $item) {
            $inventory = ProductInventory::firstOrCreate(
                [
                    'product_id' => $item->product_id,
                    'store_location_id' => $stockTransfer->to_location_id
                ],
                ['quantity' => 0]
            );
            
            $inventory->increment('quantity', $item->quantity);
        }
        
        // Cập nhật trạng thái
        $stockTransfer->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by' => auth()->id() ?? 1 // System user
        ]);
        
        Log::info("Stock transfer {$stockTransfer->id} received successfully");
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
        // Chỉ tự động xử lý nếu cùng tỉnh thành
        $fromLocation = StoreLocation::find($stockTransfer->from_location_id);
        $toLocation = StoreLocation::find($stockTransfer->to_location_id);
        
        return $fromLocation && $toLocation && 
               $fromLocation->province_code === $toLocation->province_code;
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
                        $inventory = ProductInventory::where('product_id', $item->product_id)
                            ->where('store_location_id', $stockTransfer->from_location_id)
                            ->first();
                            
                        if ($inventory) {
                            $inventory->increment('quantity', $item->quantity);
                        }
                    }
                } elseif ($stockTransfer->status === 'received') {
                    // Trừ tồn kho tại kho đích
                    foreach ($stockTransfer->items as $item) {
                        $inventory = ProductInventory::where('product_id', $item->product_id)
                            ->where('store_location_id', $stockTransfer->to_location_id)
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
}