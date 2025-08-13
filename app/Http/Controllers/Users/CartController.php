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
use Illuminate\Support\Facades\Storage;


class CartController extends Controller
{
    public function index()
    {
        // dd(session()->all());
        $appliedCoupon = session('applied_coupon');
        if (isset($appliedCoupon['source_type']) && $appliedCoupon['source_type'] === 'buy-now') {
            session()->forget('applied_coupon');
        }
        $pointsApplied = session('points_applied');
        if (isset($pointsApplied['source_type']) && $pointsApplied['source_type'] === 'buy-now') {
            session()->forget('points_applied');
        }
        $user = auth()->user();
        $hasItems = false;
        if ($user) {
            $hasItems = $user->cart && $user->cart->items()->count() > 0;
        } else {
            $sessionCart = session('cart', []);
            $hasItems = !empty($sessionCart);
        }

        if (!$hasItems && session()->has('applied_coupon')) {
            $appliedCoupon = session('applied_coupon');
            if (isset($appliedCoupon['source_type']) && $appliedCoupon['source_type'] === 'cart') {
                session()->forget('applied_coupon');
            }
        }
        $items = collect();
        $pointsBalance = $user ? $user->loyalty_points_balance : 0;

        if ($user && $user->cart) {
            // Load cart items từ DB cho user đã đăng nhập
            $items = $user->cart->items()
                ->with('cartable.product.coverImage', 'cartable.attributeValues.attribute', 'cartable.primaryImage')
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
                        'image' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) . '?v=' . time() : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg')),
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
                        $cartable = \App\Models\ProductVariant::with(['product.coverImage', 'attributeValues.attribute', 'primaryImage'])
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
                    'image' => $data['image'] ?? ($cartable && $cartable->primaryImage && file_exists(storage_path('app/public/' . $cartable->primaryImage->path)) ? Storage::url($cartable->primaryImage->path) . '?v=' . time() : ($cartable && $cartable->product && $cartable->product->coverImage && file_exists(storage_path('app/public/' . $cartable->product->coverImage->path)) ? Storage::url($cartable->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg'))),
                    'variant_attributes' => $attributes,
                    'cartable_type' => $cartableType,
                ];
            })->filter();
        }

        // Tính tổng tiền trước giảm giá
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity); // Sửa $item['price'] thành $item->price

        // Lấy thông tin giảm giá (nếu có)
        $appliedCoupon = session('applied_coupon');
        $discount = 0;
        $voucherCode = null;

        if ($appliedCoupon && ($appliedCoupon['source_type'] ?? null) === 'cart') {
            // Kiểm tra coupon có còn hiệu lực không
            $coupon = \App\Models\Coupon::where('code', $appliedCoupon['code'] ?? null)->first();

            if (!$coupon || $coupon->end_date < now() || $coupon->status !== 'active') {
                // Coupon hết hạn hoặc không hợp lệ
                session()->forget('applied_coupon');
            } else {
                // Coupon hợp lệ
                $discount = $appliedCoupon['discount'] ?? 0;
                $voucherCode = $appliedCoupon['code'] ?? null;
            }
        }

        $total = max(0, $subtotal - $discount);
        $pointsApplied = session('points_applied', ['points' => 0, 'discount' => 0]);
        $pointsDiscount = $pointsApplied['discount'] ?? 0;
        $total = max(0, $total - $pointsDiscount);
        // Lấy coupon còn hiệu lực (ví dụ đơn giản)
        $availableCoupons = Coupon::where('status', 'active')
        ->where('is_public', 1)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->where(function ($query) use ($subtotal) {
            $query->whereNull('min_order_amount')
                ->orWhere('min_order_amount', '<=', $subtotal);
        })
        ->get()
        ->filter(function ($coupon) use ($user) {
            // Đếm số lần đã sử dụng tổng thể
            $totalUsed = DB::table('coupon_usages')
                ->where('coupon_id', $coupon->id)
                ->count();
            if ($coupon->max_uses !== null && $totalUsed >= $coupon->max_uses) {
                return false;
            }

            // Đếm số lần user này đã dùng
            if ($user && $coupon->max_uses_per_user !== null) {
                $usedByUser = DB::table('coupon_usages')
                    ->where('coupon_id', $coupon->id)
                    ->where('user_id', $user->id)
                    ->count();
                if ($usedByUser >= $coupon->max_uses_per_user) {
                    return false;
                }
            }
            return true;
        });
        $totalPointsToEarn = $items->sum(function($item) {
            return ($item->points_to_earn ?? 0) * $item->quantity;
        });
        // Lấy số dư điểm hiện tại của người dùng
        $pointsBalance = $user ? $user->loyalty_points_balance : 0;
        // dd($items->pluck('image'));
        return view('users.cart.layout.main', compact('items', 'subtotal', 'discount', 'total', 'voucherCode','availableCoupons', 'totalPointsToEarn', 'pointsBalance','pointsApplied'));
    }

    // thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
            'product_variant_id' => 'required_without:product_id|integer|exists:product_variants,id',
            'product_id' => 'required_without:product_variant_id|integer|exists:products,id',
            'variant_key' => 'nullable|string',
        ]);

        // 2. Lấy biến thể sản phẩm
        if (!empty($validated['product_variant_id'])) {
            $variant = ProductVariant::with('product.coverImage', 'attributeValues', 'primaryImage')
                ->find($validated['product_variant_id']);
            if (!$variant) {
                return response()->json(['error' => 'Không tìm thấy biến thể sản phẩm.'], 404);
            }
            $product = $variant->product;
        } else {
            $product = Product::findOrFail($validated['product_id']);
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

        $quantity = (int) $validated['quantity'];

        // 3. Xác định giá bán hiện tại
        $now = now();
        $isOnSale = $variant->sale_price &&
            (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
            (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;

        // 4. Lấy số lượng hiện có trong giỏ
        $quantityInCart = 0;
        $cart = session()->get('cart', []);
        $itemKey = $variant->id;

        if (isset($cart[$itemKey])) {
            $quantityInCart = $cart[$itemKey]['quantity'];
        }

        $cartModel = null;
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

        // 5. Tính tổng số lượng mong muốn
        $totalRequested = $quantityInCart + $quantity;

        // 6. Lấy tồn kho khả dụng
        $availableStock = $variant->inventories()
            ->where('inventory_type', 'new')
            ->selectRaw('SUM(quantity - quantity_committed) as available_stock')
            ->value('available_stock');

        // 7. Kiểm tra tồn kho
        if ($variant->manage_stock && $availableStock !== null && $totalRequested > $availableStock) {
            $remaining = max(0, $availableStock - $quantityInCart);
            $message = $quantityInCart > 0
                ? "Bạn đã có {$quantityInCart} sản phẩm trong giỏ. Hệ thống chỉ còn {$remaining} sản phẩm nữa."
                : "Số lượng vượt quá tồn kho. Hiện chỉ còn {$remaining} sản phẩm.";
            return response()->json(['error' => $message], 422);
        }

        // 8. Reset coupon nếu là sản phẩm mới thêm
        session()->forget(['applied_coupon', 'discount', 'applied_voucher']);

        // 9. Cập nhật giỏ hàng session
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $imagePath = null;
            if ($variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path))) {
                $imagePath = Storage::url($variant->primaryImage->path) . '?v=' . time();
            } elseif ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path))) {
                $imagePath = Storage::url($variant->product->coverImage->path) . '?v=' . time();
            } else {
                $imagePath = asset('images/placeholder.jpg');
            }

            $cart[$itemKey] = [
                'cartable_type' => ProductVariant::class,
                'cartable_id'   => $variant->id,
                'product_id'    => $variant->product_id,
                'variant_id'    => $variant->id,
                'name'          => $variant->name ?? $variant->product->name,
                'price'         => $finalPrice,
                'quantity'      => $quantity,
                'image'         => $imagePath,
            ];
        }
        session()->put('cart', $cart);

        // 10. Cập nhật giỏ hàng DB nếu user đăng nhập
        if (auth()->check() && $cartModel) {
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

        // 11. Chuẩn bị phản hồi
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
            $totalQuantity = auth()->check()
                ? $cartModel->items()->sum('quantity')
                : array_sum(array_column(session()->get('cart', []), 'quantity'));

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

        // ==== Biến trạng thái kiểm tra ====
        $voucherFailed = false;
        $pointsFailed = false;
        $voucherMinAmount = 0;
        $pointsDiscount = 0;
        $voucherRemoved = false;

        // ==== Lấy session voucher & điểm ====
        $appliedCoupon = session('applied_coupon', []);
        $isCartCoupon = ($appliedCoupon['source_type'] ?? 'cart') === 'cart';
        $code = $isCartCoupon ? ($appliedCoupon['code'] ?? null) : null;

        $pointsAppliedKey = 'points_applied';
        $pointsApplied = session($pointsAppliedKey, ['points' => 0, 'discount' => 0]);
        $pointsDiscount = $pointsApplied['discount'] ?? 0;

        try {
            $subtotal = 0;
            $totalQuantity = 0;

            if (auth()->check()) {
                // ==== User đăng nhập ====
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

            } else {
                // ==== User khách ====
                $cart = session('cart', []);
                if (!isset($cart[$itemId])) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
                }

                $tempCart = $cart;
                unset($tempCart[$itemId]);

                $subtotal = collect($tempCart)->sum(fn($i) => $i['price'] * $i['quantity']);
                $totalQuantity = collect($tempCart)->sum('quantity');
            }

            // ==== Kiểm tra điều kiện voucher ====
            if ($isCartCoupon && $code) {
                $coupon = Coupon::where('code', $code)->first();
                if ($coupon) {
                    $voucherMinAmount = $coupon->min_order_amount;
                    if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                        $voucherFailed = true;
                    }
                }
            }

            // ==== Kiểm tra điều kiện điểm thưởng ====
            if ($subtotal < $pointsDiscount) {
                $pointsFailed = true;
            }

            // ==== Nếu fail nhưng chưa force_remove ====
            if (($voucherFailed || $pointsFailed) && !$forceRemove) {
                return response()->json([
                    'success' => false,
                    'voucher_failed' => $voucherFailed,
                    'points_failed' => $pointsFailed,
                    'voucher_min_amount' => $voucherMinAmount > 0
                        ? number_format($voucherMinAmount, 0, ',', '.') . '₫'
                        : null,
                ]);
            }

            // ==== Gỡ các ưu đãi fail ====
            if ($voucherFailed) {
                session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
                $voucherRemoved = true;
            }
            if ($pointsFailed) {
                session()->forget($pointsAppliedKey);
                $pointsDiscount = 0;
            }

            // ==== Xóa sản phẩm ====
            if (auth()->check()) {
                $item->delete();

                // Xóa trong session cart nếu có
                $cartSession = session()->get('cart', []);
                if (isset($cartSession[$item->cartable_id])) {
                    unset($cartSession[$item->cartable_id]);
                    session()->put('cart', $cartSession);
                }

                // Nếu giỏ hàng trống
                if ($remainingItems->isEmpty()) {
                    if ($isCartCoupon) {
                        session()->forget(['applied_coupon', 'discount', 'applied_voucher', $pointsAppliedKey]);
                        $voucherRemoved = true;
                    }
                    $subtotal = 0;
                    $totalQuantity = 0;
                }

            } else {
                // User khách
                if (empty($tempCart)) {
                    session()->forget('cart');
                    if ($isCartCoupon) {
                        session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
                        $voucherRemoved = true;
                    }
                    session()->forget($pointsAppliedKey);
                    $subtotal = 0;
                    $totalQuantity = 0;
                } else {
                    session()->put('cart', $tempCart);
                }
            }

            // ==== Tính tổng mới ====
            $discount = 0;
            if ($isCartCoupon && $code && !$voucherRemoved) {
                $coupon = Coupon::where('code', $code)->first();
                if ($coupon) {
                    $discount = round($coupon->calculateDiscount($subtotal));
                }
            }

            $totalAfterDiscount = max(0, $subtotal - $discount - $pointsDiscount);

            return response()->json([
                'success' => true,
                'totalQuantity' => $totalQuantity,
                'total_before_discount' => number_format($subtotal, 0, ',', '.') . '₫',
                'discount' => $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫',
                'points_discount' => $pointsDiscount > 0 ? '-' . number_format($pointsDiscount, 0, ',', '.') . '₫' : '0₫',
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
    
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $itemId   = $request->input('item_id');
        $quantity = (int) $request->input('quantity');

        $newPrice = 0;
        $totalBeforeDiscount = 0;
        $discount = 0;
        $totalAfterDiscount = 0;
        $voucherRemoved = false;
        $pointsRemoved = false;
        $forceUpdate = $request->boolean('force_update', false);

        $appliedCoupon = session('applied_coupon');
        $couponCode = $appliedCoupon['code'] ?? null;
        $sourceType = $appliedCoupon['source_type'] ?? 'cart';

        $voucherFailed = false;
        $pointsFailed = false;
        $voucherMinAmount = 0;
        $pointsDiscount = 0;

        if (auth()->check()) {
            // --- Auth user ---
            $item = CartItem::with('cartable', 'cart')->findOrFail($itemId);
            $variant = $item->cartable;

            // nếu cartable không phải ProductVariant (ví dụ TradeInItem) thì bỏ qua check inventory
            if ($variant instanceof \App\Models\ProductVariant) {
                $now = now();
                $isOnSale = $variant->sale_price &&
                    (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                    (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
                $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

                // Tính available_stock = SUM(quantity - quantity_committed)
                $availableStock = \App\Models\ProductInventory::where('product_variant_id', $variant->id)
                    ->where('inventory_type', 'new')
                    ->selectRaw('COALESCE(SUM(quantity - quantity_committed),0) as available_stock')
                    ->value('available_stock');

                $availableStock = (int) ($availableStock ?? 0);

                // Số lượng đã có trong giỏ của user cho variant này (trừ item đang cập nhật)
                $currentCartQty = CartItem::where('cart_id', $item->cart_id)
                    ->where('cartable_type', \App\Models\ProductVariant::class)
                    ->where('cartable_id', $variant->id)
                    ->where('id', '!=', $item->id)
                    ->sum('quantity');

                $currentCartQty = (int) $currentCartQty;

                // Số lượng tối đa user có thể đặt thêm cho item này
                $remainingStock = max(0, $availableStock - $currentCartQty);

                if ($quantity > $remainingStock) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sản phẩm chỉ còn {$remainingStock} sản phẩm trong kho.",
                    ], 422);
                }

                // Tạo collection items (dùng để tính tổng trước khi áp voucher)
                $cart = auth()->user()->cart()->with('items.cartable')->first();
                $items = $cart->items->map(function ($i) use ($itemId, $quantity, $newPrice) {
                    return (object)[
                        'price' => ($i->id == $itemId) ? $newPrice : $i->price,
                        'quantity' => ($i->id == $itemId) ? $quantity : $i->quantity,
                    ];
                });

                $totalBeforeDiscount = $items->sum(fn($i) => $i->price * $i->quantity);
            } else {
                // nếu cartable không phải product variant, vẫn cập nhật giá/quantity nhưng bỏ qua check tồn kho
                $now = now();
                $newPrice = $variant->price ?? 0;

                $cart = auth()->user()->cart()->with('items.cartable')->first();
                $items = $cart->items->map(function ($i) use ($itemId, $quantity, $newPrice) {
                    return (object)[
                        'price' => ($i->id == $itemId) ? ($i->cartable->price ?? $newPrice) : $i->price,
                        'quantity' => ($i->id == $itemId) ? $quantity : $i->quantity,
                    ];
                });

                $totalBeforeDiscount = $items->sum(fn($i) => $i->price * $i->quantity);
            }

        } else {
            // --- Guest user ---
            $cart = session()->get('cart', []);
            if (!isset($cart[$itemId])) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
            }

            $variantId = $cart[$itemId]['variant_id'] ?? null;
            $variant = $variantId ? \App\Models\ProductVariant::find($variantId) : null;
            if (!$variant) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không hợp lệ.'], 404);
            }

            $now = now();
            $isOnSale = $variant->sale_price &&
                (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
                (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
            $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

            // Tính available_stock = SUM(quantity - quantity_committed)
            $availableStock = \App\Models\ProductInventory::where('product_variant_id', $variant->id)
                ->where('inventory_type', 'new')
                ->selectRaw('COALESCE(SUM(quantity - quantity_committed),0) as available_stock')
                ->value('available_stock');

            $availableStock = (int) ($availableStock ?? 0);

            // Số lượng đã có trong session (trừ mục đang update) — phòng trường hợp có nhiều key cùng variant
            $currentCartQty = 0;
            foreach ($cart as $key => $entry) {
                if (($entry['variant_id'] ?? null) == $variant->id && $key != $itemId) {
                    $currentCartQty += (int) ($entry['quantity'] ?? 0);
                }
            }

            $remainingStock = max(0, $availableStock - $currentCartQty);
            if ($quantity > $remainingStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm chỉ còn {$remainingStock} sản phẩm trong kho.",
                ], 422);
            }

            // Cập nhật session cart (chưa persist DB)
            $cart[$itemId]['quantity'] = $quantity;
            $cart[$itemId]['price'] = $newPrice;
            session()->put('cart', $cart);

            $totalBeforeDiscount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        }

        // ===== Kiểm tra voucher =====
        if ($couponCode && $sourceType === 'cart') {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon) {
                $voucherMinAmount = $coupon->min_order_amount;
                if ($coupon->min_order_amount && $totalBeforeDiscount < $coupon->min_order_amount) {
                    $voucherFailed = true;
                } else {
                    $discount = round($coupon->calculateDiscount($totalBeforeDiscount));
                }
            }
        }

        // ===== Kiểm tra điểm thưởng =====
        $pointsApplied = session('points_applied', ['points' => 0, 'discount' => 0]);
        $pointsDiscount = $pointsApplied['discount'] ?? 0;

        if ($totalBeforeDiscount < $pointsDiscount) {
            $pointsFailed = true;
        }

        // ===== Nếu fail nhưng chưa force update =====
        if (($voucherFailed || $pointsFailed) && !$forceUpdate && !$request->boolean('force_points_removal', false)) {
            return response()->json([
                'success' => false,
                'voucher_failed' => $voucherFailed,
                'points_failed' => $pointsFailed,
                'voucher_min_amount' => $voucherMinAmount > 0
                    ? number_format($voucherMinAmount, 0, ',', '.') . '₫'
                    : null,
                'message' => 'Một hoặc nhiều ưu đãi không đủ điều kiện sử dụng.',
            ]);
        }

        // ===== Gỡ các ưu đãi fail =====
        if ($voucherFailed) {
            session()->forget('applied_coupon');
            $voucherRemoved = true;
            $discount = 0;
        }
        if ($pointsFailed) {
            session()->forget('points_applied');
            $pointsRemoved = true;
            $pointsDiscount = 0;
        }

        // ===== Cập nhật giỏ hàng (persist vào DB nếu auth) =====
        if (auth()->check()) {
            // $item đã lấy ở trên
            $item->quantity = $quantity;
            $item->price = $newPrice;
            $item->save();
        } else {
            // session đã cập nhật phía trên
        }

        // ===== Tính tổng mới =====
        $totalAfterDiscount = max($totalBeforeDiscount - $discount - $pointsDiscount, 0);

        return response()->json([
            'success' => true,
            'subtotal_before_dc' => number_format($totalBeforeDiscount, 0, ',', '.') . '₫',
            'discount' => $discount > 0 && !$voucherRemoved ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫',
            'points_discount' => $pointsDiscount > 0 ? '-' . number_format($pointsDiscount, 0, ',', '.') . '₫' : '0₫',
            'total_after_dc' => number_format($totalAfterDiscount, 0, ',', '.') . '₫',
            'voucher_removed' => $voucherRemoved,
            'points_removed' => $pointsRemoved,
        ]);
    }


    public function applyVoucherAjax(Request $request)
    {
        try {
            $request->validate([
                'voucher_code' => 'required|string',
            ]);

            $voucherCode = $request->input('voucher_code');
            $appliedCode = session('applied_coupon.code');

            // Nếu đã áp dụng mã này rồi
            if ($appliedCode === $voucherCode) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Mã giảm giá này đã được áp dụng thành công rồi.',
                    'discount' => session('applied_coupon.discount', 0),
                    'total_after_discount' => null,
                    'source_type' => session('applied_coupon.source_type', 'cart'),
                ]);
            }

            $isLoggedIn = auth()->check();
            \Log::info("Người dùng " . ($isLoggedIn ? 'ID ' . auth()->id() : 'guest') . " áp dụng voucher: {$voucherCode}");

            // Lấy mã giảm giá
            $coupon = Coupon::where('code', $voucherCode)->first();
            if (!$coupon || $coupon->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ.']);
            }

            if ($coupon->start_date && $coupon->start_date->isFuture()) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá chưa bắt đầu.']);
            }

            if ($coupon->end_date && $coupon->end_date->isPast()) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá đã hết hạn.']);
            }

            // Kiểm tra giới hạn sử dụng
            if ($coupon->max_uses && $coupon->usages()->count() >= $coupon->max_uses) {
                return response()->json(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.']);
            }

            if ($isLoggedIn && $coupon->max_uses_per_user) {
                $used = $coupon->usages()->where('user_id', auth()->id())->count();
                if ($used >= $coupon->max_uses_per_user) {
                    return response()->json(['success' => false, 'message' => 'Bạn đã sử dụng mã này tối đa số lần cho phép.']);
                }
            }

            // ====== Tính subtotal & xác định nguồn dữ liệu ======
            $subtotal = null;
            $sourceType = null;
            if (session()->has('buy_now_session')) {
                $buyNowData = session('buy_now_session');
                if (isset($buyNowData['items']) && is_array($buyNowData['items'])) {
                    $subtotal = collect($buyNowData['items'])->sum(fn($item) =>
                        (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1)
                    );
                } elseif (isset($buyNowData['price'], $buyNowData['quantity'])) {
                    $subtotal = (float)$buyNowData['price'] * (int)$buyNowData['quantity'];
                }
                $sourceType = 'buy-now';
            }

            if (is_null($subtotal) && $isLoggedIn) {
                $cart = auth()->user()?->cart()->with('items')->first();
                if ($cart && $cart->items->isNotEmpty()) {
                    $subtotal = $cart->items->sum(fn($i) => $i->price * $i->quantity);
                    $sourceType = 'cart';
                }
            }

            if (is_null($subtotal) && !$isLoggedIn) {
                $cartItems = session('cart', []);
                if (is_array($cartItems) && count($cartItems) > 0) {
                    $subtotal = collect($cartItems)->sum(fn($i) =>
                        (float)($i['price'] ?? 0) * (int)($i['quantity'] ?? 1)
                    );
                    $sourceType = 'cart';
                }
            }

            if (is_null($subtotal) || $subtotal <= 0) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm hợp lệ.']);
            }
            if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu để áp dụng voucher này là ' . number_format($coupon->min_order_amount, 0, ',', '.') . '₫.',
                ]);
            }
            // Lấy thông tin điểm giảm (nếu có)
            $pointsApplied = session('points_applied', ['points' => 0, 'discount' => 0]);
            $pointsDiscount = $pointsApplied['discount'] ?? 0;

            // Kiểm tra điều kiện áp voucher với subtotal và điểm giảm
            if (($subtotal - $pointsDiscount) <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể áp dụng mã giảm giá khi đơn hàng đã được thanh toán hết bằng điểm thưởng.',
                ]);
            }

            $discount = round($coupon->calculateDiscount($subtotal));
            $totalAfterPoints = $subtotal - $pointsDiscount;

            if ($discount > $totalAfterPoints) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mức giảm giá của voucher vượt quá giá trị đơn hàng còn lại sau khi trừ điểm.',
                ]);
            }

            // ====== Tính giảm giá voucher ======
            $discount = round($coupon->calculateDiscount($subtotal));
            $totalDiscount = $discount + $pointsDiscount;
            $totalAfterDiscount = max($subtotal - $totalDiscount, 0);

            // Lưu thông tin mã đã áp dụng vào session
            session()->put('applied_coupon', [
                'id'          => $coupon->id,
                'code'        => $coupon->code,
                'discount'    => $discount,
                'source_type' => $sourceType,
            ]);

            return response()->json([
                'success'              => true,
                'discount'             => $discount,
                'points_discount'      => $pointsDiscount,
                'total_after_discount' => $totalAfterDiscount,
                'message'              => 'Mã giảm giá đã được áp dụng.',
                'source_type'          => $sourceType,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
                'errors'  => $e->errors()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi áp mã giảm giá: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi áp mã.',
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
                ? $cart->items()->with('productVariant.product.coverImage', 'productVariant.attributeValues.attribute', 'productVariant.primaryImage')->get()
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
            $variant = ProductVariant::with('product.coverImage', 'attributeValues', 'primaryImage')->find($item['product_variant_id']);
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
                    'image' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) . '?v=' . time() : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg')),
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

        session()->forget(['cart', 'applied_coupon', 'discount', 'applied_voucher','points_applied']);

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
                        'image' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) . '?v=' . time() : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg')),
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
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng chức năng này.']);
        }

        $user = Auth::user();

        // Xác định subtotal & nguồn dữ liệu ưu tiên buy-now, sau đó cart (đăng nhập hoặc session)
        $subtotal = null;
        $sourceType = null;

        // Ưu tiên xử lý buy-now
        if (session()->has('buy_now_session')) {
            $buyNowData = session('buy_now_session');
            if (isset($buyNowData['items']) && is_array($buyNowData['items'])) {
                $subtotal = collect($buyNowData['items'])->sum(fn($item) =>
                    (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1)
                );
            } elseif (isset($buyNowData['price'], $buyNowData['quantity'])) {
                $subtotal = (float)$buyNowData['price'] * (int)$buyNowData['quantity'];
            }
            $sourceType = 'buy-now';
        }

        // Nếu chưa có subtotal, kiểm tra giỏ hàng đăng nhập
        if (is_null($subtotal)) {
            $cart = $user->cart()->with('items')->first();
            if ($cart && $cart->items->isNotEmpty()) {
                $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
                $sourceType = 'cart';
            }
        }

        // Nếu chưa có subtotal, kiểm tra giỏ hàng session (khách)
        if (is_null($subtotal)) {
            $sessionCart = session('cart', []);
            if (is_array($sessionCart) && count($sessionCart) > 0) {
                $subtotal = collect($sessionCart)->sum(fn($item) =>
                    (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0)
                );
                $sourceType = 'cart';
            }
        }

        if (is_null($subtotal) || $subtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm hợp lệ.']);
        }

        // Lấy giảm giá từ coupon đã áp dụng (nếu có)
        $couponDiscount = session('applied_coupon.discount', 0);
        $cartTotal = max(0, $subtotal - $couponDiscount);

        // Validate điểm sử dụng
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $pointsToUse = (int) $request->input('points');

        if ($pointsToUse > $user->loyalty_points_balance) {
            return response()->json(['success' => false, 'message' => 'Bạn không đủ điểm thưởng để sử dụng.']);
        }

        $rateValue = Cache::remember('points_to_currency_rate', 3600, function () {
            return DB::table('system_settings')->where('key', 'points_to_currency_rate')->value('value');
        });
        $conversionRate = (float) ($rateValue ?? 1);
        $discountAmount = $pointsToUse * $conversionRate;

        if ($discountAmount > $cartTotal) {
            return response()->json(['success' => false, 'message' => 'Số điểm áp dụng không được vượt quá tổng giá trị đơn hàng.']);
        }

        // Lưu điểm áp dụng vào session, kèm theo nguồn (sourceType)
        session()->put('points_applied', [
            'points' => $pointsToUse,
            'discount' => $discountAmount,
            'source_type' => $sourceType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng điểm thưởng thành công!',
            'discount_amount' => $discountAmount,
            'new_grand_total' => $cartTotal - $discountAmount,
            'source_type' => $sourceType,
        ]);
    }

    // gỡ mã giảm giá
    public function removeCoupon(Request $request)
    {
        // Gỡ mã khuyến mãi khỏi session
        session()->forget('applied_coupon');

        // Tính toán lại giỏ hàng nếu cần
        $cart = session('cart', []);

        // Tính subtotal (tổng giá trước giảm)
        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Trừ tiếp giảm giá từ điểm thưởng nếu có
        $pointsDiscount = session('points_applied.discount', 0);
        $totalAfterRemove = $subtotal - $pointsDiscount;

        return response()->json([
            'success' => true,
            'message' => 'Mã khuyến mãi đã được gỡ bỏ.',
            'new_total' => $totalAfterRemove,
        ]);
    }


    // gỡ điểm thưởng
    public function removePoints(Request $request)
    {
        // Xóa điểm thưởng
        session()->forget('points_applied');

        // Lấy cart hiện tại
        $cart = session('cart', []);

        // Tính subtotal
        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Lấy discount từ voucher nếu có
        $voucherDiscount = 0;
        if (session()->has('applied_coupon')) {
            // Giả sử bạn lưu discount của voucher trong session
            $voucherDiscount = session('applied_coupon.discount', 0);
        }

        // Tổng tiền mới = subtotal - voucher discount (vì điểm thưởng bị xóa)
        $totalAfterRemove = max(0, $subtotal - $voucherDiscount);

        return response()->json([
            'success' => true,
            'message' => 'Điểm thưởng đã được gỡ bỏ khỏi đơn hàng.',
            'new_total' => $totalAfterRemove,
        ]);
    }
}
