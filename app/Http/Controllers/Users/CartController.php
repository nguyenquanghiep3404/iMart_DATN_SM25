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

        $appliedCoupon = session('applied_coupon');
        $discount = 0;
        $voucherCode = null;

        if ($appliedCoupon) {
            $discount = $appliedCoupon['discount'] ?? 0;
            $voucherCode = $appliedCoupon['code'] ?? null;
        }

        $total = max(0, $subtotal - $discount);

        return view('users.cart.layout.main', compact(
            'items', 'subtotal', 'discount', 'total', 'voucherCode'
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
            $totalQuantity = 0;
        
            if (auth()->check()) {
                $user = auth()->user();
                $cartModel = Cart::firstOrCreate(['user_id' => $user->id]);
                $totalQuantity = $cartModel->items()->sum('quantity');
            } else {
                $cart = session()->get('cart', []);
                $totalQuantity = array_sum(array_column($cart, 'quantity'));
            }
        
            return response()->json([
                'success' => $successMsg,
                'cartItemCount' => $totalQuantity,
            ]);
        }
        return back()->with('success', $successMsg);
    }

    public function removeItem(Request $request)
    {
        $id = $request->input('item_id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm cần xóa.'], 400);
        }
    
        $totalQuantity = 0;
        $total = 0;
    
        if (auth()->check()) {
            $item = CartItem::find($id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
            }
            $cartId = $item->cart_id;
            $item->delete();
    
            $remaining = CartItem::where('cart_id', $cartId)->get();
            $totalQuantity = $remaining->sum('quantity');
            $total = $remaining->sum(fn($i) => $i->price * $i->quantity);
    
            if ($totalQuantity === 0) $this->clearDiscountIfCartEmpty();
    
        } else {
            $cart = session('cart', []);
            if (!isset($cart[$id])) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
            }
            unset($cart[$id]);
    
            if (empty($cart)) {
                session()->forget(['cart', 'applied_coupon', 'discount', 'applied_voucher']);
                $totalQuantity = 0; $total = 0;
            } else {
                session()->put('cart', $cart);
                session()->save();
    
                $totalQuantity = collect($cart)->sum('quantity');
                $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            }
        }
    
        return response()->json([
            'success' => true,
            'total' => $totalQuantity ? number_format($total, 0, ',', '.') . '₫' : '0₫',
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
    // add mã voucher
    public function applyVoucherAjax(Request $request)
    {
        try {
            $request->validate([
                'voucher_code' => 'required|string',
            ]);

            $userId = auth()->id();
            $voucherCode = $request->input('voucher_code');

            \Log::info("User ID $userId áp dụng mã giảm giá: $voucherCode");

            // Tìm coupon hợp lệ đang active và trong thời gian hiệu lực
            $coupon = Coupon::where('code', $voucherCode)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('status', 'active')
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.',
                ]);
            }

            // Lấy giỏ hàng của user
            $cart = auth()->user()?->cart;

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giỏ hàng của bạn.',
                ]);
            }

            // Kiểm tra giỏ hàng có sản phẩm hay không
            if ($cart->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi áp dụng mã giảm giá.',
                ]);
            }

            // Tính tổng tiền giỏ hàng
            $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

            // Kiểm tra điều kiện tổng tiền tối thiểu
            if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "Đơn hàng phải đạt tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để áp dụng mã này.",
                ]);
            }

            // Kiểm tra số lần dùng tối đa toàn hệ thống
            $totalUsageCount = $coupon->usages()->count();
            if ($coupon->max_uses && $totalUsageCount >= $coupon->max_uses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết lượt.',
                ]);
            }

            // Kiểm tra số lần dùng tối đa của user
            $userUsageCount = $coupon->usages()->where('user_id', $userId)->count();
            if ($coupon->max_uses_per_user && $userUsageCount >= $coupon->max_uses_per_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã sử dụng mã này tối đa số lần cho phép.',
                ]);
            }

            // Tính giảm giá
            $discount = $coupon->calculateDiscount($subtotal);
            $totalAfterDiscount = max($subtotal - $discount, 0);

            // Lưu thông tin coupon vào session
            session()->put('applied_coupon', [
                'code' => $coupon->code,
                'discount' => round($discount),
            ]);

            return response()->json([
                'success' => true,
                'discount' => round($discount),
                'total_after_discount' => round($totalAfterDiscount),
                'message' => 'Mã giảm giá đã được áp dụng.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
                'errors' => $e->errors(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi áp mã giảm giá: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra phía máy chủ.',
            ], 500);
        }
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
        // Validate request
        $request->validate([
            'products' => 'required|array',
            'products.*.product_variant_id' => 'required|integer|exists:product_variants,id',
            'products.*.quantity' => 'required|integer|min:1|max:5',
        ]);
    
        $user = auth()->user();
        $cart = session()->get('cart', []);
    
        // Lấy hoặc tạo giỏ hàng DB nếu user đăng nhập
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
    
            // Xây dựng mô tả biến thể (thuộc tính)
            $variantDescription = $variant->attributeValues
                ->map(fn($attr) => "{$attr->attribute->name}: {$attr->value}")
                ->implode(', ');
    
            // Lấy số lượng hiện có trong giỏ (session + DB nếu user đăng nhập)
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
    
            // Kiểm kho nếu quản lý kho
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
    
            // Tính giá (ưu tiên giá sale nếu còn hạn)
            $now = now();
            $isOnSale = $variant->sale_price &&
                        (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                        (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
    
            $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;
    
            // Cập nhật hoặc thêm mới vào giỏ hàng session
            if (isset($cart[$variant->id])) {
                $cart[$variant->id]['quantity'] += $item['quantity'];
                $cart[$variant->id]['price'] = $finalPrice; // Cập nhật giá nếu có thay đổi
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
    
            // Cập nhật giỏ hàng DB nếu user đăng nhập
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
    
            $results[] = [
                'variant_id' => $variant->id,
                'success' => true,
                'message' => "Đã thêm sản phẩm '{$variant->product->name} - {$variantDescription}' thành công.",
            ];
        }
    
        // Lưu giỏ hàng session cuối cùng
        session()->put('cart', $cart);
    
        // Tính tổng số lượng sản phẩm trong giỏ hàng
        if ($user) {
            $totalQuantity = CartItem::where('cart_id', $cartModel->id)->sum('quantity');
        } else {
            $totalQuantity = collect($cart)->sum('quantity');
        }
    
        return response()->json([
            'results' => $results,
            'cartItemCount' => $totalQuantity,
        ]);
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

        // Dữ liệu phản hồi về tổng tiền và số lượng = 0
        return response()->json([
            'success' => true,
            'message' => 'Giỏ hàng đã được xóa sạch.',
            'cartItemCount' => 0,
            'total' => '0₫',
            'totalQuantity' => 0,
        ]);
    }
}
