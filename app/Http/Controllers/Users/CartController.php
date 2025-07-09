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
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{
    public function index()
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
                        'slug' => $item->productVariant->product->slug ?? '',
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'stock_quantity' => $item->productVariant->stock_quantity ?? 0,
                        'image' => $item->productVariant->image_url ?? '',
                    ];
                });
        } else {
            $sessionCart = session('cart', []);
    
            $items = collect($sessionCart)->map(function ($data) {
                $variant = ProductVariant::with('product')->find($data['variant_id']);
                return [
                    'id' => $data['variant_id'],
                    'name' => $variant?->product->name ?? 'Không xác định',
                    'slug' => $variant?->product->slug ?? '',
                    'price' => (float)$data['price'],
                    'quantity' => (int)$data['quantity'],
                    'stock_quantity' => $variant?->stock_quantity ?? 0,
                    'image' => $data['image'] ?? '',
                ];
            })->filter(fn($item) => $item['name'] !== 'Không xác định');
        }
    
        $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);
        $voucher = session('applied_voucher');
        $discount = 0;
    
        if ($voucher) {
            $discount = $voucher['type'] === 'percentage'
                ? $subtotal * $voucher['value'] / 100
                : min($voucher['value'], $subtotal);
        }
    
        $total = max(0, $subtotal - $discount);
    
        return view('users.cart.layout.main', compact(
            'items', 'subtotal', 'discount', 'total', 'voucher'
        ));
    }
    
    // thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        // Validate dữ liệu đầu vào, yêu cầu ít nhất product_variant_id hoặc product_id phải có
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:5',
            'product_variant_id' => 'required_without:product_id|integer|exists:product_variants,id',
            'product_id' => 'required_without:product_variant_id|integer|exists:products,id',
            'variant_key' => 'nullable|string',
        ]);

        // Lấy biến thể sản phẩm
        if (!empty($validated['product_variant_id'])) {
            $variant = ProductVariant::find($validated['product_variant_id']);
            if (!$variant) {
                return response()->json(['error' => 'Không tìm thấy biến thể sản phẩm.'], 404);
            }
            $product = $variant->product;
        } else {
            // Tìm product
            $product = Product::findOrFail($validated['product_id']);

            // Tìm biến thể theo variant_key nếu có
            $variant = null;
            if (!empty($validated['variant_key'])) {
                $variant = ProductVariant::where('product_id', $product->id)->get()
                    ->first(function ($v) use ($validated) {
                        $attributes = $v->attributeValues->pluck('value')->toArray();
                        return implode('_', $attributes) === $validated['variant_key'];
                    });
            }

            if (!$variant) {
                $variant = ProductVariant::where('product_id', $product->id)->first();
            }

            if (!$variant) {
                return response()->json(['error' => 'Sản phẩm chưa có biến thể, vui lòng liên hệ quản trị viên.'], 422);
            }
        }

        $quantity = $validated['quantity'];

        // Kiểm tra tồn kho
        $now = now();
        $isOnSale = $variant->sale_price &&
            (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
            (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;

        $cart = session()->get('cart', []);
        $itemKey = $variant->id;
        $quantityInCart = isset($cart[$itemKey]) ? $cart[$itemKey]['quantity'] : 0;

        if (auth()->check()) {
            $user = auth()->user();
            $cartModel = Cart::firstOrCreate(['user_id' => $user->id]);

            $dbItem = CartItem::where('cart_id', $cartModel->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($dbItem) {
                $quantityInCart = $dbItem->quantity;
            }
        }

        $totalRequested = $quantityInCart + $quantity;
        if ($variant->manage_stock && $variant->stock_quantity !== null && $totalRequested > $variant->stock_quantity) {
            $remaining = max(0, $variant->stock_quantity - $quantityInCart);
            return response()->json([
                'error' => 'Số lượng vượt quá tồn kho. Hiện chỉ còn ' . $remaining . ' sản phẩm.'
            ], 422);
        }

        // Nếu sản phẩm mới thì reset coupon
        if (!isset($cart[$itemKey])) {
            session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
        }

        // Cập nhật giỏ hàng session
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name'       => $variant->name ?? $variant->product->name,
                'price'      => $finalPrice,
                'quantity'   => $quantity,
                'image'      => $variant->image_url,
            ];
        }
        session()->put('cart', $cart);

        // Cập nhật database nếu user đã đăng nhập
        if (auth()->check()) {
            $existingItem = CartItem::where('cart_id', $cartModel->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->price = $finalPrice;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id'            => $cartModel->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $quantity,
                    'price'              => $finalPrice,
                ]);
            }
        }

        $successMsg = 'Đã thêm sản phẩm vào giỏ hàng!';

        if ($request->has('buy_now')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => $successMsg,
                    'redirect' => route('cart.index'),
                ]);
            }
            return redirect()->route('cart.index')->with('success', $successMsg);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => $successMsg]);
        }

    return back()->with('success', $successMsg);
    }

    public function removeItem(Request $request)
    {
        // Lấy id sản phẩm trong giỏ hàng để xóa (đảm bảo frontend gửi đúng 'item_id' hoặc sửa lại tên trường nếu cần)
        $id = $request->input('item_id');

        if (auth()->check()) {
            // Trường hợp người dùng đã đăng nhập: xóa trong database
            $item = CartItem::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'
                ], 404);
            }

            $cartId = $item->cart_id;
            $item->delete();

            // Nếu cần, xóa mã giảm giá khi giỏ hàng trống
            $remainingQuantity = CartItem::where('cart_id', $cartId)->sum('quantity');
            if ($remainingQuantity == 0) {
                $this->clearDiscountIfCartEmpty(); // Hàm bạn tự định nghĩa để xóa coupon, voucher,...
            }

            // Tính tổng tiền và tổng số lượng còn lại trong giỏ
            $total = $this->calculateTotal();
            $totalQuantity = $remainingQuantity;

        } else {
            // Trường hợp khách (guest) dùng session để lưu giỏ hàng
            $cart = session('cart', []);

            if (!isset($cart[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'
                ], 404);
            }
            \Log::debug('Remove item id:', ['id' => $id]);
            \Log::debug('Cart before remove:', session('cart'));
            // Xóa sản phẩm khỏi giỏ
            unset($cart[$id]);

            // Nếu giỏ hàng trống, xóa hẳn session cart và các mã giảm giá liên quan
            if (empty($cart)) {
                session()->forget('cart');
                session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
            } else {
                session()->put('cart', $cart);
            }

            session()->save();

            // Tính tổng tiền và tổng số lượng trong giỏ hàng session mới
            $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
            $totalQuantity = collect($cart)->sum('quantity');
        }

        // Trả về JSON thông tin cập nhật cho frontend
        return response()->json([
            'success' => true,
            'total' => number_format($total, 0, ',', '.') . '₫',
            'totalQuantity' => $totalQuantity,
        ]);
    }


    public function updateQuantity(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity');

        if ($quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng không hợp lệ.'
            ], 422);
        }

        if (auth()->check()) {
            // ✅ Người dùng đăng nhập → DB
            $item = CartItem::with('productVariant')->findOrFail($itemId);
            $variant = $item->productVariant;

            $now = now();
            $isOnSale = $variant->sale_price &&
                (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);

            $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

            $item->quantity = $quantity;
            $item->price = $newPrice;
            $item->save();

            $total = $this->calculateTotal();

        } else {
            // ⚠️ Người dùng vãng lai → Session
            $cart = session()->get('cart', []);
            if (!isset($cart[$itemId])) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
            }

            // Cập nhật lại giá
            $variant = ProductVariant::find($cart[$itemId]['variant_id']);
            $now = now();
            $isOnSale = $variant->sale_price &&
                (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);

            $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

            $cart[$itemId]['quantity'] = $quantity;
            $cart[$itemId]['price'] = $newPrice;
            session()->put('cart', $cart);

            $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        }

        return response()->json([
            'success' => true,
            'subtotal' => number_format($newPrice * $quantity, 0, ',', '.') . '₫',
            'total' => number_format($total, 0, ',', '.') . '₫',
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

        // Lấy tổng tiền giỏ hàng
        $subtotal = $this->getCartSubtotal();

        // ⚠️ Kiểm tra giỏ hàng có rỗng không
        if ($subtotal <= 0) {
            return response()->json(['message' => 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm để áp dụng mã giảm giá.'], 422);
        }

        // Kiểm tra số tiền tối thiểu của đơn hàng
        if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
            return response()->json([
                'message' => 'Đơn hàng của bạn cần đạt tối thiểu ' . $this->formatCurrency($coupon->min_order_amount) . ' để áp dụng mã giảm giá.'
            ], 422);
        }

        // Kiểm tra người dùng đã sử dụng mã này chưa
        if ($user && $coupon->usedBy($user->id)) {
            return response()->json(['message' => 'Bạn đã sử dụng mã giảm giá này.'], 422);
        }

        // Chỉ cho phép áp dụng 1 mã giảm giá
        if (session()->has('applied_coupon')) {
            return response()->json(['message' => 'Bạn chỉ được áp dụng một mã giảm giá cho mỗi đơn hàng.'], 422);
    }

    // Tính toán giảm giá
    $discountAmount = $coupon->calculateDiscount($subtotal);

    // Lưu thông tin giảm giá vào session
    session()->forget(['applied_coupon', 'discount']);
    session([
        'applied_coupon' => $coupon->code,
        'discount' => $discountAmount
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công!',
        'subtotal' => $this->formatCurrency($subtotal - $discountAmount),
        'total' => $this->formatCurrency($subtotal - $discountAmount),
        'discount' => $this->formatCurrency($discountAmount),
        'code' => $coupon->code
    ]);
}



    private function calculateTotal()
    {
        $cart = auth()->user()->cart;
        if (!$cart) return 0;

        return CartItem::where('cart_id', $cart->id)
            ->sum(DB::raw('price * quantity'));
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
    private function getCartItems()
    {
        if (auth()->check()) {
            $cart = auth()->user()->cart;
            return $cart
                ? $cart->items()->with('productVariant.product', 'productVariant.attributeValues.attribute')->get()
                : collect();
        }

        // Lấy từ session (cho khách chưa login)
        $sessionCart = session('cart', []);
        return collect($sessionCart)->map(function ($item) {
        return (object)[
            'id' => $item['variant_id'], // Thêm dòng này nếu cần dùng $item->id
            'productVariant' => (object)[
                'id' => $item['variant_id'] ?? null,
                'product' => Product::find($item['product_id']),
                'image_url' => $item['image'] ?? null
            ],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
        ];
    });
    }
    private function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . '₫';
    }


    public function addMultiple(Request $request)
{
    $request->validate([
        'products' => 'required|array',
        'products.*.product_variant_id' => 'required|integer|exists:product_variants,id',
        'products.*.quantity' => 'required|integer|min:1|max:5',
    ]);

    $user = auth()->user();
    $cart = session()->get('cart', []);

    if ($user) {
        $cartModel = Cart::firstOrCreate(['user_id' => $user->id]);
    }

    $results = [];

    foreach ($request->products as $item) {
        $variant = ProductVariant::with('product', 'attributeValues')->find($item['product_variant_id']);

        if (!$variant) {
            $results[] = [
                'variant_id' => $item['product_variant_id'],
                'success' => false,
                'message' => 'Biến thể không tồn tại',
            ];
            continue;
        }

        // Xây dựng mô tả biến thể (gồm cả thuộc tính)
        $variantDescription = $variant->attributeValues
            ->map(function($attr) {
                return "{$attr->attribute->name}: {$attr->value}";
            })
            ->implode(', ');

        // Lấy số lượng hiện có trong giỏ (session + db nếu đăng nhập)
        $quantityInCart = isset($cart[$variant->id]) ? $cart[$variant->id]['quantity'] : 0;
        if ($user) {
            $dbItem = CartItem::where('cart_id', $cartModel->id)
                ->where('product_variant_id', $variant->id)
                ->first();
            if ($dbItem) {
                $quantityInCart = $dbItem->quantity;
            }
        }

        $totalRequested = $quantityInCart + $item['quantity'];

        // Kiểm kho
        if ($variant->manage_stock && $variant->stock_quantity !== null) {
            if ($totalRequested > $variant->stock_quantity) {
                $remaining = max(0, $variant->stock_quantity - $quantityInCart);

                $results[] = [
                    'variant_id' => $variant->id,
                    'success' => false,
                    'message' => "Sản phẩm '{$variant->product->name} - {$variantDescription}' còn tồn kho {$remaining} sản phẩm.",
                ];
                continue;
            }
        }

        // Tính giá (có sale price nếu có)
        $now = now();
        $isOnSale = $variant->sale_price &&
                    (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                    (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;

        // Thêm hoặc cập nhật session cart
        if (isset($cart[$variant->id])) {
            $cart[$variant->id]['quantity'] += $item['quantity'];
        } else {
            $cart[$variant->id] = [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->product->name,
                'price' => $finalPrice,
                'quantity' => $item['quantity'],
                'image' => $variant->image_url,
            ];
        }

        // Cập nhật db cart nếu user đăng nhập
        if ($user) {
            $existingItem = CartItem::where('cart_id', $cartModel->id)
                ->where('product_variant_id', $variant->id)
                ->first();
            if ($existingItem) {
                $existingItem->quantity += $item['quantity'];
                $existingItem->price = $finalPrice;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cartModel->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $finalPrice,
                ]);
            }
        }

        // Trả về kết quả
        $results[] = [
            'variant_id' => $variant->id,
            'success' => true,
            'message' => "Đã thêm sản phẩm '{$variant->product->name} - {$variantDescription}' thành công.",
        ];
    }

    // Lưu session cart cuối cùng
    session()->put('cart', $cart);

    return response()->json(['results' => $results]);
}
public function clearCart(Request $request)
{
    if (auth()->check()) {
        $userId = auth()->id();
        $cart = Cart::where('user_id', $userId)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }
    }

    session()->forget(['cart', 'applied_coupon', 'discount', 'applied_voucher']);

    return response()->json([
        'success' => true,
        'message' => 'Giỏ hàng đã được xóa sạch.',
    ]);
}

}
