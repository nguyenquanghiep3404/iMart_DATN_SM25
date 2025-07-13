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
            // User đã đăng nhập, load từ DB
            $items = $user->cart->items()
                ->with('productVariant.product', 'productVariant.attributeValues.attribute')
                ->get()
                ->filter(fn($item) =>
                    $item->productVariant && $item->productVariant->product
                )
                ->map(function ($item) {
                    $attributes = $item->productVariant->attributeValues->mapWithKeys(function ($attrVal) {
                        return [
                            $attrVal->attribute->name => $attrVal->value
                        ];
                    });

                    return [
                        'id' => $item->id,
                        'name' => $item->productVariant->product->name,
                        'slug' => $item->productVariant->product->slug ?? '',
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'stock_quantity' => $item->productVariant->stock_quantity ?? 0,
                        'image' => $item->productVariant->image_url ?? '',
                        'variant_attributes' => $attributes,
                    ];
                });
        } else {
            // Chưa đăng nhập, lấy giỏ hàng từ session
            $sessionCart = session('cart', []);

            $items = collect($sessionCart)->map(function ($data) {
                $variant = \App\Models\ProductVariant::with(['product', 'attributeValues.attribute'])
                    ->find($data['variant_id']);

                if (!$variant || !$variant->product) {
                    return null;
                }

                $attributes = $variant->attributeValues->mapWithKeys(function ($attrVal) {
                    return [
                        $attrVal->attribute->name => $attrVal->value
                    ];
                });

                return [
                    'id' => $data['variant_id'],
                    'name' => $variant->product->name,
                    'slug' => $variant->product->slug ?? '',
                    'price' => (float)$data['price'],
                    'quantity' => (int)$data['quantity'],
                    'stock_quantity' => $variant->stock_quantity ?? 0,
                    'image' => $data['image'] ?? $variant->image_url ?? '',
                    'variant_attributes' => $attributes,
                ];
            })->filter(); // Loại bỏ null
        }

        // Tính tổng tiền
        $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);

        // Giảm giá (nếu có)
        $appliedCoupon = session('applied_coupon');
        $discount = $appliedCoupon['discount'] ?? 0;
        $voucherCode = $appliedCoupon['code'] ?? null;

        $total = max(0, $subtotal - $discount);
        // dd($items);
        return view('users.cart.layout.main', compact(
            'items', 'subtotal', 'discount', 'total', 'voucherCode'
        ));
    }

    // thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        // Validate dữ liệu đầu vào, yêu cầu ít nhất product_variant_id hoặc product_id phải có
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
        $request->validate([
            'item_id' => 'required|integer',
            'force_remove' => 'sometimes|boolean',
        ]);

        $itemId = $request->input('item_id');
        $forceRemove = $request->boolean('force_remove', false);
        $userId = auth()->id();
        $appliedCoupon = session('applied_coupon');
        $code = $appliedCoupon['code'] ?? null;
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
                    ->get(); // Lấy danh sách sau khi xoá (giả lập)

                $totalQuantity = $remainingItems->sum('quantity');
                $total = $remainingItems->sum(fn($i) => $i->price * $i->quantity);

                // Kiểm tra voucher trước khi xoá
                if ($code) {
                    $coupon = Coupon::where('code', $code)->first();
                    if ($coupon && $coupon->min_order_amount && $total < $coupon->min_order_amount) {
                        if (!$forceRemove) {
                            return response()->json([
                                'success' => false,
                                'shortfall' => true,
                                'code' => $coupon->code,
                                'required_min' => $coupon->min_order_amount,
                                'current_total' => $total,
                                'message' => "Bạn cần giữ đơn hàng tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để tiếp tục sử dụng mã “{$coupon->code}”. Hiện tại đơn hàng còn lại là " . number_format($total, 0, ',', '.') . "₫."
                            ]);
                        } else {
                            session()->forget('applied_coupon');
                            $voucherRemoved = true;
                        }
                    }
                }

                // Đến đây mới thật sự xoá
                $item->delete();

                return response()->json([
                    'success' => true,
                    'totalQuantity' => $totalQuantity,
                    'total' => number_format($total, 0, ',', '.') . '₫',
                    'voucher_removed' => $voucherRemoved,
                ]);
            } else {
                // Guest (session)
                $cart = session('cart', []);
                if (!isset($cart[$itemId])) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
                }

                $tempCart = $cart;
                unset($tempCart[$itemId]);

                $totalQuantity = collect($tempCart)->sum('quantity');
                $total = collect($tempCart)->sum(fn($i) => $i['price'] * $i['quantity']);

                if ($code) {
                    $coupon = Coupon::where('code', $code)->first();
                    if ($coupon && $coupon->min_order_amount && $total < $coupon->min_order_amount) {
                        if (!$forceRemove) {
                            return response()->json([
                                'success' => false,
                                'shortfall' => true,
                                'code' => $coupon->code,
                                'required_min' => $coupon->min_order_amount,
                                'current_total' => $total,
                                'message' => "Bạn cần giữ đơn hàng tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để tiếp tục sử dụng mã “{$coupon->code}”. Hiện tại đơn hàng còn lại là " . number_format($total, 0, ',', '.') . "₫."
                            ]);
                        } else {
                            session()->forget('applied_coupon');
                            $voucherRemoved = true;
                        }
                    }
                }

                // Tới đây mới cập nhật session
                unset($cart[$itemId]);
                if (empty($cart)) {
                    session()->forget(['cart', 'applied_coupon']);
                    $totalQuantity = 0;
                    $total = 0;
                } else {
                    session()->put('cart', $cart);
                }

                return response()->json([
                    'success' => true,
                    'totalQuantity' => $totalQuantity,
                    'total' => number_format($total, 0, ',', '.') . '₫',
                    'voucher_removed' => $voucherRemoved,
                ]);
            }

        } catch (\Throwable $e) {
            \Log::error("Lỗi khi xoá sản phẩm: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi xoá sản phẩm. Vui lòng thử lại.'
            ], 500);
        }
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
    // public function applyVoucherAjax(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'voucher_code' => 'required|string',
    //         ]);

    //         $userId = auth()->id();
    //         $voucherCode = $request->input('voucher_code');
    //         $appliedCoupon = session('applied_coupon.code') ?? null;

    //         if ($appliedCoupon === $voucherCode) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Mã giảm giá này đã được áp dụng thành công rồi.',
    //                 'discount' => session('applied_coupon.discount') ?? 0,
    //             ]);
    //         }
    //         \Log::info("User ID $userId áp dụng mã giảm giá: $voucherCode");

    //         // Tìm coupon hợp lệ đang active và trong thời gian hiệu lực
    //         $coupon = Coupon::where('code', $voucherCode)->first();

    //         if (!$coupon) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mã giảm giá không tồn tại.',
    //             ]);
    //         }

    //         // Kiểm tra ngày bắt đầu
    //         if ($coupon->start_date && $coupon->start_date->isFuture()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mã giảm giá chưa đến thời gian bắt đầu.',
    //             ]);
    //         }

    //         // Kiểm tra ngày kết thúc
    //         if ($coupon->end_date && $coupon->end_date->isPast()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mã giảm giá đã hết hạn.',
    //             ]);
    //         }

    //         // Kiểm tra trạng thái
    //         if ($coupon->status !== 'active') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mã giảm giá không hợp lệ.',
    //             ]);
    //         }

    //         $hasUsedThisCoupon = $coupon->usages()
    //             ->where('user_id', $userId)
    //             ->exists();

    //         if ($hasUsedThisCoupon) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Bạn đã sử dụng mã giảm giá này rồi. Vui lòng thử mã khác.',
    //             ]);
    //         }

    //         // Lấy giỏ hàng của user
    //         $cart = auth()->user()?->cart()->with('items')->first();

    //         if (!$cart) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Không tìm thấy giỏ hàng của bạn.',
    //             ]);
    //         }

    //         // Kiểm tra giỏ hàng có sản phẩm hay không
    //         if ($cart->items->isEmpty()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi áp dụng mã giảm giá.',
    //             ]);
    //         }

    //         // Tính tổng tiền giỏ hàng
    //         $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

    //         // Kiểm tra điều kiện tổng tiền tối thiểu
    //         if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Đơn hàng phải đạt tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để áp dụng mã này.",
    //             ]);
    //         }

    //         // Kiểm tra số lần dùng tối đa toàn hệ thống
    //         $totalUsageCount = $coupon->usages()->count();
    //         if ($coupon->max_uses && $totalUsageCount >= $coupon->max_uses) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mã giảm giá đã được sử dụng hết lượt.',
    //             ]);
    //         }

    //         // Kiểm tra số lần dùng tối đa của user
    //         $userUsageCount = $coupon->usages()->where('user_id', $userId)->count();
    //         if ($coupon->max_uses_per_user && $userUsageCount >= $coupon->max_uses_per_user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Bạn đã sử dụng mã này tối đa số lần cho phép.',
    //             ]);
    //         }

    //         // Tính giảm giá
    //         $discount = $coupon->calculateDiscount($subtotal);
    //         $totalAfterDiscount = max($subtotal - $discount, 0);

    //         // Lưu thông tin coupon vào session
    //         session()->put('applied_coupon', [
    //             'code' => $coupon->code,
    //             'discount' => round($discount),
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'discount' => round($discount),
    //             'total_after_discount' => round($totalAfterDiscount),
    //             'message' => 'Mã giảm giá đã được áp dụng.',
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Vui lòng nhập mã giảm giá.',
    //             'errors' => $e->errors(),
    //         ]);
    //     } catch (\Throwable $e) {
    //         \Log::error('Lỗi khi áp mã giảm giá: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Đã có lỗi xảy ra phía máy chủ.',
    //         ], 500);
    //     }
    // }
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

            \Log::info("Người dùng ".($isLoggedIn ? 'ID '.auth()->id() : 'guest')." áp dụng voucher: {$voucherCode}");

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
                if ($coupon->max_uses_per_user &&
                    $coupon->usages()->where('user_id', $userId)->count() >= $coupon->max_uses_per_user) {
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
                    'message' => 'Đơn hàng phải đạt tối thiểu '.number_format($coupon->min_order_amount, 0, ',', '.').'₫ để áp dụng mã này.',
                ]);
            }

            /*------------------------------------------------------------------
            | 7. Tính & lưu giảm giá
            *-----------------------------------------------------------------*/
            $discount            = round($coupon->calculateDiscount($subtotal));
            $totalAfterDiscount  = max($subtotal - $discount, 0);

            session()->put('applied_coupon', [
                'code'     => $coupon->code,
                'discount' => $discount,
            ]);

            /*------------------------------------------------------------------
            | 8. Phản hồi thành công
            *-----------------------------------------------------------------*/
            return response()->json([
                'success'             => true,
                'discount'            => $discount,
                'total_after_discount'=> $totalAfterDiscount,
                'message'             => 'Mã giảm giá đã được áp dụng.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.', 'errors' => $e->errors()]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi áp mã giảm giá: '.$e->getMessage());
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
}
