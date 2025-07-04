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
        $usersToNotify = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'inventory_manager']);
        })->get();

        // === ƯU TIÊN GỬI: Hết hàng (và chỉ gửi khi stock_status VỪA CHUYỂN thành 'out_of_stock') ===
        if (
            $variant->isDirty('stock_status') &&
            $variant->stock_status === 'out_of_stock'
        ) {
            foreach ($usersToNotify as $user) {
                $user->notify(new ProductOutOfStock($variant));
            }

            return; // Dừng lại, không gửi tiếp cảnh báo tồn kho thấp
        }

        // === THỨ HAI: Gửi cảnh báo tồn kho thấp khi số lượng thay đổi và thấp hơn ngưỡng ===
        if ($variant->isDirty('stock_quantity')) {
            $threshold = 5;

            if ($variant->stock_quantity < $threshold) {
                foreach ($usersToNotify as $user) {
                    $user->notify(new LowStockAlert($variant));
                }
            }
        }
    }
}
