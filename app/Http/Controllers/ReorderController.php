<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\CartManager;

class ReorderController extends Controller
{
    public function reorder(Request $request, Order $order)
    {

        $cartService = app(CartManager::class);
        $addedCount = 0;
        $skipped = [];

        foreach ($order->items as $item) {
            $variant = $item->variant;

            if (!$variant || $variant->stock_status !== 'in_stock' || $variant->sellable_stock < $item->quantity) {
                $skipped[] = $variant?->product?->name ?? 'Không xác định';
                continue;
            }

            if ($cartService->addToCart($variant->id, $item->quantity)) {
                $addedCount++;
            } else {
                $skipped[] = $variant->product->name ?? 'Không xác định';
            }
        }

        $total = $order->items->count();
        if ($addedCount === $total) {
            Session::flash('success', '✅ Đã thêm tất cả sản phẩm từ đơn hàng cũ vào giỏ hàng của bạn.');
        } elseif ($addedCount > 0) {
            $missing = implode(', ', $skipped);
            Session::flash('warning', "⚠️ Đã thêm {$addedCount}/{$total} sản phẩm. Một số sản phẩm đã hết hàng: {$missing}");
        } else {
            Session::flash('error', '❌ Không thể thêm sản phẩm nào vì tất cả đã hết hàng.');
        }

        return redirect()->route('cart.index');
    }
}
