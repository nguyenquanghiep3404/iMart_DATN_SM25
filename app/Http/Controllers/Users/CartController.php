<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;         
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'variant_key'  => 'required|string',
            'quantity'     => 'required|integer|min:1|max:5',
        ]);

        // Lấy sản phẩm
        $product = Product::findOrFail($request->product_id);

        // Tìm biến thể khớp với variant_key
        $variant = ProductVariant::where('product_id', $product->id)->get()
            ->first(function ($variant) use ($request) {
                $attributes = $variant->attributeValues->pluck('value')->toArray();
                return implode('_', $attributes) === $request->variant_key;
            });

        if (!$variant) {
            return back()->with('error', 'Không tìm thấy biến thể phù hợp.');
        }

        // Tính giá cuối cùng
        $now = now();
        $finalPrice = $variant->sale_price &&
                      $variant->sale_price_starts_at <= $now &&
                      $variant->sale_price_ends_at >= $now
                      ? $variant->sale_price
                      : $variant->price;

        // ---- 1. Lưu vào session
        $cart = session()->get('cart', []);

        $itemKey = $variant->id;
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $request->quantity;
        } else {
            $cart[$itemKey] = [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'name'       => $product->name,
                'price'      => $finalPrice,
                'quantity'   => $request->quantity,
                'image'      => $variant->image_url,
            ];
        }

        session()->put('cart', $cart);

        // ---- 2. Lưu vào database

        // Tìm hoặc tạo cart của user
        $cartModel = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        // Tìm xem đã có cart_item với variant chưa
        $existingItem = CartItem::where('cart_id', $cartModel->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($existingItem) {
            // Nếu đã có thì cộng dồn số lượng
            $existingItem->quantity += $request->quantity;
            $existingItem->price = $finalPrice; // cập nhật lại giá nếu cần
            $existingItem->save();
        } else {
            // Tạo mới cart item
            CartItem::create([
                'cart_id'            => $cartModel->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $request->quantity,
                'price'              => $finalPrice,
            ]);
        }

        return back()->with('success', 'Đã thêm vào giỏ hàng!');
    }
    public function index()
    {
        $user = Auth::user();
    
        // Kiểm tra nếu user chưa có cart
        $cart = $user->cart;
        if (!$cart) {
            return view('users.cart.layout.main', [
                'items' => collect(),
                'subtotal' => 0,
                'totalItems' => 0,
                'totalQuantity' => 0,
            ]);
        }
    
        // Truy vấn cart_items cùng productVariant -> product và attributeValues
        $items = CartItem::with([
            'productVariant.product',
            'productVariant.attributeValues.attribute',
        ])
        ->where('cart_id', $cart->id)
        ->get();
    
        // Tính tổng thành tiền, số sản phẩm khác nhau, tổng số lượng
        $subtotal = $items->sum(fn($item) => $item->subtotal);
        $totalItems = $items->count();
        $totalQuantity = $items->sum('quantity'); 
    
        return view('users.cart.layout.main', compact(
            'items',
            'subtotal',
            'totalItems',
            'totalQuantity'
        ));
    }
    public function updateQuantity(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity');
    
        $item = CartItem::findOrFail($itemId);
        $item->quantity = $quantity;
        $item->save();
    
        return response()->json([
            'success' => true,
            'subtotal' => number_format($item->price * $quantity, 0, ',', '.'),
            'total' => number_format($this->calculateTotal(), 0, ',', '.'),
        ]);
    }
    
}
