<?php

namespace App\Services;

use App\Models\ProductInventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryCommitmentService
{
    /**
     * Tạm giữ tồn kho cho đơn hàng
     */
    public function commitInventoryForOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $this->commitInventoryForOrderItem($orderItem);
            }
        });
    }

    /**
     * Tạm giữ tồn kho cho một item trong đơn hàng
     */
    private function commitInventoryForOrderItem(OrderItem $orderItem)
    {
        $productVariantId = $orderItem->product_variant_id;
        $requestedQuantity = $orderItem->quantity;
        
        // Tìm kho có tồn kho khả dụng
        $inventory = $this->findAvailableInventory($productVariantId, $requestedQuantity);
        
        if (!$inventory) {
            throw new \Exception("Không đủ tồn kho cho sản phẩm SKU: {$orderItem->sku}");
        }
        
        // Tạm giữ tồn kho
        $inventory->commitStock($requestedQuantity);
        
        Log::info("Đã tạm giữ {$requestedQuantity} sản phẩm {$orderItem->sku} cho đơn hàng {$orderItem->order->order_code}");
    }

    /**
     * Thả tồn kho đã tạm giữ khi hủy đơn hàng
     */
    public function releaseInventoryForOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $this->releaseInventoryForOrderItem($orderItem);
            }
        });
    }

    /**
     * Thả tồn kho đã tạm giữ cho một item
     */
    private function releaseInventoryForOrderItem(OrderItem $orderItem)
    {
        $productVariantId = $orderItem->product_variant_id;
        $quantity = $orderItem->quantity;
        
        // Tìm kho đã tạm giữ tồn kho
        $inventory = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('quantity_committed', '>', 0)
            ->first();
            
        if ($inventory) {
            $inventory->releaseStock($quantity);
            Log::info("Đã thả {$quantity} sản phẩm {$orderItem->sku} từ đơn hàng {$orderItem->order->order_code}");
        }
    }

    /**
     * Xuất kho thực tế khi giao hàng
     */
    public function fulfillInventoryForOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $this->fulfillInventoryForOrderItem($orderItem);
            }
        });
    }

    /**
     * Xuất kho thực tế cho một item
     */
    private function fulfillInventoryForOrderItem(OrderItem $orderItem)
    {
        $productVariantId = $orderItem->product_variant_id;
        $quantity = $orderItem->quantity;
        
        // Tìm kho đã tạm giữ tồn kho
        $inventory = ProductInventory::where('product_variant_id', $productVariantId)
            ->where('quantity_committed', '>=', $quantity)
            ->first();
            
        if (!$inventory) {
            throw new \Exception("Không tìm thấy tồn kho đã tạm giữ cho sản phẩm SKU: {$orderItem->sku}");
        }
        
        $inventory->fulfillStock($quantity);
        Log::info("Đã xuất kho {$quantity} sản phẩm {$orderItem->sku} cho đơn hàng {$orderItem->order->order_code}");
    }

    /**
     * Tìm kho có tồn kho khả dụng
     */
    private function findAvailableInventory($productVariantId, $requestedQuantity)
    {
        return ProductInventory::where('product_variant_id', $productVariantId)
            ->whereRaw('(quantity - quantity_committed) >= ?', [$requestedQuantity])
            ->orderBy('quantity', 'desc') // Ưu tiên kho có nhiều hàng nhất
            ->first();
    }

    /**
     * Kiểm tra tồn kho khả dụng cho một sản phẩm
     */
    public function checkAvailableStock($productVariantId, $requestedQuantity = 1)
    {
        $totalAvailable = ProductInventory::where('product_variant_id', $productVariantId)
            ->selectRaw('SUM(quantity - quantity_committed) as total_available')
            ->value('total_available') ?? 0;
            
        return $totalAvailable >= $requestedQuantity;
    }

    /**
     * Lấy tổng tồn kho khả dụng cho một sản phẩm
     */
    public function getTotalAvailableStock($productVariantId)
    {
        return ProductInventory::where('product_variant_id', $productVariantId)
            ->selectRaw('SUM(quantity - quantity_committed) as total_available')
            ->value('total_available') ?? 0;
    }
}