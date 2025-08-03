<?php

namespace App\Http\Controllers\Users;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BundleSuggestedProduct;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $items = collect();
        $pointsBalance = $user ? $user->loyalty_points_balance : 0;

        if ($user && $user->cart) {
            // Load cart items từ DB cho user đã đăng nhập
            $items = $user->cart->items()
                ->with('cartable.product', 'cartable.attributeValues.attribute')
                ->get()
                ->filter(fn($item) => $item->cartable && $item->cartable->product)
                ->map(function ($item) {
                    $variant = $item->cartable;
                    $attributes = $variant->attributeValues->mapWithKeys(function ($attrVal) {
                        return [$attrVal->attribute->name => $attrVal->value];
                    });
                    $stockQuantity = \App\Models\ProductInventory::where('product_variant_id', $variant->id)
                        ->where('inventory_type', 'new')
                        ->sum('quantity');
                    $item->points_to_earn = $variant->points_awarded_on_purchase ?? 0;

                    // SỬA Ở ĐÂY: Thêm (object) để chuyển array thành object
                    return (object) [
                        'id' => $item->id,
                        'name' => $variant->product->name,
                        'slug' => $variant->product->slug ?? '',
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'stock_quantity' => $stockQuantity,
                        'image' => $variant->image_url ?? '',
                        'variant_attributes' => $attributes,
                        'cartable_type' => $item->cartable_type,
                        'points_to_earn' => $item->points_to_earn,
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
                $stockQuantity = \App\Models\ProductInventory::where('product_variant_id', $cartable->id)
                    ->where('inventory_type', 'new')
                    ->sum('quantity');

                // SỬA Ở ĐÂY: Thêm (object) để chuyển array thành object
                return (object) [
                    'id' => $cartableId,
                    'name' => $cartable->product->name,
                    'slug' => $cartable->product->slug ?? '',
                    'price' => (float)($data['price'] ?? 0),
                    'quantity' => (int)($data['quantity'] ?? 1),
                    'stock_quantity' => $stockQuantity,
                    'image' => $data['image'] ?? $cartable->image_url ?? '',
                    'variant_attributes' => $attributes,
                    'cartable_type' => $cartableType,
                ];
            })->filter();
        }

        // Tính tổng tiền trước giảm giá
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity); // Sửa $item['price'] thành $item->price

        // Lấy thông tin giảm giá (nếu có)
        $appliedCoupon = session('applied_coupon');
        $discount = $appliedCoupon['discount'] ?? 0;
        $voucherCode = $appliedCoupon['code'] ?? null;

        $total = max(0, $subtotal - $discount);
        // Lấy coupon còn hiệu lực (ví dụ đơn giản)
        $availableCoupons = Coupon::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_public', 1)
            ->where('min_order_amount', '<=', $subtotal)
            ->get();

        $totalPointsToEarn = $items->sum(function($item) {
            return ($item->points_to_earn ?? 0) * $item->quantity;
        });

        // Lấy số dư điểm hiện tại của người dùng
        $pointsBalance = $user ? $user->loyalty_points_balance : 0;
        return view('users.cart.layout.main', compact('items', 'subtotal', 'discount', 'total', 'voucherCode','availableCoupons', 'totalPointsToEarn', 'pointsBalance'));
    }

    // thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        session()->forget('buy_now_session');
        // Validate
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
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
                ->where('cartable_id', $variant->id)
                ->where('cartable_type', ProductVariant::class)
                ->first();


            if ($dbItem) {
                $quantityInCart = $dbItem->quantity;
            }
        }

        $totalRequested = $quantityInCart + $quantity;
        $availableStock = $variant->inventories()
            ->where('inventory_type', 'new')
            ->sum('quantity');

        if ($variant->manage_stock && $availableStock !== null && $totalRequested > $availableStock) {
            $remaining = max(0, $availableStock - $quantityInCart);
            return response()->json([
                'error' => 'Số lượng vượt quá tồn kho. Hiện chỉ còn ' . $remaining . ' sản phẩm.'
            ], 422);
        }

        // Nếu sản phẩm mới thì reset coupon
        // if (!isset($cart[$itemKey])) {
        session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
        // }

        // Cập nhật giỏ hàng session
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'cartable_type' => ProductVariant::class,
                'cartable_id'   => $variant->id,
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
                ->where('cartable_id', $variant->id)
                ->where('cartable_type', ProductVariant::class)
                ->first();
            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->price = $finalPrice;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id'        => $cartModel->id,
                    'cartable_id'    => $variant->id,
                    'cartable_type'  => ProductVariant::class,
                    'quantity'       => $quantity,
                    'price'          => $finalPrice,
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

    // xóa sản phẩm
    public function removeItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'force_remove' => 'sometimes|boolean',
        ]);

        $itemId = $request->input('item_id');
        $forceRemove = $request->boolean('force_remove', false);
        $code = session('applied_coupon.code') ?? null;
        $discount = session('applied_coupon.discount') ?? 0;
        $voucherRemoved = false;

        try {
            if (auth()->check()) {
                $item = CartItem::find($itemId);
                if (!$item) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
                }

                $cartId = $item->cart_id;
                $remainingItems = CartItem::where('cart_id', $cartId)
                    ->where('id', '!=', $itemId)
                    ->get();

                $subtotal = $remainingItems->sum(fn($i) => $i->price * $i->quantity);
                $totalQuantity = $remainingItems->sum('quantity');

                if ($code) {
                    $coupon = Coupon::where('code', $code)->first();
                    if ($coupon && $coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                        if (!$forceRemove) {
                            return response()->json([
                                'success' => false,
                                'shortfall' => true,
                                'code' => $coupon->code,
                                'required_min' => $coupon->min_order_amount,
                                'current_total' => $subtotal,
                                'message' => "Bạn cần giữ đơn hàng tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để tiếp tục sử dụng mã “{$coupon->code}”"
                            ]);
                        } else {
                            session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
                            $voucherRemoved = true;
                            $discount = 0;
                        }
                    }
                }

                // Xóa item trong DB
                $item->delete();

                // Xóa item tương ứng trong session cart để tránh dữ liệu session sai
                $cartSession = session()->get('cart', []);
                if (isset($cartSession[$item->cartable_id])) {
                    unset($cartSession[$item->cartable_id]);
                    session()->put('cart', $cartSession);
                }

                // Nếu giỏ hàng đã trống sau khi xóa, xóa luôn voucher và reset giá trị
                if ($remainingItems->isEmpty()) {
                    session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
                    $subtotal = 0;
                    $totalQuantity = 0;
                    $discount = 0;
                }

            } else {
                // User chưa đăng nhập xử lý session
                $cart = session('cart', []);
                if (!isset($cart[$itemId])) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
                }

                $tempCart = $cart;
                unset($tempCart[$itemId]);

                $subtotal = collect($tempCart)->sum(fn($i) => $i['price'] * $i['quantity']);
                $totalQuantity = collect($tempCart)->sum('quantity');

                if ($code) {
                    $coupon = Coupon::where('code', $code)->first();
                    if ($coupon && $coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                        if (!$forceRemove) {
                            return response()->json([
                                'success' => false,
                                'shortfall' => true,
                                'code' => $coupon->code,
                                'required_min' => $coupon->min_order_amount,
                                'current_total' => $subtotal,
                                'message' => "Bạn cần giữ đơn hàng tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để tiếp tục sử dụng mã “{$coupon->code}”. Hiện tại đơn hàng còn lại là " . number_format($subtotal, 0, ',', '.') . "₫."
                            ]);
                        } else {
                            session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
                            $voucherRemoved = true;
                            $discount = 0;
                        }
                    }
                }

                unset($cart[$itemId]);
                if (empty($cart)) {
                    session()->forget(['cart', 'applied_coupon', 'discount', 'applied_voucher']);
                    $subtotal = 0;
                    $totalQuantity = 0;
                    $discount = 0;
                } else {
                    session()->put('cart', $cart);
                }
            }

            $totalAfterDiscount = max(0, $subtotal - $discount);

            return response()->json([
                'success' => true,
                'totalQuantity' => $totalQuantity,
                'total_before_discount' => number_format($subtotal, 0, ',', '.') . '₫',
                'discount' => number_format($discount, 0, ',', '.') . '₫',
                'total_after_discount' => number_format($totalAfterDiscount, 0, ',', '.') . '₫',
                'voucher_removed' => $voucherRemoved,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Lỗi khi xoá sản phẩm: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi xoá sản phẩm. Vui lòng thử lại.'
            ], 500);
        }
    }

    // cập nhập số lượng
    public function updateQuantity(Request $request)
{
    $itemId = $request->input('item_id');
    $quantity = $request->input('quantity');

    if ($quantity < 1) {
        return response()->json(['success' => false, 'message' => 'Số lượng không hợp lệ.'], 422);
    }

    $items = collect();

    // Lấy danh sách sản phẩm sau khi cập nhật
    if (auth()->check()) {
        $item = CartItem::with('cartable')->findOrFail($itemId);
        $item->update(['quantity' => $quantity]);
        $items = auth()->user()->cart->items()->with('cartable')->get();
    } else {
        $cart = session('cart', []);
        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }
        // Chuyển đổi session cart thành collection để tính toán
        $items = collect($cart)->map(function ($data) {
            return (object) [
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'cartable' => ProductVariant::find($data['variant_id'])
            ];
        });
    }

    // Tính toán lại các giá trị
    $subtotal = $items->sum(fn($i) => $i->price * $i->quantity);

    // Áp dụng lại coupon (nếu có)
    $appliedCoupon = session('applied_coupon');
    $discount = 0;
    $couponIsValid = true;
    if ($appliedCoupon) {
        $coupon = Coupon::where('code', $appliedCoupon['code'])->first();
        if ($coupon && $subtotal >= $coupon->min_order_amount) {
            $discount = round($coupon->calculateDiscount($subtotal));
            session()->put('applied_coupon.discount', $discount);
        } else {
            session()->forget('applied_coupon');
            $couponIsValid = false;
        }
    }

    // Áp dụng lại giảm giá từ điểm thưởng (nếu có)
    $pointsDiscount = 0;
    $pointsApplied = session('points_applied');
    if ($pointsApplied) {
        $pointsDiscount = $pointsApplied['discount'];
        // Đảm bảo không giảm quá tổng tiền sau coupon
        $maxDiscount = max(0, $subtotal - $discount);
        if ($pointsDiscount > $maxDiscount) {
            $pointsDiscount = $maxDiscount;
            session()->put('points_applied.discount', $pointsDiscount);
        }
    }

    $totalAfterDiscount = max(0, $subtotal - $discount - $pointsDiscount);

    // TÍNH TOÁN LẠI TỔNG ĐIỂM THƯỞNG
    $totalPointsToEarn = $items->sum(function($item) {
        if ($item->cartable && $item->cartable instanceof ProductVariant) {
            return ($item->cartable->points_awarded_on_purchase ?? 0) * $item->quantity;
        }
        return 0;
    });

    return response()->json([
        'success' => true,
        'subtotal_before_dc' => number_format($subtotal, 0, ',', '.') . '₫',
        'discount' => $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫',
        'points_discount' => $pointsDiscount > 0 ? '-' . number_format($pointsDiscount, 0, ',', '.') . '₫' : '0₫',
        'total_after_dc' => number_format($totalAfterDiscount, 0, ',', '.') . '₫',
        'voucher_removed' => !$couponIsValid,
        'total_points_earned' => $totalPointsToEarn,
    ]);
}

    // add mã voucher
    public function applyVoucherAjax(Request $request)
    {
        try {
            /*------------------------------------------------------------------
            | 1. Xác thực input
            *-----------------------------------------------------------------*/
            $request->validate([
                'voucher_code' => 'required|string',
            ]);

            $voucherCode   = $request->input('voucher_code');
            $appliedCode   = session('applied_coupon.code');
            $isLoggedIn    = auth()->check();

            /*------------------------------------------------------------------
            | 2. Nếu mã đã áp dụng trong session → trả kết quả luôn
            *-----------------------------------------------------------------*/
            if ($appliedCode === $voucherCode) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Mã giảm giá này đã được áp dụng thành công rồi.',
                    'discount' => session('applied_coupon.discount', 0),
                ]);
            }

            \Log::info("Người dùng " . ($isLoggedIn ? 'ID ' . auth()->id() : 'guest') . " áp dụng voucher: {$voucherCode}");

            /*------------------------------------------------------------------
            | 3. Tìm & kiểm tra các điều kiện cơ bản của voucher
            *-----------------------------------------------------------------*/
            $coupon = Coupon::where('code', $voucherCode)->first();
            if (!$coupon) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá không tồn tại.']);
            }

            if ($coupon->start_date && $coupon->start_date->isFuture()) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá chưa đến thời gian bắt đầu.']);
            }

            if ($coupon->end_date && $coupon->end_date->isPast()) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá đã hết hạn.']);
            }

            if ($coupon->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ.']);
            }

            /*------------------------------------------------------------------
            | 4. Kiểm tra số lượt dùng (user & hệ thống)
            *-----------------------------------------------------------------*/
            // Tổng lượt dùng toàn hệ thống
            if ($coupon->max_uses && $coupon->usages()->count() >= $coupon->max_uses) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá đã được sử dụng hết lượt.']);
            }

            if ($isLoggedIn) {
                $userId = auth()->id();

                // Đã từng dùng mã này?
                if ($coupon->usages()->where('user_id', $userId)->exists()) {
                    return response()->json(['success' => false, 'message' => 'Bạn đã sử dụng mã giảm giá này rồi. Vui lòng thử mã khác.']);
                }

                // Vượt quá giới hạn / user?
                if (
                    $coupon->max_uses_per_user &&
                    $coupon->usages()->where('user_id', $userId)->count() >= $coupon->max_uses_per_user
                ) {
                    return response()->json(['success' => false, 'message' => 'Bạn đã sử dụng mã này tối đa số lần cho phép.']);
                }
            }

            /*------------------------------------------------------------------
            | 5. Lấy giỏ hàng & tính subtotal
            *-----------------------------------------------------------------*/
            if ($isLoggedIn) {
                $cart = auth()->user()?->cart()->with('items')->first();

                if (!$cart || $cart->items->isEmpty()) {
                    return response()->json(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi áp dụng mã giảm giá.']);
                }

                $subtotal = $cart->items->sum(fn($i) => $i->price * $i->quantity);
            } else {
                // Giỏ hàng khách vãng lai: session('cart') là mảng [variant_id => item]
                $cartItems = session('cart');

                if (!$cartItems || !is_array($cartItems) || count($cartItems) === 0) {
                    return response()->json(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi áp dụng mã giảm giá.']);
                }

                $subtotal = collect($cartItems)->sum(fn($i) => (float)$i['price'] * (int)$i['quantity']);
            }

            /*------------------------------------------------------------------
            | 6. Kiểm tra điều kiện tối thiểu
            *-----------------------------------------------------------------*/
            if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng phải đạt tối thiểu ' . number_format($coupon->min_order_amount, 0, ',', '.') . '₫ để áp dụng mã này.',
                ]);
            }

            /*------------------------------------------------------------------
            | 7. Tính & lưu giảm giá
            *-----------------------------------------------------------------*/
            $discount            = round($coupon->calculateDiscount($subtotal));
            $totalAfterDiscount  = max($subtotal - $discount, 0);

            session()->put('applied_coupon', [
                'id'       => $coupon->id,
                'code'     => $coupon->code,
                'discount' => $discount,
            ]);

            /*------------------------------------------------------------------
            | 8. Phản hồi thành công
            *-----------------------------------------------------------------*/
            return response()->json([
                'success'             => true,
                'discount'            => $discount,
                'total_after_discount' => $totalAfterDiscount,
                'message'             => 'Mã giảm giá đã được áp dụng.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.', 'errors' => $e->errors()]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi áp mã giảm giá: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra phía máy chủ.'], 500);
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
    // add nhiều sản phẩm cùng lúc
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

        $oldCart = $cart; // Lưu giỏ hàng cũ

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

            $variantDescription = $variant->attributeValues
                ->map(fn($attr) => "{$attr->attribute->name}: {$attr->value}")
                ->implode(', ');

            $quantityInCart = isset($cart[$variant->id]) ? $cart[$variant->id]['quantity'] : 0;

            if ($user) {
                $dbItem = CartItem::where('cart_id', $cartModel->id)
                    ->where('cartable_type', \App\Models\ProductVariant::class)
                    ->where('cartable_id', $variant->id)
                    ->first();
                if ($dbItem) {
                    $quantityInCart = $dbItem->quantity;
                }
            }

            $totalRequested = $quantityInCart + $item['quantity'];

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

            $now = now();
            $isOnSale = $variant->sale_price &&
                (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);

            $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;

            if (isset($cart[$variant->id])) {
                $cart[$variant->id]['quantity'] += $item['quantity'];
                $cart[$variant->id]['price'] = $finalPrice;
            } else {
                $cart[$variant->id] = [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => $variant->product->name,
                    'price' => $finalPrice,
                    'quantity' => $item['quantity'],
                    'image' => $variant->image_url,
                    'cartable_type' => \App\Models\ProductVariant::class,
                    'cartable_id' => $variant->id,
                ];
            }

            if ($user) {
                $existingItem = CartItem::where('cart_id', $cartModel->id)
                    ->where('cartable_type', \App\Models\ProductVariant::class)
                    ->where('cartable_id', $variant->id)
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $item['quantity'];
                    $existingItem->price = $finalPrice;
                    $existingItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cartModel->id,
                        'cartable_type' => \App\Models\ProductVariant::class,
                        'cartable_id' => $variant->id,
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

        // --- So sánh kỹ: sản phẩm mới hoặc quantity thay đổi
        $hasNewProduct = false;

        foreach ($request->products as $item) {
            $variantId = $item['product_variant_id'];
            $newQty = $item['quantity'];

            if (!isset($oldCart[$variantId])) {
                // Sản phẩm mới hoàn toàn
                $hasNewProduct = true;
                break;
            } else {
                // Nếu quantity tăng lên (số lượng trước đó trong giỏ hàng cũ)
                if ($oldCart[$variantId]['quantity'] < ($oldCart[$variantId]['quantity'] + $newQty)) {
                    $hasNewProduct = true;
                    break;
                }
            }
        }
        // ---

        if ($hasNewProduct) {
            session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
        }

        session()->put('cart', $cart);

        if ($user) {
            $totalQuantity = CartItem::where('cart_id', $cartModel->id)->sum('quantity');
        } else {
            $totalQuantity = collect($cart)->sum('quantity');
        }

        return response()->json([
            'results' => $results,
            'cartItemCount' => $totalQuantity,
            'voucherReset' => $hasNewProduct,
        ]);
    }
    // xóa toàn bộ giỏ hàng
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



    public function addCombo(Request $request)
    {
        try {
            // Xác thực dữ liệu
            $request->validate([
                'product_bundle_id' => 'required|integer|exists:product_bundles,id', // 👈 thêm dòng này
                'products' => 'required|array',
                'products.*.product_variant_id' => 'required|integer|exists:product_variants,id',
                'products.*.quantity' => 'required|integer|min:1|max:10000',
                'products.*.price' => 'nullable|numeric|min:0', // Giá gửi từ frontend (tùy chọn)
            ]);
            $bundle = ProductBundle::findOrFail($request->product_bundle_id);

            $user = auth()->user();
            $cart = session()->get('cart', []);
            $results = [];
            $totalQuantity = 0;

            // Lấy danh sách product_variant_id để kiểm tra giá ưu đãi
            $variantIds = array_column($request->products, 'product_variant_id');
            $suggestedProducts = BundleSuggestedProduct::where('product_bundle_id', $bundle->id)
                ->whereIn('product_variant_id', $variantIds)
                ->get()
                ->keyBy('product_variant_id');


            if ($user) {
                $cartModel = Cart::firstOrCreate(['user_id' => $user->id]);
            }

            // Kiểm tra nếu có sản phẩm mới để reset coupon
            $hasNewProduct = false;
            $oldCart = $cart;

            foreach ($request->products as $item) {
                $variantId = $item['product_variant_id'];
                $quantity = $item['quantity'];

                // Lấy thông tin biến thể
                $variant = ProductVariant::with('product')->find($variantId);
                if (!$variant) {
                    $results[] = [
                        'variant_id' => $variantId,
                        'success' => false,
                        'message' => 'Biến thể không tồn tại.'
                    ];
                    continue;
                }

                // Kiểm tra tồn kho
                $quantityInCart = isset($cart[$variantId]) ? $cart[$variantId]['quantity'] : 0;
                if ($user) {
                    $dbItem = CartItem::where('cart_id', $cartModel->id)
                        ->where('cartable_type', ProductVariant::class)
                        ->where('cartable_id', $variantId)
                        ->first();
                    if ($dbItem) {
                        $quantityInCart = $dbItem->quantity;
                    }
                }

                $totalRequested = $quantityInCart + $quantity;
                $availableStock = $variant->inventories()
                    ->where('inventory_type', 'new')
                    ->sum('quantity');

                if ($variant->manage_stock && $availableStock !== null && $totalRequested > $availableStock) {
                    $remaining = max(0, $availableStock - $quantityInCart);
                    $results[] = [
                        'variant_id' => $variantId,
                        'success' => false,
                        'message' => "Sản phẩm {$variant->product->name} chỉ còn {$remaining} sản phẩm trong kho."
                    ];
                    continue;
                }

                // Tính giá
                $finalPrice = null;
                if (isset($suggestedProducts[$variantId])) {
                    // Sản phẩm gợi ý: sử dụng discount_value từ bundle_suggested_products
                    $suggested = $suggestedProducts[$variantId];
                    if ($suggested->discount_type === 'fixed_price') {
                        $finalPrice = $suggested->discount_value;
                    } else { // percentage
                        $originalPrice = $variant->sale_price ?? $variant->price;
                        $finalPrice = $originalPrice * (1 - $suggested->discount_value / 100);
                    }
                } else {
                    // Sản phẩm chính: sử dụng sale_price hoặc price
                    $now = now();
                    $isOnSale = $variant->sale_price &&
                        (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                        (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
                    $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;
                }

                // Kiểm tra sản phẩm mới hoặc tăng số lượng
                if (!isset($cart[$variantId]) || $cart[$variantId]['quantity'] < ($quantityInCart + $quantity)) {
                    $hasNewProduct = true;
                }

                // Cập nhật session
                if (isset($cart[$variantId])) {
                    $cart[$variantId]['quantity'] += $quantity;
                    $cart[$variantId]['price'] = $finalPrice;
                } else {
                    $cart[$variantId] = [
                        'cartable_type' => ProductVariant::class,
                        'cartable_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'name' => $variant->name ?? $variant->product->name,
                        'price' => $finalPrice,
                        'quantity' => $quantity,
                        'image' => $variant->image_url,
                    ];
                }

                // Cập nhật database nếu user đã đăng nhập
                if ($user) {
                    $existingItem = CartItem::where('cart_id', $cartModel->id)
                        ->where('cartable_type', ProductVariant::class)
                        ->where('cartable_id', $variant->id)
                        ->first();
                    if ($existingItem) {
                        $existingItem->quantity += $quantity;
                        $existingItem->price = $finalPrice;
                        $existingItem->save();
                    } else {
                        CartItem::create([
                            'cart_id' => $cartModel->id,
                            'cartable_type' => ProductVariant::class,
                            'cartable_id' => $variant->id,
                            'quantity' => $quantity,
                            'price' => $finalPrice,
                        ]);
                    }
                }

                $results[] = [
                    'variant_id' => $variantId,
                    'success' => true,
                    'message' => "Đã thêm sản phẩm {$variant->product->name} vào giỏ hàng."
                ];
            }

            // Reset coupon nếu có sản phẩm mới
            if ($hasNewProduct) {
                session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
            }

            session()->put('cart', $cart);

            // Tính tổng số lượng
            if ($user) {
                $totalQuantity = CartItem::where('cart_id', $cartModel->id)->sum('quantity');
            } else {
                $totalQuantity = collect($cart)->sum('quantity');
            }

            // Kiểm tra lỗi
            $errors = array_filter($results, fn($result) => !$result['success']);
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => array_column($errors, 'message')
                ], 422);
            }

            return response()->json([
                'success' => 'Đã thêm gói sản phẩm vào giỏ hàng thành công!',
                'cartItemCount' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi thêm combo vào giỏ hàng: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi thêm gói vào giỏ hàng.'
            ], 500);
        }
    }
   /**
     * Xử lý AJAX để áp dụng điểm thưởng vào giỏ hàng.
     */
    public function applyPoints(Request $request)
{
    // dd($request->all());
    if (!Auth::check()) {
        return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
    }

    $request->validate([
        'points' => 'required|integer|min:1',
        'type' => 'required|in:cart,buy-now'
    ]);

    $user = Auth::user();
    session()->forget('points_applied');
    $pointsToUse = $request->input('points');
    $type = $request->input('type');


    if ($user->loyalty_points_balance < $pointsToUse) {
        return response()->json(['success' => false, 'message' => 'Số dư điểm không đủ.']);
    }

    $pointConversionRate = 1;
    $discountAmount = $pointsToUse * $pointConversionRate;

    if ($type === 'cart') {
        // Luồng giỏ hàng
        $cart = $user->cart;
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng đang trống.']);
        }

        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $couponDiscount = session('applied_coupon.discount', 0);
        $subtotalAfterCoupon = $subtotal - $couponDiscount;

        if ($discountAmount > $subtotalAfterCoupon) {
            return response()->json(['success' => false, 'message' => 'Số điểm áp dụng vượt quá giá trị đơn hàng.']);
        }

        session(['points_applied' => ['points' => $pointsToUse, 'discount' => $discountAmount]]);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng điểm thành công!',
            'discount_amount' => $discountAmount,
            'new_grand_total' => $subtotalAfterCoupon - $discountAmount
        ]);

    } elseif ($type === 'buy-now') {
        // Luồng mua ngay
        $buyNowSession = session('buy_now_session');

        if (!$buyNowSession) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu mua ngay.']);
        }

        $product = Product::find($buyNowSession['product_id']);
        $variant = ProductVariant::find($buyNowSession['variant_id']);
        $quantity = $buyNowSession['quantity'];

        if (!$product || !$variant || $quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.']);
        }

        $subtotal = $variant->price * $quantity;

        if ($discountAmount > $subtotal) {
            return response()->json(['success' => false, 'message' => 'Số điểm áp dụng vượt quá giá trị đơn hàng.']);
        }

        session(['buy_now_points_applied' => ['points' => $pointsToUse, 'discount' => $discountAmount]]);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng điểm thành công!',
            'discount_amount' => $discountAmount,
            'new_grand_total' => $subtotal - $discountAmount
        ]);
    }

    return response()->json(['success' => false, 'message' => 'Loại yêu cầu không hợp lệ.']);
}

}
