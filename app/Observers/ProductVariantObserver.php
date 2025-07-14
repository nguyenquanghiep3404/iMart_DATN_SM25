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
        if ($variant->isDirty('stock_quantity')) {
            $newQty = $variant->stock_quantity;

            if ($newQty == 0) {
                $variant->stock_status = 'out_of_stock';
            } elseif ($newQty < 5) {
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

        // Tồn kho thấp
        if (
            $variant->wasChanged('stock_quantity') &&
            $variant->getOriginal('stock_quantity') >= $threshold &&
            $variant->stock_quantity < $threshold
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
                $variant->wasChanged('stock_quantity') &&
                $variant->getOriginal('stock_quantity') == 0 &&
                $variant->stock_quantity > 0
            ) {
                Notification::send($users, new WishlistProductBackInStock($variant));
            }

            // 2. Sắp hết hàng
            if (
                $variant->wasChanged('stock_quantity') &&
                $variant->getOriginal('stock_quantity') >= $threshold &&
                $variant->stock_quantity < $threshold
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
}
