<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockAlert;
use App\Notifications\ProductOutOfStock;
use App\Notifications\WishlistProductBackInStock;
use App\Notifications\WishlistProductLowStock;
use App\Notifications\WishlistProductOnSale;
use App\Models\Wishlist;

class ProductVariantObserver
{
    public function updating(ProductVariant $variant)
    {
        // Kiểm tra xem có thay đổi trong inventories không
        if ($variant->isDirty('inventories')) {
            $sellableStock = $variant->getSellableStockAttribute();

            if ($sellableStock == 0) {
                $variant->stock_status = 'out_of_stock';
            } elseif ($sellableStock < 5) {
                $variant->stock_status = 'low';
            } else {
                $variant->stock_status = 'in_stock';
            }
        }
    }

    public function updated(ProductVariant $variant)
    {
        $threshold = 5;

        // === ADMIN/INVENTORY NOTIFICATIONS ===
        $adminUsers = User::whereHas(
            'roles',
            fn($q) =>
            $q->whereIn('name', ['admin', 'inventory_manager'])
        )->get();

        // Hết hàng
        if ($variant->wasChanged('stock_status') && $variant->stock_status === 'out_of_stock') {
            Notification::send($adminUsers, new ProductOutOfStock($variant));
            return;
        }

        // Tồn kho thấp - kiểm tra dựa trên sellable stock
        $currentSellableStock = $variant->getSellableStockAttribute();
        $previousSellableStock = $this->getPreviousSellableStock($variant);

        if (
            $previousSellableStock >= $threshold &&
            $currentSellableStock < $threshold &&
            $currentSellableStock > 0
        ) {
            Notification::send($adminUsers, new LowStockAlert($variant));
        }

        // Vừa nhập hàng trở lại nhưng vẫn dưới ngưỡng
        if (
            $variant->wasChanged('stock_status') &&
            $variant->stock_status === 'low' &&
            $variant->getOriginal('stock_status') === 'out_of_stock'
        ) {
            Notification::send($adminUsers, new LowStockAlert($variant));
        }

        // === USER WISHLIST NOTIFICATIONS ===
        $wishlistUserIds = Wishlist::whereHas('items', function ($q) use ($variant) {
            $q->where('product_variant_id', $variant->id);
        })->pluck('user_id')->unique();

        if ($wishlistUserIds->isNotEmpty()) {
            $users = User::whereIn('id', $wishlistUserIds)->get();

            // 1. Có hàng trở lại
            if (
                $previousSellableStock == 0 &&
                $currentSellableStock > 0
            ) {
                Notification::send($users, new WishlistProductBackInStock($variant));
            }

            // 2. Sắp hết hàng
            if (
                $previousSellableStock >= $threshold &&
                $currentSellableStock < $threshold &&
                $currentSellableStock > 0
            ) {
                Notification::send($users, new WishlistProductLowStock($variant));
            }

            // 3. Đang giảm giá
            if (
                $variant->wasChanged('sale_price') &&
                $variant->sale_price < $variant->price
            ) {
                Notification::send($users, new WishlistProductOnSale($variant));
            }
        }
    }

    /**
     * Helper method để lấy tồn kho có thể bán trước khi cập nhật
     */
    private function getPreviousSellableStock(ProductVariant $variant): int
    {
        // Lấy dữ liệu inventories trước khi cập nhật
        $originalInventories = $variant->getOriginal('inventories') ?? [];
        
        if (is_array($originalInventories)) {
            return collect($originalInventories)
                ->where('inventory_type', 'new')
                ->sum('quantity');
        }
        
        return 0;
    }
}
