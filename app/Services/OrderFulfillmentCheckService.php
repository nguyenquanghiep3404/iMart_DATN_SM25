<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockTransfer;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Models\ShippingTransitTime;
use App\Services\AutoStockTransferService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service kiểm tra điều kiện fulfillment cho đơn hàng
 * Đảm bảo hàng đã về kho trước khi gán shipper
 */
class OrderFulfillmentCheckService
{
    protected $autoTransferService;
    
    public function __construct()
    {
        $this->autoTransferService = new AutoStockTransferService();
    }
    
    /**
     * Kiểm tra xem đơn hàng có thể gán shipper không
     * 
     * @param Order $order
     * @return array
     */
    public function canAssignShipper(Order $order): array
    {
        // Chỉ kiểm tra đơn hàng ở trạng thái awaiting_shipment
        if ($order->status !== Order::STATUS_AWAITING_SHIPMENT) {
            return [
                'can_assign' => false,
                'reason' => 'Đơn hàng không ở trạng thái chờ giao hàng',
                'requires_transfer' => false
            ];
        }
        
        // Kiểm tra xem có cần chuyển kho không
        $transferCheck = $this->checkStockTransferRequirement($order);
        
        if ($transferCheck['requires_transfer']) {
            // Kiểm tra xem phiếu chuyển kho đã hoàn thành chưa
            $transferStatus = $this->checkTransferStatus($order);
            
            if (!$transferStatus['completed']) {
                return [
                    'can_assign' => false,
                    'reason' => $transferStatus['reason'],
                    'requires_transfer' => true,
                    'transfer_info' => $transferStatus['transfer_info'] ?? null,
                    'estimated_arrival' => $transferStatus['estimated_arrival'] ?? null
                ];
            }
        }
        
        // Kiểm tra tồn kho tại kho đích
        $stockCheck = $this->checkDestinationStock($order);
        
        if (!$stockCheck['has_stock']) {
            return [
                'can_assign' => false,
                'reason' => $stockCheck['reason'],
                'requires_transfer' => true
            ];
        }
        
        return [
            'can_assign' => true,
            'reason' => 'Đơn hàng sẵn sàng gán shipper',
            'requires_transfer' => false,
            'fulfillment_location' => $stockCheck['location']
        ];
    }
    
    /**
     * Kiểm tra xem đơn hàng có cần chuyển kho không
     * 
     * @param Order $order
     * @return array
     */
    private function checkStockTransferRequirement(Order $order): array
    {
        $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
        
        if (!$destinationProvince) {
            return ['requires_transfer' => false, 'reason' => 'Không tìm thấy tỉnh đích'];
        }
        
        // Tìm warehouse trong tỉnh đích
        $warehouseInProvince = StoreLocation::where('type', 'warehouse')
            ->where('province_code', $destinationProvince->code)
            ->where('is_active', true)
            ->first();
            
        if (!$warehouseInProvince) {
            return [
                'requires_transfer' => true,
                'reason' => 'Không có warehouse trong tỉnh đích',
                'target_province' => $destinationProvince->code
            ];
        }
        
        // Kiểm tra warehouse có đủ hàng không
        $hasStock = $this->checkWarehouseStock($order, $warehouseInProvince->id);
        
        if (!$hasStock) {
            return [
                'requires_transfer' => true,
                'reason' => 'Warehouse trong tỉnh không có đủ hàng',
                'target_warehouse' => $warehouseInProvince->id,
                'target_province' => $destinationProvince->code
            ];
        }
        
        return ['requires_transfer' => false, 'reason' => 'Đã có đủ hàng trong tỉnh đích'];
    }
    
    /**
     * Kiểm tra trạng thái phiếu chuyển kho cho đơn hàng
     * 
     * @param Order $order
     * @return array
     */
    private function checkTransferStatus(Order $order): array
    {
        // Tìm phiếu chuyển kho liên quan đến đơn hàng
        $relatedTransfers = StockTransfer::where('notes', 'LIKE', '%Order:' . $order->order_code . '%')
            ->whereIn('status', ['pending', 'shipped', 'received'])
            ->get();
            
        if ($relatedTransfers->isEmpty()) {
            return [
                'completed' => false,
                'reason' => 'Chưa có phiếu chuyển kho cho đơn hàng này',
                'transfer_info' => null
            ];
        }
        
        $pendingTransfers = $relatedTransfers->where('status', 'pending');
        $shippedTransfers = $relatedTransfers->where('status', 'shipped');
        $receivedTransfers = $relatedTransfers->where('status', 'received');
        
        // Nếu có phiếu chuyển kho đã nhận, đơn hàng sẵn sàng
        if ($receivedTransfers->isNotEmpty()) {
            return [
                'completed' => true,
                'reason' => 'Phiếu chuyển kho đã hoàn thành',
                'transfer_info' => $receivedTransfers->first()
            ];
        }
        
        // Nếu có phiếu đang vận chuyển, tính thời gian dự kiến
        if ($shippedTransfers->isNotEmpty()) {
            $transfer = $shippedTransfers->first();
            $estimatedArrival = $this->calculateEstimatedArrival($transfer);
            
            return [
                'completed' => false,
                'reason' => 'Phiếu chuyển kho đang vận chuyển',
                'transfer_info' => $transfer,
                'estimated_arrival' => $estimatedArrival
            ];
        }
        
        // Nếu có phiếu chờ xử lý
        if ($pendingTransfers->isNotEmpty()) {
            return [
                'completed' => false,
                'reason' => 'Phiếu chuyển kho chờ xử lý',
                'transfer_info' => $pendingTransfers->first()
            ];
        }
        
        return [
            'completed' => false,
            'reason' => 'Trạng thái phiếu chuyển kho không xác định'
        ];
    }
    
    /**
     * Kiểm tra tồn kho tại kho đích
     * 
     * @param Order $order
     * @return array
     */
    private function checkDestinationStock(Order $order): array
    {
        $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
        
        if (!$destinationProvince) {
            return ['has_stock' => false, 'reason' => 'Không tìm thấy tỉnh đích'];
        }
        
        // Tìm warehouse trong tỉnh đích
        $warehouseInProvince = StoreLocation::where('type', 'warehouse')
            ->where('province_code', $destinationProvince->code)
            ->where('is_active', true)
            ->first();
            
        if (!$warehouseInProvince) {
            return [
                'has_stock' => false,
                'reason' => 'Không có warehouse trong tỉnh đích'
            ];
        }
        
        $hasStock = $this->checkWarehouseStock($order, $warehouseInProvince->id);
        
        return [
            'has_stock' => $hasStock,
            'reason' => $hasStock ? 'Có đủ hàng tại warehouse đích' : 'Không đủ hàng tại warehouse đích',
            'location' => $hasStock ? $warehouseInProvince : null
        ];
    }
    
    /**
     * Kiểm tra warehouse có đủ hàng cho đơn hàng không
     * 
     * @param Order $order
     * @param int $warehouseId
     * @return bool
     */
    private function checkWarehouseStock(Order $order, int $warehouseId): bool
    {
        foreach ($order->items as $item) {
            $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
                ->where('store_location_id', $warehouseId)
                ->where('inventory_type', 'new')
                ->first();
                
            if (!$inventory || $inventory->available_quantity < $item->quantity) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Tính thời gian dự kiến đến của phiếu chuyển kho
     * 
     * @param StockTransfer $transfer
     * @return Carbon|null
     */
    private function calculateEstimatedArrival(StockTransfer $transfer): ?Carbon
    {
        if (!$transfer->shipped_at) {
            return null;
        }
        
        $fromLocation = StoreLocation::find($transfer->from_location_id);
        $toLocation = StoreLocation::find($transfer->to_location_id);
        
        if (!$fromLocation || !$toLocation) {
            return null;
        }
        
        // Tính toán dựa trên thời gian vận chuyển
        $transitTime = ShippingTransitTime::where('from_province_code', $fromLocation->province_code)
            ->where('to_province_code', $toLocation->province_code)
            ->first();
            
        if ($transitTime) {
            return $transfer->shipped_at->addHours($transitTime->transit_time_hours);
        }
        
        // Fallback: 24 giờ nếu không có dữ liệu
        return $transfer->shipped_at->addHours(24);
    }
    
    /**
     * Tự động tạo phiếu chuyển kho nếu cần thiết
     * 
     * @param Order $order
     * @return array
     */
    public function createAutoTransferIfNeeded(Order $order): array
    {
        $transferCheck = $this->checkStockTransferRequirement($order);
        
        if (!$transferCheck['requires_transfer']) {
            return [
                'created' => false,
                'reason' => 'Không cần chuyển kho'
            ];
        }
        
        try {
            $result = $this->autoTransferService->checkAndCreateAutoTransfer($order);
            
            if ($result['success'] && !empty($result['transfers_created'])) {
                Log::info("Đã tạo phiếu chuyển kho tự động cho đơn hàng {$order->order_code}", [
                    'order_id' => $order->id,
                    'transfers' => $result['transfers_created']
                ]);
                
                return [
                    'created' => true,
                    'transfers' => $result['transfers_created'],
                    'reason' => 'Đã tạo phiếu chuyển kho tự động'
                ];
            }
            
            return [
                'created' => false,
                'reason' => $result['message'] ?? 'Không thể tạo phiếu chuyển kho'
            ];
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo phiếu chuyển kho tự động cho đơn hàng {$order->order_code}: {$e->getMessage()}");
            
            return [
                'created' => false,
                'reason' => 'Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage()
            ];
        }
    }
}