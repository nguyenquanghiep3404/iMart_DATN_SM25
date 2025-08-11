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
     * Lấy tất cả các tùy chọn giao hàng có thể cho đơn hàng
     *
     * @param Order $order
     * @return array
     */
    public function getAllDeliveryOptions(Order $order): array
    {
        try {
            $options = [];
            $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
            
            if (!$destinationProvince) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin tỉnh đích'
                ];
            }

            // Tùy chọn 1: Giao hàng nội bộ (nếu có kho trong tỉnh)
            $warehouseInProvince = $this->findWarehouseInProvince($destinationProvince->code);
            if ($warehouseInProvince && $this->checkStockAvailability($order, $warehouseInProvince->id)) {
                $internalOption = $this->getInternalShippingOption($order, $warehouseInProvince);
                if ($internalOption['success']) {
                    $options[] = [
                        'type' => 'internal',
                        'name' => 'Giao hàng nội bộ',
                        'description' => 'Shipper nội bộ giao hàng từ kho trong tỉnh',
                        'estimated_days' => 1,
                        'shipping_fee' => $internalOption['shipping_fee'],
                        'warehouse_id' => $warehouseInProvince->id,
                        'warehouse_name' => $warehouseInProvince->name,
                        'priority' => 1
                    ];
                }
            }

            // Tùy chọn 2: API bên thứ 3
            $externalOption = $this->getExternalShippingOption($order);
            if ($externalOption['success']) {
                $options[] = [
                    'type' => 'external',
                    'name' => 'Giao hàng qua đối tác',
                    'description' => 'Sử dụng dịch vụ giao hàng bên thứ 3',
                    'estimated_days' => $externalOption['estimated_days'],
                    'shipping_fee' => $externalOption['shipping_fee'],
                    'provider' => $externalOption['provider'],
                    'priority' => 2
                ];
            }

            // Sắp xếp theo độ ưu tiên
            usort($options, function($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });

            $recommended = !empty($options) ? $options[0] : null;

            return [
                'success' => true,
                'options' => $options,
                'recommended' => $recommended
            ];

        } catch (Exception $e) {
            Log::error('Error getting delivery options: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy tùy chọn giao hàng: ' . $e->getMessage()
            ];
        }
    }
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
                // Không có kho hoặc không có hàng -> Dùng API bên thứ 3
                return $this->getExternalShippingOption($order);
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
            'estimated_delivery_time' => '1-2 ngày',
            'shipping_fee' => $this->calculateInternalShippingFee($order, $warehouse),
            'priority' => 1, // Ưu tiên cao nhất
            'description' => 'Giao hàng bởi shipper nội bộ từ kho trong tỉnh'
        ];
    }

    /**
     * Lấy tùy chọn giao hàng bên ngoài (API GHN, GHTK, etc.)
     */
    private function getExternalShippingOption(Order $order): array
    {
        return [
            'success' => true,
            'method' => 'external_shipping',
            'method_name' => 'Giao hàng nhanh',
            'providers' => [
                [
                    'name' => 'Giao Hàng Nhanh (GHN)',
                    'estimated_delivery_time' => '2-3 ngày',
                    'shipping_fee' => $this->calculateExternalShippingFee($order, 'ghn')
                ],
                [
                    'name' => 'Giao Hàng Tiết Kiệm (GHTK)',
                    'estimated_delivery_time' => '3-5 ngày',
                    'shipping_fee' => $this->calculateExternalShippingFee($order, 'ghtk')
                ]
            ],
            'priority' => 2, // Ưu tiên thấp hơn
            'description' => 'Giao hàng qua đối tác bên ngoài'
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
     * Tính phí giao hàng nội bộ
     */
    private function calculateInternalShippingFee(Order $order, StoreLocation $warehouse): int
    {
        // Logic tính phí giao hàng nội bộ
        // Có thể dựa trên khoảng cách, trọng lượng, v.v.
        $baseFee = 25000; // Phí cơ bản 25k
        
        // Tính thêm phí theo trọng lượng (nếu có)
        $totalWeight = $this->calculateOrderWeight($order);
        $weightFee = max(0, ($totalWeight - 1) * 5000); // 5k cho mỗi kg sau kg đầu tiên
        
        return $baseFee + $weightFee;
    }

    /**
     * Tính phí giao hàng bên ngoài
     */
    private function calculateExternalShippingFee(Order $order, string $provider): int
    {
        // Logic tính phí giao hàng bên ngoài
        // Có thể tích hợp API thực tế của các nhà cung cấp
        $baseFees = [
            'ghn' => 35000,
            'ghtk' => 30000
        ];
        
        $baseFee = $baseFees[$provider] ?? 35000;
        
        // Tính thêm phí theo trọng lượng
        $totalWeight = $this->calculateOrderWeight($order);
        $weightFee = max(0, ($totalWeight - 1) * 8000); // 8k cho mỗi kg sau kg đầu tiên
        
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
                
                // Nếu tùy chọn tối ưu là nội bộ, thêm tùy chọn bên ngoài
                if ($optimalOption['success'] && $optimalOption['method'] === 'internal_shipping') {
                    $externalOption = $this->getExternalShippingOption($order);
                    if ($externalOption['success']) {
                        $options[] = $externalOption;
                    }
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
}