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
        // Chỉ kiểm tra đơn hàng ở trạng thái processing
        if ($order->status !== Order::STATUS_PROCESSING) {
            return [
                'can_assign' => false,
                'reason' => 'Chỉ có thể gán shipper cho đơn hàng đang ở trạng thái "Đang xử lý".',
                'requires_transfer' => false,
                'requires_external_shipping' => false
            ];
        }
        
        // Kiểm tra xem có cần chuyển kho không
        $transferCheck = $this->checkStockTransferRequirement($order);
        
        // Nếu là trường hợp đặc biệt (không có kho tại tỉnh đích), không gán shipper
        if (isset($transferCheck['is_special_case']) && $transferCheck['is_special_case']) {
            return [
                'can_assign' => false,
                'reason' => $transferCheck['shipping_type'],
                'shipping_fee' => $transferCheck['shipping_fee'],
                'requires_external_shipping' => true
            ];
        }
        
        if ($transferCheck['requires_transfer']) {
            // Kiểm tra xem phiếu chuyển kho đã hoàn thành chưa
            $transferStatus = $this->checkTransferStatus($order);
            
            if (!$transferStatus['completed']) {
                return [
                    'can_assign' => false,
                    'reason' => $transferStatus['reason'],
                    'requires_transfer' => true,
                    'transfer_info' => $transferStatus['transfer_info'] ?? null,
                    'estimated_arrival' => $transferStatus['estimated_arrival'] ?? null,
                    'requires_external_shipping' => false
                ];
            }
        }
        
        // Kiểm tra tồn kho tại kho đích
        $stockCheck = $this->checkDestinationStock($order);
        
        if (!$stockCheck['has_stock']) {
            return [
                'can_assign' => false,
                'reason' => $stockCheck['reason'],
                'requires_transfer' => true,
                'requires_external_shipping' => false
            ];
        }
        
        return [
            'can_assign' => true,
            'reason' => 'Đơn hàng sẵn sàng gán shipper',
            'requires_transfer' => false,
            'requires_external_shipping' => false,
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
            // Không có warehouse trong tỉnh đích - kiểm tra 2 trường hợp đặc biệt
            $specialCase = $this->checkSpecialShippingCase($order);
            
            return [
                'requires_transfer' => true,
                'reason' => 'Không có warehouse trong tỉnh đích',
                'target_province' => $destinationProvince->code,
                'is_special_case' => $specialCase['is_special'],
                'shipping_fee' => $specialCase['shipping_fee'],
                'shipping_type' => $specialCase['shipping_type']
            ];
        }
        
        // Kiểm tra warehouse có đủ hàng không
        $hasStock = $this->checkWarehouseStock($order, $warehouseInProvince->id);
        
        if (!$hasStock) {
            return [
                'requires_transfer' => true,
                'reason' => 'Warehouse trong tỉnh không có đủ hàng',
                'target_warehouse' => $warehouseInProvince->id,
                'target_province' => $destinationProvince->code,
                'is_special_case' => false
            ];
        }
        
        return [
            'requires_transfer' => false, 
            'reason' => 'Đã có đủ hàng trong tỉnh đích',
            'is_special_case' => false
        ];
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
     * Kiểm tra 2 trường hợp đặc biệt về phí giao hàng
     * 
     * @param Order $order
     * @return array
     */
    private function checkSpecialShippingCase(Order $order): array
    {
        try {
            $destinationProvinceCode = $order->shipping_old_province_code;
            $destinationProvince = ProvinceOld::where('code', $destinationProvinceCode)->first();
            
            if (!$destinationProvince) {
                return [
                    'is_special' => false,
                    'shipping_fee' => 0,
                    'shipping_type' => 'Không xác định'
                ];
            }
            
            // Tìm warehouse gần nhất để xác định vùng miền gốc
            $nearestWarehouse = StoreLocation::join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                ->where('store_locations.is_active', true)
                ->where('store_locations.type', 'warehouse')
                ->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationProvince->region])
                ->select('store_locations.*', 'provinces_old.region as origin_region')
                ->first();
            
            if (!$nearestWarehouse) {
                return [
                    'is_special' => false,
                    'shipping_fee' => 0,
                    'shipping_type' => 'Không tìm thấy warehouse'
                ];
            }
            
            // Kiểm tra 2 trường hợp đặc biệt
            if ($nearestWarehouse->origin_region === $destinationProvince->region) {
                // Trường hợp 1: Cùng vùng miền - Phí 25,000 VNĐ
                return [
                    'is_special' => true,
                    'shipping_fee' => 25000,
                    'shipping_type' => 'Cùng vùng miền - Không có kho tại tỉnh đích'
                ];
            } else {
                // Trường hợp 2: Khác vùng miền - Phí 40,000 VNĐ
                return [
                    'is_special' => true,
                    'shipping_fee' => 40000,
                    'shipping_type' => 'Khác vùng miền - Không có kho tại tỉnh đích'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Error checking special shipping case: ' . $e->getMessage());
            return [
                'is_special' => false,
                'shipping_fee' => 0,
                'shipping_type' => 'Lỗi kiểm tra'
            ];
        }
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
        
        // Không tạo phiếu chuyển kho cho 2 trường hợp đặc biệt
        if (isset($transferCheck['is_special_case']) && $transferCheck['is_special_case']) {
            Log::info("Đơn hàng {$order->order_code} thuộc trường hợp đặc biệt, không tạo phiếu chuyển kho");
            return [
                'created' => false,
                'reason' => 'Trường hợp đặc biệt - không cần chuyển kho',
                'special_shipping' => [
                    'fee' => $transferCheck['shipping_fee'],
                    'type' => $transferCheck['shipping_type']
                ]
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