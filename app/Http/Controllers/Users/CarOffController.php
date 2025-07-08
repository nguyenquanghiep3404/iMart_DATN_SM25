<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CarOffController extends Controller
{
    public function index(Request $request)
{
    $user = auth()->user();
    $items = collect();

    if ($user && $user->cart) {
        $items = $user->cart->items()
            ->with('productVariant.product', 'productVariant.attributeValues.attribute')
            ->get()
            ->filter(fn($item) =>
                $item->productVariant && $item->productVariant->product
            )
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->productVariant->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'stock_quantity' => $item->productVariant->stock_quantity ?? 0,
                ];
            });
    } else {
        $sessionCart = session('cart', []);
        $items = collect($sessionCart)->map(function ($data) {
            $variant = \App\Models\ProductVariant::with('product')->find($data['variant_id']);
            return [
                'id' => $data['variant_id'],
                'name' => $variant?->product->name ?? 'Không xác định',
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'stock_quantity' => $variant?->stock_quantity ?? 0,
            ];
        })->filter(fn($item) => $item['name'] !== 'Không xác định');
    }

    $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);
    $discount = 0;
    $total = $subtotal;

    if ($voucher = session('applied_voucher')) {
        $discount = $voucher['type'] === 'percentage'
            ? $subtotal * $voucher['value'] / 100
            : min($voucher['value'], $subtotal);
        $total = max(0, $subtotal - $discount);
    }
    // dd(session('cart'));
    // dd($items->first());     
    // dd($items);     
    if ($request->ajax()) {
        return view('users.partials.cart_items', compact('items', 'subtotal', 'discount', 'total'))->render();
    }

    return view('users.partials.cart_items', compact('items', 'subtotal', 'discount', 'total'));
}

}
