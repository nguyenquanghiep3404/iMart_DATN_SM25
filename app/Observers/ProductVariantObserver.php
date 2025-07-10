<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\ProductOutOfStock;

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
        $usersToNotify = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'inventory_manager']);
        })->get();

        // === 1. GỬI THÔNG BÁO HẾT HÀNG ===
        if (
            $variant->wasChanged('stock_status') &&
            $variant->stock_status === 'out_of_stock'
        ) {
            foreach ($usersToNotify as $user) {
                $user->notify(new ProductOutOfStock($variant));
            }

            return; // Dừng, không gửi tiếp cảnh báo tồn kho thấp
        }

        // === 2. GỬI CẢNH BÁO TỒN KHO THẤP ===
        if (
            $variant->wasChanged('stock_quantity') &&
            $variant->getOriginal('stock_quantity') >= $threshold &&
            $variant->stock_quantity < $threshold
        ) {
            foreach ($usersToNotify as $user) {
                $user->notify(new LowStockAlert($variant));
            }
        }
        // === 3. GỬI CẢNH BÁO VỪA NHẬP HÀNG NHƯNG VẪN DƯỚI NGƯỠNG ===
        if (
            $variant->wasChanged('stock_status') &&
            $variant->stock_status === 'low' &&
            $variant->getOriginal('stock_status') === 'out_of_stock'
        ) {
            foreach ($usersToNotify as $user) {
                $user->notify(new LowStockAlert($variant));
            }
        }
    }
}
