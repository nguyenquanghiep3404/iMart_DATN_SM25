<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class CarOffController extends Controller
{
    

    public function index(Request $request)
{
    $user = auth()->user();
    $items = collect();

    if ($user && $user->cart) {
        // Load cart items từ DB cho user đã đăng nhập
        $items = $user->cart->items()
            ->with('cartable.product', 'cartable.attributeValues.attribute')
            ->get()
            ->filter(fn($item) => $item->cartable && $item->cartable->product)
            ->map(function ($item) {
                $variant = $item->cartable;

                // Lấy các thuộc tính biến thể dạng key => value
                $attributes = $variant->attributeValues->mapWithKeys(fn($attrVal) => [
                    $attrVal->attribute->name => $attrVal->value
                ]);

                // Lấy tồn kho mới nhất từ bảng product_inventories (inventory_type = 'new')
                $stockQuantity = \App\Models\ProductInventory::where('product_variant_id', $variant->id)
                    ->where('inventory_type', 'new')
                    ->sum('quantity');

                return [
                    'id' => $item->id,
                    'name' => $variant->product->name,
                    'slug' => $variant->product->slug ?? '',
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'stock_quantity' => $stockQuantity,
                    'image' => $variant->image_url ?? '',
                    'variant_attributes' => $attributes,
                ];
            });

    } else {
        // User chưa đăng nhập, lấy cart từ session
        $sessionCart = session('cart', []);

        $items = collect($sessionCart)->map(function ($data) {
            if (!isset($data['cartable_type'], $data['cartable_id'])) {
                return null;
            }

            $cartableType = $data['cartable_type'];
            $cartableId = $data['cartable_id'];

            switch ($cartableType) {
                case \App\Models\ProductVariant::class:
                    $cartable = \App\Models\ProductVariant::with(['product', 'attributeValues.attribute'])
                        ->find($cartableId);
                    break;
                default:
                    return null;
            }

            if (!$cartable || !$cartable->product) {
                return null;
            }

            $attributes = $cartable->attributeValues->mapWithKeys(fn($attrVal) => [
                $attrVal->attribute->name => $attrVal->value
            ]);

            // Lấy tồn kho mới nhất
            $stockQuantity = \App\Models\ProductInventory::where('product_variant_id', $cartable->id)
                ->where('inventory_type', 'new')
                ->sum('quantity');

            return [
                'id' => $cartableId,
                'name' => $cartable->product->name,
                'slug' => $cartable->product->slug ?? '',
                'price' => (float)($data['price'] ?? 0),
                'quantity' => (int)($data['quantity'] ?? 1),
                'stock_quantity' => $stockQuantity,
                'image' => $data['image'] ?? $cartable->image_url ?? '',
                'variant_attributes' => $attributes,
            ];
        })->filter();
    }

    // Tính tổng tiền trước giảm giá
    $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);

    // Lấy thông tin giảm giá (nếu có)
    $appliedCoupon = session('applied_coupon');
    $discount = $appliedCoupon['discount'] ?? 0;
    $voucherCode = $appliedCoupon['code'] ?? null;

    $total = max(0, $subtotal - $discount);

    // Trả về view tương ứng, ví dụ:
    if ($request->ajax()) {
        return view('users.partials.cart_items', compact('items', 'total'))->render();
    }

    return view('users.partials.cart_items', compact('items', 'total'));
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

            $stock = ProductInventory::where('product_variant_id', $cartItem->productVariant->id)
                ->where('inventory_type', 'new')
                ->sum('quantity');
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
            $stock = ProductInventory::where('product_variant_id', $variant->id)
                ->where('inventory_type', 'new')
                ->sum('quantity');

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
