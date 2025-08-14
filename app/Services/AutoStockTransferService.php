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

            foreach ($order->items as $item) {
                // Kiểm tra tồn kho tại các warehouse
                $warehouseInventory = $this->findWarehouseWithStock(
                    $item->product_variant_id, 
                    $item->quantity, 
                    $destinationRegion,
                    $destinationProvince->code ?? null
                );
                
                if (!$warehouseInventory) {
                    // Tìm store có hàng để chuyển về warehouse
                    $storeWithStock = $this->findStoreWithStock($item->product_variant_id, $item->quantity, $destinationRegion);
                    
                    if ($storeWithStock) {
                        // Tìm warehouse gần nhất để nhận hàng
                        $targetWarehouse = $this->findNearestWarehouse(
                            $storeWithStock['store_location_id'], 
                            $destinationRegion,
                            $destinationProvince->code ?? null
                        );
                        
                        if ($targetWarehouse) {
                            $transfer = $this->createStockTransfer(
                                $storeWithStock['store_location_id'],
                                $targetWarehouse->id,
                                $item->product_variant_id,
                                $item->quantity,
                                "Chuyển hàng tự động cho đơn hàng #{$order->order_code}. Order:{$order->order_code}"
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

            return [
                'success' => true,
                'transfers_created' => $transfersCreated,
                'message' => count($transfersCreated) > 0 
                    ? 'Đã tạo ' . count($transfersCreated) . ' phiếu chuyển kho tự động'
                    : 'Không cần tạo phiếu chuyển kho'
            ];

        } catch (Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho tự động: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo phiếu chuyển kho tự động: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Tìm warehouse có đủ tồn kho
     * Ưu tiên: 1. Cùng tỉnh với khách hàng, 2. Cùng vùng miền, 3. Warehouse khác
     */
    private function findWarehouseWithStock(int $productVariantId, int $quantity, ?string $destinationRegion, ?string $destinationProvinceCode = null): ?array
    {
        $query = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('quantity', '>=', $quantity)
            ->where('inventory_type', 'new')
            ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
            ->where('store_locations.type', 'warehouse')
            ->where('store_locations.is_active', true);

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
     * Tạo phiếu chuyển kho
     */
    private function createStockTransfer(
        int $fromLocationId,
        int $toLocationId,
        int $productVariantId,
        int $quantity,
        string $notes
    ): ?StockTransfer {
        try {
            return DB::transaction(function () use ($fromLocationId, $toLocationId, $productVariantId, $quantity, $notes) {
                // Tạo mã chuyển kho
                $transferCode = 'AUTO-' . strtoupper(uniqid());

                // Tạo phiếu chuyển kho
                $stockTransfer = StockTransfer::create([
                    'transfer_code' => $transferCode,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'status' => 'pending',
                    'created_by' => auth()->id() ?? 1, // Hệ thống tự động
                    'notes' => $notes
                ]);

                // Tạo chi tiết phiếu chuyển kho
                StockTransferItem::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity
                ]);

                Log::info("Đã tạo phiếu chuyển kho tự động: {$transferCode}");

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
        // Chỉ tự động xử lý nếu cả 2 địa điểm đều thuộc cùng hệ thống
        // và có đủ điều kiện (ví dụ: cùng thành phố, có kết nối trực tiếp)
        $fromLocation = $transfer->fromLocation;
        $toLocation = $transfer->toLocation;

        if (!$fromLocation || !$toLocation) {
            return false;
        }

        // Kiểm tra khoảng cách (cùng tỉnh thành)
        return $fromLocation->province_code === $toLocation->province_code;
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