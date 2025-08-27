<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service xử lý tạo phiếu chuyển kho tự động cho gói hàng (fulfillment)
 * thay vì cho từng sản phẩm riêng lẻ
 */
class FulfillmentStockTransferService
{
    /**
     * Kiểm tra và tạo phiếu chuyển kho tự động cho fulfillments của đơn hàng
     * 
     * @param Order $order
     * @return array
     */
    public function checkAndCreateFulfillmentTransfers(Order $order): array
    {
        try {
            Log::info('Bắt đầu kiểm tra tạo phiếu chuyển kho fulfillment cho đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status
            ]);

            // Chỉ tạo phiếu chuyển kho khi đơn hàng ở trạng thái 'đang xử lý'
            if ($order->status !== Order::STATUS_PROCESSING) {
                return [
                    'success' => false,
                    'message' => 'Đơn hàng chưa ở trạng thái đang xử lý',
                    'transfers_created' => []
                ];
            }

            $fulfillments = $order->fulfillments;
            
            if ($fulfillments->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Đơn hàng chưa có fulfillments',
                    'transfers_created' => []
                ];
            }

            $transfersCreated = [];
            $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
            $destinationRegion = $destinationProvince->region ?? null;

            foreach ($fulfillments as $fulfillment) {
                $transferResult = $this->createTransferForFulfillment(
                    $fulfillment, 
                    $order, 
                    $destinationRegion
                );
                
                if ($transferResult['success']) {
                    $transfersCreated[] = $transferResult['transfer'];
                }
            }

            return [
                'success' => !empty($transfersCreated),
                'message' => empty($transfersCreated) ? 
                    'Không cần tạo phiếu chuyển kho' : 
                    'Đã tạo ' . count($transfersCreated) . ' phiếu chuyển kho fulfillment',
                'transfers_created' => $transfersCreated
            ];

        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho fulfillment', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage(),
                'transfers_created' => []
            ];
        }
    }

    /**
     * Tạo phiếu chuyển kho cho một fulfillment cụ thể
     * 
     * @param OrderFulfillment $fulfillment
     * @param Order $order
     * @param string|null $destinationRegion
     * @return array
     */
    private function createTransferForFulfillment(
        OrderFulfillment $fulfillment, 
        Order $order, 
        ?string $destinationRegion
    ): array {
        try {
            // Kiểm tra xem fulfillment có cần chuyển kho không
            $needsTransfer = $this->checkIfFulfillmentNeedsTransfer($fulfillment, $order);
            
            if (!$needsTransfer['needs_transfer']) {
                return [
                    'success' => false,
                    'message' => $needsTransfer['reason']
                ];
            }

            // Tìm kho đích gần khách hàng nhất
            $destinationWarehouse = $this->findDestinationWarehouse(
                $order->shipping_old_province_code, 
                $destinationRegion
            );

            if (!$destinationWarehouse) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy kho đích phù hợp'
                ];
            }

            // Tạo phiếu chuyển kho cho toàn bộ fulfillment
            $stockTransfer = $this->createFulfillmentStockTransfer(
                $fulfillment,
                $destinationWarehouse->id,
                $order
            );

            if ($stockTransfer) {
                Log::info('Đã tạo phiếu chuyển kho fulfillment thành công', [
                    'fulfillment_id' => $fulfillment->id,
                    'transfer_code' => $stockTransfer->transfer_code,
                    'from_location' => $fulfillment->store_location_id,
                    'to_location' => $destinationWarehouse->id
                ]);

                return [
                    'success' => true,
                    'transfer' => [
                        'transfer_code' => $stockTransfer->transfer_code,
                        'from_location_id' => $fulfillment->store_location_id,
                        'to_location_id' => $destinationWarehouse->id,
                        'fulfillment_id' => $fulfillment->id
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Không thể tạo phiếu chuyển kho'
            ];

        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho cho fulfillment', [
                'fulfillment_id' => $fulfillment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kiểm tra xem fulfillment có cần chuyển kho không
     * 
     * @param OrderFulfillment $fulfillment
     * @param Order $order
     * @return array
     */
    private function checkIfFulfillmentNeedsTransfer(OrderFulfillment $fulfillment, Order $order): array
    {
        $currentLocation = $fulfillment->storeLocation;
        
        if (!$currentLocation) {
            return [
                'needs_transfer' => false,
                'reason' => 'Không tìm thấy thông tin kho hiện tại'
            ];
        }

        // Kiểm tra xem kho hiện tại có đủ hàng không
        $hasEnoughStock = $this->checkFulfillmentStock($fulfillment);
        
        if (!$hasEnoughStock) {
            return [
                'needs_transfer' => true,
                'reason' => 'Kho hiện tại không đủ hàng cho fulfillment'
            ];
        }

        // Kiểm tra xem có cần chuyển về kho gần khách hàng hơn không
        $customerProvince = $order->shipping_old_province_code;
        $currentProvince = $currentLocation->province_code;
        
        if ($customerProvince !== $currentProvince) {
            // Tìm kho gần khách hàng hơn
            $nearerWarehouse = $this->findDestinationWarehouse($customerProvince, null);
            
            if ($nearerWarehouse && $nearerWarehouse->id !== $currentLocation->id) {
                return [
                    'needs_transfer' => true,
                    'reason' => 'Có kho gần khách hàng hơn để tối ưu giao hàng'
                ];
            }
        }

        return [
            'needs_transfer' => false,
            'reason' => 'Fulfillment không cần chuyển kho'
        ];
    }

    /**
     * Kiểm tra tồn kho cho fulfillment
     * 
     * @param OrderFulfillment $fulfillment
     * @return bool
     */
    private function checkFulfillmentStock(OrderFulfillment $fulfillment): bool
    {
        foreach ($fulfillment->items as $item) {
            $inventory = ProductInventory::where('store_location_id', $fulfillment->store_location_id)
                ->where('product_variant_id', $item->orderItem->product_variant_id)
                ->where('inventory_type', 'new')
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tìm kho đích phù hợp
     * 
     * @param string $destinationProvinceCode
     * @param string|null $destinationRegion
     * @return StoreLocation|null
     */
    private function findDestinationWarehouse(string $destinationProvinceCode, ?string $destinationRegion): ?StoreLocation
    {
        $query = StoreLocation::where('type', 'warehouse')
            ->where('is_active', true);

        // Ưu tiên 1: Tìm warehouse cùng tỉnh với khách hàng
        $sameProvinceWarehouse = clone $query;
        $sameProvinceWarehouse = $sameProvinceWarehouse->where('province_code', $destinationProvinceCode)->first();
        
        if ($sameProvinceWarehouse) {
            return $sameProvinceWarehouse;
        }

        // Ưu tiên 2: Tìm warehouse cùng vùng miền
        if ($destinationRegion) {
            $query->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                  ->where('provinces_old.region', $destinationRegion)
                  ->select('store_locations.*');
            
            $sameRegionWarehouse = $query->first();
            if ($sameRegionWarehouse) {
                return $sameRegionWarehouse;
            }
        }

        // Ưu tiên 3: Bất kỳ warehouse nào khác
        return StoreLocation::where('type', 'warehouse')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Tạo phiếu chuyển kho cho fulfillment
     * 
     * @param OrderFulfillment $fulfillment
     * @param int $toLocationId
     * @param Order $order
     * @return StockTransfer|null
     */
    private function createFulfillmentStockTransfer(
        OrderFulfillment $fulfillment, 
        int $toLocationId, 
        Order $order
    ): ?StockTransfer {
        try {
            return DB::transaction(function () use ($fulfillment, $toLocationId, $order) {
                // Tạo mã chuyển kho với tracking code từ fulfillment
                $transferCode = 'FULFILL-' . $order->order_code . '-' . $fulfillment->id . '-' . strtoupper(substr(uniqid(), -4));

                // Tạo phiếu chuyển kho
                $stockTransfer = StockTransfer::create([
                    'transfer_code' => $transferCode,
                    'from_location_id' => $fulfillment->store_location_id,
                    'to_location_id' => $toLocationId,
                    'status' => 'pending',
                    'created_by' => auth()->id() ?? 1,
                    'notes' => "Order:{$order->order_code} - Chuyển kho tự động cho fulfillment #{$fulfillment->id}"
                ]);

                // Tạo chi tiết phiếu chuyển kho cho từng sản phẩm trong fulfillment
                foreach ($fulfillment->items as $item) {
                    StockTransferItem::create([
                        'stock_transfer_id' => $stockTransfer->id,
                        'product_variant_id' => $item->orderItem->product_variant_id,
                        'quantity' => $item->quantity
                    ]);
                }

                Log::info("Đã tạo phiếu chuyển kho fulfillment: {$transferCode}", [
                    'fulfillment_id' => $fulfillment->id,
                    'order_code' => $order->order_code,
                    'items_count' => $fulfillment->items->count()
                ]);

                return $stockTransfer;
            });
        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho fulfillment: ' . $e->getMessage());
            return null;
        }
    }
}