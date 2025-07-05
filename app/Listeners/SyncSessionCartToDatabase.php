<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

class SyncSessionCartToDatabase
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $sessionCart = session('cart', []);
        if (empty($sessionCart)) return;

        $cartModel = Cart::firstOrCreate(['user_id' => $user->id]);
        $warnings = [];

        foreach ($sessionCart as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if (!$variant) continue;

            $existingItem = CartItem::where('cart_id', $cartModel->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            $sessionQty = $item['quantity'];
            $dbQty = $existingItem ? $existingItem->quantity : 0;
            $totalQty = $sessionQty + $dbQty;

            $availableStock = $variant->manage_stock && $variant->stock_quantity !== null
                ? $variant->stock_quantity
                : PHP_INT_MAX;

            if ($totalQty > $availableStock) {
                $maxCanAdd = max(0, $availableStock - $dbQty);
                if ($maxCanAdd <= 0) {
                    $warnings[] = "Sản phẩm {$item['name']} đã hết hàng hoặc đã có đủ số lượng trong giỏ.";
                    continue;
                }

                $warnings[] = "Sản phẩm {$item['name']} chỉ thêm được tối đa {$maxCanAdd} do giới hạn tồn kho.";
                $sessionQty = $maxCanAdd;
            }

            if ($existingItem) {
                $existingItem->quantity += $sessionQty;
                $existingItem->price = $item['price'];
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id'            => $cartModel->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $sessionQty,
                    'price'              => $item['price'],
                ]);
            }
        }

        session()->forget('cart');

        if (!empty($warnings)) {
            session()->flash('cart_sync_warnings', $warnings);
        }
    }
}
