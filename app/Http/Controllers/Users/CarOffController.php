<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class CarOffController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $items = collect();

        if ($user && $user->cart) {
            // Lấy giỏ hàng từ DB cho user đăng nhập
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
                        'slug' => $item->productVariant->product->slug,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'stock_quantity' => $item->productVariant->stock_quantity ?? 0,
                        'image' => $item->productVariant->image_url ?? '',
                    ];
                });
        } else {
            // Lấy giỏ hàng từ session nếu chưa đăng nhập
            $sessionCart = session('cart', []);
            $items = collect($sessionCart)->map(function ($data) {
                if (!is_array($data)) {
                    if (is_object($data)) {
                        $data = (array) $data;
                    } else {
                        return null;
                    }
                }

                if (!isset($data['variant_id'], $data['price'], $data['quantity'])) {
                    return null;
                }

                $variant = ProductVariant::with('product')->find($data['variant_id']);

                if (!$variant || !$variant->product) {
                    return null;
                }

                return [
                    'id' => $data['variant_id'],
                    'name' => $variant->product->name,
                    'slug' => $variant->product->slug,
                    'price' => (float)$data['price'],
                    'quantity' => (int)$data['quantity'],
                    'stock_quantity' => $variant->stock_quantity ?? 0,
                    'image' => $data['image'] ?? '',
                ];
            })->filter();
        }

        // Tính tổng tiền (subtotal)
        $subtotal = $items->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $discount = 0;
        $total = $subtotal;

        // Xử lý voucher giảm giá nếu có
        if ($voucher = session('applied_voucher')) {
            $discount = $voucher['type'] === 'percentage'
                ? $subtotal * $voucher['value'] / 100
                : min($voucher['value'], $subtotal);

            $total = max(0, $subtotal - $discount);
        }

        if ($request->ajax()) {
            return view('users.partials.cart_items', compact('items', 'subtotal', 'discount', 'total'))->render();
        }

        return view('users.partials.cart_items', compact('items', 'subtotal', 'discount', 'total'));
    }
    public function removeItem(Request $request)
    {
        $itemId = $request->input('item_id');
        $user = auth()->user();
    
        if ($user && $user->cart) {
            // Xóa trong DB
            $cartItem = $user->cart->items()->where('id', $itemId)->first();
            if ($cartItem) {
                $cartItem->delete();
            } else {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 404);
            }
            $items = $user->cart->items()->get()->map(function ($item) {
                return [
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ];
            });
        } else {
            // Xóa trong session
            $cart = Session::get('cart', []);
    
            // Nếu session cart dùng variant_id làm key
            if (array_key_exists($itemId, $cart)) {
                unset($cart[$itemId]);
                Session::put('cart', $cart);
                Session::save();
            } else {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 404);
            }
    
            $items = collect($cart);
        }
    
        $subtotal = $items->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    
        $formattedSubtotal = number_format($subtotal, 0, ',', '.');
    
        return response()->json([
            'success' => true,
            'subtotal' => $formattedSubtotal,
        ]);
    }
    public function updateQuantity(Request $request)
{
    $itemId = $request->input('item_id');
    $quantity = (int) $request->input('quantity');

    if ($quantity < 1) {
        return response()->json(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.'], 422);
    }

    $user = auth()->user();

    if ($user && $user->cart) {
        $cartItem = $user->cart->items()->with('productVariant')->where('id', $itemId)->first();
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
        }

        $stock = $cartItem->productVariant->stock_quantity ?? 0;
        if ($quantity > $stock) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng tối đa còn lại là $stock."
            ], 422);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

    } else {
        // Người dùng chưa đăng nhập: kiểm tra session cart
        $cart = Session::get('cart', []);

        if (!isset($cart[$itemId])) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
        }

        $variant = ProductVariant::find($itemId);
        $stock = $variant->stock_quantity ?? 0;

        if ($quantity > $stock) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng tối đa còn lại là $stock."
            ], 422);
        }

        $cart[$itemId]['quantity'] = $quantity;
        Session::put('cart', $cart);
        Session::save();
    }

    return response()->json(['success' => true]);
}

}
