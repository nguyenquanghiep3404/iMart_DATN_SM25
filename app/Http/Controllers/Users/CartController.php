<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cart = $user->cart;

        $items = $cart ? $cart->items()->with('productVariant.product', 'productVariant.attributeValues.attribute')->get() : collect();

        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $voucher = session('applied_voucher');
        $discount = 0;

        if ($voucher) {
            $discount = $voucher['type'] === 'percentage'
                ? $subtotal * $voucher['value'] / 100
                : min($voucher['value'], $subtotal);
        }

        $total = $subtotal - $discount;

        return view('users.cart.layout.main', compact(
            'items', 'subtotal', 'discount', 'total', 'voucher'
        ));
    }

    public function add(Request $request)
{
    $request->validate([
        'product_id'   => 'required|integer|exists:products,id',
        'variant_key'  => 'required|string',
        'quantity'     => 'required|integer|min:1|max:5',
    ]);

    $product = Product::findOrFail($request->product_id);

    // Tìm biến thể đúng theo variant_key
    $variant = ProductVariant::where('product_id', $product->id)->get()
        ->first(function ($variant) use ($request) {
            $attributes = $variant->attributeValues->pluck('value')->toArray();
            return implode('_', $attributes) === $request->variant_key;
        });

    if (!$variant) {
        return back()->with('error', 'Không tìm thấy biến thể phù hợp.');
    }

    $now = now();
    $finalPrice = $variant->sale_price &&
                  $variant->sale_price_starts_at <= $now &&
                  $variant->sale_price_ends_at >= $now
                  ? $variant->sale_price
                  : $variant->price;

    // Thêm vào session giỏ hàng
    $cart = session()->get('cart', []);
    $itemKey = $variant->id;

    // Nếu là sản phẩm mới, xóa mã giảm giá cũ (nếu có)
    if (!isset($cart[$itemKey])) {
        session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
    }

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

    // Thêm vào DB
    $cartModel = Cart::firstOrCreate([
        'user_id' => auth()->id(),
    ]);

    $existingItem = CartItem::where('cart_id', $cartModel->id)
        ->where('product_variant_id', $variant->id)
        ->first();

    if ($existingItem) {
        $existingItem->quantity += $request->quantity;
        $existingItem->price = $finalPrice;
        $existingItem->save();
    } else {
        CartItem::create([
            'cart_id'            => $cartModel->id,
            'product_variant_id' => $variant->id,
            'quantity'           => $request->quantity,
            'price'              => $finalPrice,
        ]);
    }

    return back()->with('success', 'Đã thêm vào giỏ hàng! Mã giảm giá (nếu có) cần áp dụng lại.');
}


    public function removeItem(Request $request)
    {
        $id = $request->input('item_id');
        $item = CartItem::findOrFail($id);
        $cartId = $item->cart_id;
        $item->delete();

        $this->clearDiscountIfCartEmpty();

        return response()->json([
            'success' => true,
            'total' => number_format($this->calculateTotal(), 0, ',', '.') . '₫',
            'totalQuantity' => CartItem::where('cart_id', $cartId)->sum('quantity'),
        ]);
    }

    public function updateQuantity(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity');

        $item = CartItem::with('productVariant')->findOrFail($itemId);

        if ($quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng không hợp lệ.'
            ], 422);
        }

        $item->quantity = $quantity;
        $item->save();

        return response()->json([
            'success' => true,
            'subtotal' => number_format($item->price * $quantity, 0, ',', '.') . '₫',
            'total' => number_format($this->calculateTotal(), 0, ',', '.') . '₫',
        ]);
    }

    public function applyVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string'
        ]);

        $code = trim($request->voucher_code);
        $voucher = Coupon::whereRaw('lower(code) = ?', [strtolower($code)])
                         ->where('status', 'active')->first();

        if (!$voucher) {
            return back()->with('error', 'Mã giảm giá không tồn tại hoặc không hoạt động.');
        }

        $now = now();
        if ($now->lt($voucher->start_date) || $now->gt($voucher->end_date)) {
            return back()->with('error', 'Mã giảm giá đã hết hạn hoặc chưa đến ngày áp dụng.');
        }

        if ($this->getCartSubtotal() < $voucher->min_order_amount) {
            return back()->with('error', 'Đơn hàng chưa đạt mức tối thiểu để dùng mã này.');
        }

        $usedCount = CouponUsage::where('coupon_id', $voucher->id)
            ->where('user_id', auth()->id())
            ->count();

        if ($voucher->max_uses_per_user && $usedCount >= $voucher->max_uses_per_user) {
            return back()->with('error', 'Bạn đã sử dụng mã này đủ số lần.');
        }

        session()->put('applied_voucher', [
            'id'    => $voucher->id,
            'code'  => $voucher->code,
            'type'  => $voucher->type,
            'value' => $voucher->value,
        ]);

        return back()->with('success', 'Áp dụng mã giảm giá thành công.');
    }

    public function applyVoucherAjax(Request $request)
{
    $code = trim($request->input('code'));
    $user = auth()->user();

    $coupon = Coupon::whereRaw('lower(code) = ?', [strtolower($code)])->first();

    if (!$coupon) {
        return response()->json(['message' => 'Mã giảm giá không tồn tại.'], 422);
    }

    // Kiểm tra trạng thái
    if ($coupon->status !== 'active') {
        return response()->json(['message' => 'Mã giảm giá chưa được kích hoạt.'], 422);
    }

    // Kiểm tra thời gian hiệu lực
    if ($coupon->start_date && now()->lt($coupon->start_date)) {
        return response()->json(['message' => 'Mã giảm giá chưa bắt đầu áp dụng.'], 422);
    }

    if ($coupon->end_date && now()->gt($coupon->end_date)) {
        return response()->json(['message' => 'Mã giảm giá đã hết hạn.'], 422);
    }

    // Kiểm tra user đã dùng chưa
    if ($coupon->usedBy($user->id)) {
        return response()->json(['message' => 'Bạn đã sử dụng mã giảm giá này.'], 422);
    }

    // ⚠️ KIỂM TRA SAU CÁC BƯỚC KHÁC
    if (session()->has('applied_coupon')) {
        return response()->json(['message' => 'Bạn chỉ được áp dụng một mã giảm giá cho mỗi đơn hàng.'], 422);
    }

    // Tính toán và lưu session
    $discountAmount = $coupon->calculateDiscount($this->getCartSubtotal());

    session([
        'applied_coupon' => $coupon->code,
        'discount' => $discountAmount
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công!',
        'subtotal' => formatCurrency($this->getCartSubtotal() - $discountAmount),
        'total' => formatCurrency($this->getCartSubtotal() - $discountAmount),
        'discount' => formatCurrency($discountAmount),
    ]);
}


    private function calculateTotal()
    {
        $cart = auth()->user()->cart;
        return $cart
            ? $cart->items->sum(fn($item) => $item->price * $item->quantity)
            : 0;
    }

    private function getCartSubtotal()
    {
        $cart = auth()->user()->cart;
        return $cart
            ? $cart->items->sum(fn($item) => $item->price * $item->quantity)
            : 0;
    }
    private function clearDiscountIfCartEmpty()
{
    $cart = auth()->user()->cart;
    if (!$cart || $cart->items()->count() === 0) {
        session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
    }
}

}
