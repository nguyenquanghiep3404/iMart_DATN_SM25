<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;

class AbandonedCartController  extends Controller
{
    public function index()
    {
        $abandonedCarts = AbandonedCart::with([
            'user',
            'cart.items.cartable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\ProductVariant::class => ['product'],
                    \App\Models\TradeInItem::class => [], // nếu bạn dùng TradeInItem trong giỏ
                ]);
            }
        ])->latest()->paginate(15);
        $totalAbandonedCarts = AbandonedCart::count();
        return view('admin.abandoned_carts.index', compact('abandonedCarts','totalAbandonedCarts'));
    }

    public function show($id)
    {
        $cart = \App\Models\AbandonedCart::with([
            'user',
            'logs', // 👈 THÊM DÒNG NÀY
            'cart.items.cartable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\ProductVariant::class => ['product'],
                    \App\Models\TradeInItem::class => [],
                ]);
            }
        ])->findOrFail($id);
    
        // Tính tổng tiền từ các cart items
        $total = $cart->cart->items->sum(function ($item) {
            $price = $item->price ?? ($item->cartable->price ?? 0);
            return $item->quantity * $price;
        });
    
    
        return view('admin.abandoned_carts.show', compact('cart', 'total'));
    }
    
}
