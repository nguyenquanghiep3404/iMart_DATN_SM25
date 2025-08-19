<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Models\User;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service tối ưu hóa phương thức giao hàng
 * Ưu tiên shipper nội bộ nếu có kho trong tỉnh, dùng API bên thứ 3 nếu không có kho
 */
class DeliveryOptimizationService
{

    /**
     * Xác định phương thức giao hàng tối ưu cho đơn hàng
     *
     * @param Order $order
     * @return array
     */
    public function determineOptimalDeliveryMethod(Order $order): array
    {
        try {
            $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
            if (!$destinationProvince) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin tỉnh đích'
                ];
            }

            // Kiểm tra có warehouse trong tỉnh đích không
            $warehouseInProvince = $this->findWarehouseInProvince($destinationProvince->code);
            
            // Kiểm tra có đủ hàng trong warehouse tỉnh đích không
            $hasStockInProvince = false;
            if ($warehouseInProvince) {
                $hasStockInProvince = $this->checkStockAvailability($order, $warehouseInProvince->id);
            }

            // Xác định phương thức giao hàng
            if ($warehouseInProvince && $hasStockInProvince) {
                // Có kho và có hàng trong tỉnh -> Ưu tiên shipper nội bộ
                return $this->getInternalShippingOption($order, $warehouseInProvince);
            } else {
                // Không có kho hoặc không có hàng -> Trả về lỗi
                return [
                    'success' => false,
                    'message' => 'Không thể giao hàng đến địa chỉ này'
                ];
            }

        } catch (Exception $e) {
            Log::error('Lỗi khi xác định phương thức giao hàng tối ưu: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi xác định phương thức giao hàng'
            ];
        }
    }

    /**
     * Tìm warehouse trong tỉnh đích
     */
    private function findWarehouseInProvince(string $provinceCode): ?StoreLocation
    {
        return StoreLocation::where('type', 'warehouse')
            ->where('province_code', $provinceCode)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Kiểm tra tồn kho có đủ cho đơn hàng không
     */
    private function checkStockAvailability(Order $order, int $warehouseId): bool
    {
        foreach ($order->items as $item) {
            $inventory = ProductInventory::where('product_variant_id', $item->product_variant_id)
                ->where('store_location_id', $warehouseId)
                ->where('inventory_type', 'new')
                ->where('quantity', '>=', $item->quantity)
                ->first();

            if (!$inventory) {
                return false;
            }
        }
        return true;
    }

    /**
     * Lấy tùy chọn giao hàng nội bộ (shipper của cửa hàng)
     */
    private function getInternalShippingOption(Order $order, StoreLocation $warehouse): array
    {
        // Tìm shipper có thể giao hàng từ warehouse này
        $availableShippers = $this->findAvailableShippers($warehouse->id);

        // Tính thời gian vận chuyển từ bảng shipping_transit_times
        $transitTime = \App\Models\ShippingTransitTime::getTransitTime(
            'store_shipper',
            $warehouse->province_code,
            $order->shipping_old_province_code
        );

        // Nếu không có dữ liệu, mặc định là 7 ngày
        $transitDaysMin = $transitTime ? $transitTime->transit_days_min : 7;
        $transitDaysMax = $transitTime ? $transitTime->transit_days_max : 7;
        $estimatedDeliveryTime = $transitDaysMin == $transitDaysMax ? 
            "{$transitDaysMin} ngày" : 
            "{$transitDaysMin}-{$transitDaysMax} ngày";

        return [
            'success' => true,
            'method' => 'internal_shipping',
            'method_name' => 'Giao hàng của cửa hàng',
            'warehouse' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'province' => $warehouse->province_code
            ],
            'available_shippers' => $availableShippers,
            'estimated_delivery_time' => $estimatedDeliveryTime,
            'transit_days_min' => $transitDaysMin,
            'transit_days_max' => $transitDaysMax,
            'estimated_delivery_date' => now()->addDays($transitDaysMax)->format('Y-m-d'),
            'shipping_fee' => $this->calculateInternalShippingFee($order, $warehouse),
            'priority' => 1, // Ưu tiên cao nhất
            'description' => 'Giao hàng bởi shipper nội bộ từ kho'
        ];
    }



    /**
     * Tìm shipper có thể giao hàng từ warehouse
     */
    private function findAvailableShippers(int $warehouseId): array
    {
        $shippers = User::whereHas('warehouseAssignments', function ($query) use ($warehouseId) {
                $query->where('store_location_id', $warehouseId);
            })
            ->where('is_active', true)
            ->get(['id', 'name', 'phone']);

        return $shippers->map(function ($shipper) {
            return [
                'id' => $shipper->id,
                'name' => $shipper->name,
                'phone' => $shipper->phone,
                'status' => 'available' // Có thể mở rộng để kiểm tra lịch làm việc
            ];
        })->toArray();
    }

    /**
     * Tính phí giao hàng nội bộ - sử dụng logic từ ShipmentController
     */
    private function calculateInternalShippingFee(Order $order, StoreLocation $warehouse): int
    {
        // Sử dụng logic tính phí từ ShipmentController
        $totalWeight = $this->calculateOrderWeight($order) * 1000; // Chuyển từ kg sang gram
        
        $originProvince = ProvinceOld::where('code', $warehouse->province_code)->first();
         $destinationProvince = ProvinceOld::where('code', $order->shipping_old_province_code)->first();
         
         $baseFee = 0;
         $weightFee = 0;
         
         // Kiểm tra xem có kho tại tỉnh đích không
         $hasWarehouseAtDestination = StoreLocation::where('province_code', $order->shipping_old_province_code)->exists();
        
        if ($originProvince && $destinationProvince) {
            // Trường hợp 1: Giao hàng nội tỉnh - MIỄN PHÍ
            if ($originProvince->code === $destinationProvince->code) {
                $baseFee = 0;
                $weightFee = 0;
            }
            // Trường hợp 2: Có kho tại tỉnh đích
            else if ($hasWarehouseAtDestination) {
                if ($originProvince->region === $destinationProvince->region) {
                    // Cùng vùng miền
                    $baseFee = 15000;
                    $weightFee = max(0, ($totalWeight - 1000) * 5); // 5 VND/gram cho phần vượt 1kg
                } else {
                    // Khác vùng miền
                    $baseFee = 35000;
                    $weightFee = max(0, ($totalWeight - 1000) * 10); // 10 VND/gram cho phần vượt 1kg
                }
            }
            // Trường hợp 3: Không có kho tại tỉnh đích - PHÍ CỐ ĐỊNH
            else {
                if ($originProvince->region === $destinationProvince->region) {
                    // Cùng vùng miền - PHÍ CỐ ĐỊNH
                    $baseFee = 25000;
                    $weightFee = 0; // KHÔNG tính theo trọng lượng
                } else {
                    // Khác vùng miền - PHÍ CỐ ĐỊNH
                    $baseFee = 40000;
                    $weightFee = 0; // KHÔNG tính theo trọng lượng
                }
            }
        } else {
            // Fallback nếu không tìm thấy thông tin tỉnh
            $baseFee = 30000;
            $weightFee = 0; // Cũng nên là phí cố định
        }
        
        return $baseFee + $weightFee;
    }



    /**
     * Tính tổng trọng lượng đơn hàng
     */
    private function calculateOrderWeight(Order $order): float
    {
        $totalWeight = 0;
        foreach ($order->items as $item) {
            // Giả sử mỗi sản phẩm có trọng lượng mặc định 0.5kg
            $productWeight = $item->productVariant->weight ?? 0.5;
            $totalWeight += $productWeight * $item->quantity;
        }
        return max(1, $totalWeight); // Tối thiểu 1kg
    }

    /**
     * Lấy tất cả tùy chọn giao hàng có thể cho đơn hàng
     */
    public function getAllDeliveryOptions(Order $order): array
    {
        try {
            $options = [];
            
            // Lấy tùy chọn tối ưu
            $optimalOption = $this->determineOptimalDeliveryMethod($order);
            if ($optimalOption['success']) {
                $options[] = $optimalOption;
            }
            
            // Thêm các tùy chọn khác nếu cần
            $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
            if ($destinationProvince) {
                // Luôn có tùy chọn nhận tại cửa hàng
                $pickupOption = $this->getPickupOption($order, $destinationProvince);
                if ($pickupOption['success']) {
                    $options[] = $pickupOption;
                }
            }
            
            return [
                'success' => true,
                'options' => $options,
                'recommended' => $options[0] ?? null
            ];
            
        } catch (Exception $e) {
            Log::error('Lỗi khi lấy tùy chọn giao hàng: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy tùy chọn giao hàng'
            ];
        }
    }

    /**
     * Lấy tùy chọn nhận tại cửa hàng
     */
    private function getPickupOption(Order $order, ProvinceOld $province): array
    {
        $stores = StoreLocation::where('type', 'store')
            ->where('province_code', $province->code)
            ->where('is_active', true)
            ->get(['id', 'name', 'address']);

        if ($stores->isEmpty()) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'method' => 'pickup',
            'method_name' => 'Nhận tại cửa hàng',
            'stores' => $stores->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address
                ];
            })->toArray(),
            'estimated_delivery_time' => 'Ngay khi có hàng',
            'shipping_fee' => 0,
            'priority' => 3,
            'description' => 'Khách hàng đến cửa hàng nhận hàng'
        ];
    }

    /**
     * Kiểm tra xem có cần chuyển kho không
     */
    public function needsStockTransfer(Order $order): array
    {
        $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
        if (!$destinationProvince) {
            return ['needs_transfer' => false, 'reason' => 'Không tìm thấy tỉnh đích'];
        }

        $warehouseInProvince = $this->findWarehouseInProvince($destinationProvince->code);
        if (!$warehouseInProvince) {
            return [
                'needs_transfer' => true, 
                'reason' => 'Không có warehouse trong tỉnh đích',
                'target_province' => $destinationProvince->code
            ];
        }

        $hasStock = $this->checkStockAvailability($order, $warehouseInProvince->id);
        if (!$hasStock) {
            return [
                'needs_transfer' => true,
                'reason' => 'Warehouse trong tỉnh không có đủ hàng',
                'target_warehouse' => $warehouseInProvince->id,
                'target_province' => $destinationProvince->code
            ];
        }

        return ['needs_transfer' => false, 'reason' => 'Đã có đủ hàng trong tỉnh đích'];
    }

    /**
     * Tìm kho gần nhất với tỉnh đích
     */
    private function findNearestWarehouse($destinationProvinceCode): ?StoreLocation
    {
        // Tìm kho trong cùng tỉnh trước
        $warehouse = StoreLocation::where('province_code', $destinationProvinceCode)
            ->where('is_active', true)
            ->first();
            
        if ($warehouse) {
            return $warehouse;
        }
        
        // Nếu không có kho trong tỉnh, tìm kho trong cùng vùng miền
        $destinationProvince = ProvinceOld::where('code', $destinationProvinceCode)->first();
        if ($destinationProvince) {
            $warehouse = StoreLocation::join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                ->where('provinces_old.region', $destinationProvince->region)
                ->where('store_locations.is_active', true)
                ->select('store_locations.*')
                ->first();
                
            if ($warehouse) {
                return $warehouse;
            }
        }
        
        // Cuối cùng, trả về kho đầu tiên có sẵn
        return StoreLocation::where('is_active', true)->first();
    }
}