<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\ProductVariant;

class CartManager
{
    public function addToCart($variantId, $quantity)
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant || $variant->stock_status !== 'in_stock') {
            return false;
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->firstOrCreate([]);
            $item = $cart->items()->firstOrNew([
                'cartable_type' => ProductVariant::class,
                'cartable_id' => $variantId,
            ]);
            $item->price = $variant->price;
            $item->quantity += $quantity;
            $item->save();
        } else {
            $cart = session('cart', []);
            $found = false;

            foreach ($cart as $key => &$entry) {
                if (
                    $entry['cartable_type'] === ProductVariant::class &&
                    $entry['cartable_id'] === $variantId
                ) {
                    $entry['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }


            if (!$found) {
                $cart[$variantId] = [
                    'cartable_type' => ProductVariant::class,
                    'cartable_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $variant->sale_price ?? $variant->price,
                    'image' => $variant->image_url,
                ];
            }

            session(['cart' => $cart]);
        }

        return true;
    }
}
