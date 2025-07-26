<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbandonedCart;

class CartRecoveryController extends Controller
{
    public function recover(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('cart.recover_result')->with('error', 'Link khôi phục không hợp lệ.');
        }

        $abandonedCart = AbandonedCart::where('recovery_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$abandonedCart) {
            return redirect()->route('cart.recover_result')->with('error', 'Link khôi phục không hợp lệ hoặc đã sử dụng.');
        }

        // Lưu giỏ hàng vào session
        session(['cart' => $abandonedCart->cart->items->map(function ($item) {
            return [
                'product_variant_id' => $item->cartable_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'cartable_type' => $item->cartable_type,
            ];
        })->toArray()]);

        // Cập nhật trạng thái token đã dùng
        $abandonedCart->status = 'recovered';
        $abandonedCart->save();

        // Redirect về trang giỏ hàng kèm thông báo
        return redirect()->route('cart.index')->with('message', 'Giỏ hàng của bạn đã được khôi phục.');
    }
}
