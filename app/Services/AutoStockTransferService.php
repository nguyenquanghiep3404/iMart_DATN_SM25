<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\ProvinceOld;
use App\Models\ShippingTransitTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service xử lý logic chuyển hàng tự động từ chi nhánh store về kho warehouse
 * để tối ưu hóa việc xử lý đơn hàng online
 */
class AutoStockTransferService
{
    /**
     * Kiểm tra và tự động chuyển hàng từ store về warehouse gần nhất
     * nếu warehouse không có đủ hàng để xử lý đơn hàng online
     *
     * @param Order $order
     * @return array
     */
    public function checkAndCreateAutoTransfer(Order $order): array
    {
        try {
            $transfersCreated = [];
            $destinationProvince = ProvinceOld::find($order->shipping_old_province_code);
            $destinationRegion = $destinationProvince->region ?? null;
            
            Log::info('AutoStockTransferService: Bắt đầu kiểm tra đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'shipping_province_code' => $order->shipping_old_province_code,
                'destination_province' => $destinationProvince ? $destinationProvince->name : 'Không tìm thấy',
                'destination_region' => $destinationRegion,
                'items_count' => $order->items->count()
            ]);
            
            // Lấy tracking code từ order fulfillments
            $orderTrackingCode = $order->fulfillments()->whereNotNull('tracking_code')->first()?->tracking_code;

            foreach ($order->items as $item) {
                Log::info('AutoStockTransferService: Xử lý item', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'product_variant_id' => $item->product_variant_id,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity
                ]);
                
                // Kiểm tra xem tỉnh đích có warehouse không
                $destinationWarehouse = $this->findDestinationWarehouse($destinationProvince->code ?? null);
                
                Log::info('AutoStockTransferService: Kết quả tìm warehouse đích', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'destination_warehouse' => $destinationWarehouse ? [
                        'id' => $destinationWarehouse->id,
                        'name' => $destinationWarehouse->name,
                        'province_code' => $destinationWarehouse->province_code
                    ] : null
                ]);
                
                if ($destinationWarehouse) {
                    // Nếu tỉnh đích có warehouse, kiểm tra warehouse đó có đủ hàng không
                    $destinationInventory = $this->checkWarehouseStock(
                        $item->product_variant_id, 
                        $item->quantity, 
                        $destinationWarehouse->id
                    );
                    
                    Log::info('AutoStockTransferService: Kiểm tra stock tại warehouse đích', [
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'warehouse_id' => $destinationWarehouse->id,
                        'warehouse_name' => $destinationWarehouse->name,
                        'product_variant_id' => $item->product_variant_id,
                        'required_quantity' => $item->quantity,
                        'has_sufficient_stock' => $destinationInventory ? true : false,
                        'available_stock' => $destinationInventory ? $destinationInventory->quantity : 0,
                        'need_transfer' => !$destinationInventory
                    ]);
                    
                    if (!$destinationInventory) {
                        // Warehouse tỉnh đích không có hàng, tìm nguồn hàng để chuyển về
                        Log::info('AutoStockTransferService: Tìm nguồn hàng để chuyển', [
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'product_variant_id' => $item->product_variant_id,
                            'required_quantity' => $item->quantity,
                            'destination_region' => $destinationRegion,
                            'destination_province_code' => $destinationProvince->code
                        ]);
                        
                        $sourceWithStock = $this->findSourceWithStock($item->product_variant_id, $item->quantity, $destinationRegion, $destinationProvince->code);
                        
                        Log::info('AutoStockTransferService: Kết quả tìm nguồn hàng', [
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'source_found' => $sourceWithStock ? true : false,
                            'source_details' => $sourceWithStock
                        ]);
                        
                        if ($sourceWithStock) {
                            Log::info('AutoStockTransferService: Tạo stock transfer', [
                                'order_id' => $order->id,
                                'item_id' => $item->id,
                                'source_location_id' => $sourceWithStock['store_location_id'],
                                'destination_warehouse_id' => $destinationWarehouse->id,
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'tracking_code' => $orderTrackingCode
                            ]);
                            
                            $transfer = $this->createStockTransfer(
                                $sourceWithStock['store_location_id'],
                                $destinationWarehouse->id,
                                $item->product_variant_id,
                                $item->quantity,
                                "Chuyển hàng tự động cho đơn hàng #{$order->order_code}. Order:{$order->order_code}",
                                $orderTrackingCode
                            );
                            
                            Log::info('AutoStockTransferService: Kết quả tạo stock transfer', [
                                'order_id' => $order->id,
                                'item_id' => $item->id,
                                'transfer_created' => $transfer ? true : false,
                                'transfer_id' => $transfer ? $transfer->id : null
                            ]);
                            
                            if ($transfer) {
                                $transfersCreated[] = [
                                    'transfer_id' => $transfer->id,
                                    'transfer_code' => $transfer->transfer_code,
                                    'from_store' => $sourceWithStock['store_name'],
                                    'to_warehouse' => $destinationWarehouse->name,
                                    'product_sku' => $item->sku,
                                    'quantity' => $item->quantity
                                ];
                            }
                        }
                    }
                } else {
                    // Tỉnh đích không có warehouse, kiểm tra warehouse khác có hàng không
                    $warehouseInventory = $this->findWarehouseWithStock(
                        $item->product_variant_id, 
                        $item->quantity, 
                        $destinationRegion,
                        null // Không ưu tiên tỉnh đích vì không có warehouse
                    );
                    
                    if (!$warehouseInventory) {
                        // Không có warehouse nào có hàng, tìm store có hàng để chuyển về warehouse gần nhất
                        $storeWithStock = $this->findStoreWithStock($item->product_variant_id, $item->quantity, $destinationRegion);
                        
                        if ($storeWithStock) {
                            // Tìm warehouse gần nhất để nhận hàng
                            $targetWarehouse = $this->findNearestWarehouse(
                                $storeWithStock['store_location_id'], 
                                $destinationRegion,
                                null // Không có warehouse tỉnh đích
                            );
                            
                            if ($targetWarehouse) {
                                $transfer = $this->createStockTransfer(
                                    $storeWithStock['store_location_id'],
                                    $targetWarehouse->id,
                                    $item->product_variant_id,
                                    $item->quantity,
                                    "Chuyển hàng tự động cho đơn hàng #{$order->order_code}. Order:{$order->order_code}",
                                    $orderTrackingCode
                                );
                                
                                if ($transfer) {
                                    $transfersCreated[] = [
                                        'transfer_id' => $transfer->id,
                                        'transfer_code' => $transfer->transfer_code,
                                        'from_store' => $storeWithStock['store_name'],
                                        'to_warehouse' => $targetWarehouse->name,
                                        'product_sku' => $item->sku,
                                        'quantity' => $item->quantity
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $result = [
                'success' => true,
                'transfers_created' => $transfersCreated,
                'message' => count($transfersCreated) > 0 
                    ? 'Đã tạo ' . count($transfersCreated) . ' phiếu chuyển kho tự động'
                    : 'Không cần tạo phiếu chuyển kho'
            ];
            
            Log::info('AutoStockTransferService: Kết thúc xử lý đơn hàng', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total_transfers_created' => count($transfersCreated),
                'result' => $result
            ]);
            
            return $result;

        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho tự động: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo phiếu chuyển kho tự động: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Tìm warehouse tại tỉnh đích
     */
    private function findDestinationWarehouse($provinceCode)
     {
         if (!$provinceCode) {
             return null;
         }
         
         return StoreLocation::where('type', 'warehouse')
             ->where('province_code', $provinceCode)
             ->where('is_active', true)
             ->first();
     }
    
    /**
     * Kiểm tra tồn kho tại warehouse cụ thể
     */
    private function checkWarehouseStock($productVariantId, $quantity, $warehouseId)
    {
        $inventory = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('store_location_id', $warehouseId)
            ->where('inventory_type', 'new')
            ->first();
            
        return $inventory && $inventory->quantity >= $quantity ? $inventory : null;
    }
    
    /**
      * Tìm nguồn hàng (warehouse hoặc store) có đủ tồn kho
      */
     private function findSourceWithStock($productVariantId, $quantity, $region = null, $excludeProvinceCode = null)
     {
         // Tìm warehouse có hàng (ưu tiên cùng vùng miền, loại trừ tỉnh đích)
         $warehouseWithStock = $this->findWarehouseWithStock($productVariantId, $quantity, $region, null, $excludeProvinceCode);
         
         if ($warehouseWithStock) {
             return [
                 'store_location_id' => $warehouseWithStock['store_location_id'],
                 'store_name' => $warehouseWithStock['store_name'],
                 'quantity' => $warehouseWithStock['quantity']
             ];
         }
         
         // Nếu không có warehouse, tìm store có hàng
         return $this->findStoreWithStock($productVariantId, $quantity, $region);
     }
    
    /**
     * Tìm warehouse có đủ tồn kho
     * Ưu tiên: 1. Cùng tỉnh với khách hàng, 2. Cùng vùng miền, 3. Warehouse khác
     */
    private function findWarehouseWithStock(int $productVariantId, int $quantity, ?string $destinationRegion, ?string $destinationProvinceCode = null, ?string $excludeProvinceCode = null): ?array
    {
        $query = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('quantity', '>=', $quantity)
            ->where('inventory_type', 'new')
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->where('store_locations.type', 'warehouse')
            ->where('store_locations.is_active', true);
            
        // Loại trừ tỉnh cụ thể nếu có
         if ($excludeProvinceCode) {
             $query->where('store_locations.province_code', '!=', $excludeProvinceCode);
         }

        // Ưu tiên 1: Tìm warehouse cùng tỉnh với khách hàng
        if ($destinationProvinceCode) {
            $sameProvinceQuery = clone $query;
            $sameProvinceInventory = $sameProvinceQuery->where('store_locations.province_code', $destinationProvinceCode)
                ->select(
                    'product_inventories.*',
                    'store_locations.name as warehouse_name',
                    'store_locations.id as warehouse_id'
                )->first();
            
            if ($sameProvinceInventory) {
                return [
                    'store_location_id' => $sameProvinceInventory->warehouse_id,
                    'store_name' => $sameProvinceInventory->warehouse_name,
                    'quantity' => $sameProvinceInventory->quantity
                ];
            }
        }

        // Ưu tiên 2: Tìm warehouse cùng vùng miền
        if ($destinationRegion) {
            $query->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                  ->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationRegion]);
        }

        $inventory = $query->select(
            'product_inventories.*',
            'store_locations.name as warehouse_name',
            'store_locations.id as warehouse_id'
        )->first();

        return $inventory ? [
            'store_location_id' => $inventory->warehouse_id,
            'store_name' => $inventory->warehouse_name,
            'quantity' => $inventory->quantity
        ] : null;
    }

    /**
     * Tìm store có đủ tồn kho
     */
    private function findStoreWithStock(int $productVariantId, int $quantity, ?string $destinationRegion): ?array
    {
        $query = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('quantity', '>=', $quantity)
            ->where('inventory_type', 'new')
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->where('store_locations.type', 'store')
            ->where('store_locations.is_active', true);

        if ($destinationRegion) {
            $query->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                  ->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationRegion]);
        }

        $inventory = $query->select(
            'product_inventories.*',
            'store_locations.name as store_name',
            'store_locations.id as store_id'
        )->first();

        return $inventory ? [
            'store_location_id' => $inventory->store_id,
            'store_name' => $inventory->store_name,
            'quantity' => $inventory->quantity
        ] : null;
    }

    /**
     * Tìm warehouse gần nhất để nhận hàng
     * Ưu tiên: 1. Cùng tỉnh với khách hàng, 2. Cùng vùng miền, 3. Warehouse khác
     */
    private function findNearestWarehouse(int $fromStoreId, ?string $destinationRegion, ?string $destinationProvinceCode = null): ?StoreLocation
    {
        $fromStore = StoreLocation::find($fromStoreId);
        if (!$fromStore) {
            return null;
        }

        $query = StoreLocation::where('type', 'warehouse')
            ->where('is_active', true)
            ->where('id', '!=', $fromStoreId);

        // Ưu tiên 1: Tìm warehouse cùng tỉnh với khách hàng
        if ($destinationProvinceCode) {
            $sameProvinceWarehouse = clone $query;
            $sameProvinceWarehouse = $sameProvinceWarehouse->where('province_code', $destinationProvinceCode)->first();
            if ($sameProvinceWarehouse) {
                return $sameProvinceWarehouse;
            }
        }

        // Ưu tiên 2: Tìm warehouse cùng vùng miền
        if ($destinationRegion) {
            $query->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                  ->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationRegion])
                  ->select('store_locations.*');
        }

        return $query->first();
    }

    /**
     * Tạo phiếu chuyển kho với tracking code từ order fulfillment
     */
    private function createStockTransfer(
        int $fromLocationId,
        int $toLocationId,
        int $productVariantId,
        int $quantity,
        string $notes,
        ?string $orderTrackingCode = null
    ): ?StockTransfer {
        try {
            return DB::transaction(function () use ($fromLocationId, $toLocationId, $productVariantId, $quantity, $notes, $orderTrackingCode) {
                // Tạo mã chuyển kho với tracking code từ order nếu có
                $transferCode = $orderTrackingCode ? 
                    'AUTO-' . $orderTrackingCode . '-' . strtoupper(substr(uniqid(), -4)) : 
                    'AUTO-' . strtoupper(uniqid());

                // Tạo phiếu chuyển kho
                $stockTransfer = StockTransfer::create([
                    'transfer_code' => $transferCode,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'status' => 'pending',
                    'created_by' => auth()->id() ?? 1, // Hệ thống tự động
                    'notes' => $notes . ($orderTrackingCode ? " (Tracking: {$orderTrackingCode})" : '')
                ]);

                // Tạo chi tiết phiếu chuyển kho
                StockTransferItem::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity
                ]);

                Log::info("Đã tạo phiếu chuyển kho tự động: {$transferCode}" . 
                    ($orderTrackingCode ? " với tracking code: {$orderTrackingCode}" : ''));

                return $stockTransfer;
            });
        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra xem có thể tự động xử lý phiếu chuyển kho không
     * (Tự động xuất và nhận hàng nếu cùng hệ thống)
     */
    public function canAutoProcessTransfer(StockTransfer $transfer): bool
    {
        // Cho phép tự động xử lý tất cả phiếu chuyển kho hợp lệ
        $fromLocation = $transfer->fromLocation;
        $toLocation = $transfer->toLocation;

        if (!$fromLocation || !$toLocation) {
            return false;
        }

        // Chỉ cần kiểm tra tồn tại của cả 2 địa điểm
        return true;
    }

    /**
     * Tự động xử lý phiếu chuyển kho (xuất và nhận)
     */
    public function autoProcessTransfer(StockTransfer $transfer): array
    {
        try {
            if (!$this->canAutoProcessTransfer($transfer)) {
                return [
                    'success' => false,
                    'message' => 'Không thể tự động xử lý phiếu chuyển kho này'
                ];
            }

            DB::transaction(function () use ($transfer) {
                // Tự động xuất kho
                $this->autoDispatchTransfer($transfer);
                
                // Tự động nhận kho (giả lập vận chuyển tức thì)
                $this->autoReceiveTransfer($transfer);
            });

            return [
                'success' => true,
                'message' => 'Đã tự động xử lý phiếu chuyển kho thành công'
            ];

        } catch (Exception $e) {
            Log::error('Lỗi khi tự động xử lý phiếu chuyển kho: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi tự động xử lý: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Tự động xuất kho
     */
    private function autoDispatchTransfer(StockTransfer $transfer): void
    {
        // Logic tương tự như trong StockTransferController::processDispatch
        // nhưng được tự động hóa
        $transfer->update([
            'status' => 'shipped',
            'shipped_at' => now()
        ]);
    }

    /**
     * Tự động nhận kho
     */
    private function autoReceiveTransfer(StockTransfer $transfer): void
    {
        // Logic tương tự như trong StockTransferController::processReceive
        // nhưng được tự động hóa
        $transfer->update([
            'status' => 'received',
            'received_at' => now()
        ]);
    }
}