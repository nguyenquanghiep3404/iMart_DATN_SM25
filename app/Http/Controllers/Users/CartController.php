<?php

namespace App\Http\Controllers\Users;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    // public function index()
    // {
    //     $user = auth()->user();
    //     $items = collect();

    //     if ($user && $user->cart) {
    //         // User đã đăng nhập, load từ DB
    //         $items = $user->cart->items()
    //         ->with('cartable.product', 'cartable.attributeValues.attribute')
    //         ->get()
    //         ->filter(fn($item) =>
    //             $item->cartable && $item->cartable->product
    //         )
    //         ->map(function ($item) {
    //             $variant = $item->cartable;
            
    //             $attributes = $variant->attributeValues->mapWithKeys(function ($attrVal) {
    //                 return [
    //                     $attrVal->attribute->name => $attrVal->value
    //                 ];
    //             });
            
    //             return [
    //                 'id' => $item->id,
    //                 'name' => $variant->product->name,
    //                 'slug' => $variant->product->slug ?? '',
    //                 'price' => $item->price,
    //                 'quantity' => $item->quantity,
    //                 'stock_quantity' => $variant->stock_quantity ?? 0,
    //                 'image' => $variant->image_url ?? '',
    //                 'variant_attributes' => $attributes,
            
    //                 // ✅ Thêm dòng này để view dùng
    //                 'cartable_type' => $item->cartable_type,
    //             ];
    //         });
            
    //     }  else {
    //         // Chưa đăng nhập, lấy giỏ hàng từ session
    //         $sessionCart = session('cart', []);
        
    //         $items = collect($sessionCart)->map(function ($data) {
    //             // Kiểm tra thông tin cần thiết
    //             if (!isset($data['cartable_type']) || !isset($data['cartable_id'])) {
    //                 return null;
    //             }
        
    //             $cartableType = $data['cartable_type'];
    //             $cartableId = $data['cartable_id'];
        
    //             $cartable = null;
        
    //             switch ($cartableType) {
    //                 case \App\Models\ProductVariant::class:
    //                     $cartable = \App\Models\ProductVariant::with(['product', 'attributeValues.attribute'])
    //                         ->find($cartableId);
    //                     break;
        
    //                 // Sau này nếu có thêm:
    //                 // case \App\Models\TradeInItem::class:
    //                 //     $cartable = \App\Models\TradeInItem::with('product')->find($cartableId);
    //                 //     break;
        
    //                 default:
    //                     return null;
    //             }
        
    //             if (!$cartable || !$cartable->product) {
    //                 return null;
    //             }
        
    //             $attributes = $cartable->attributeValues->mapWithKeys(function ($attrVal) {
    //                 return [
    //                     $attrVal->attribute->name => $attrVal->value
    //                 ];
    //             });
        
    //             return [
    //                 'id' => $cartableId,
    //                 'name' => $cartable->product->name,
    //                 'slug' => $cartable->product->slug ?? '',
    //                 'price' => (float)($data['price'] ?? 0),
    //                 'quantity' => (int)($data['quantity'] ?? 1),
    //                 'stock_quantity' => $cartable->stock_quantity ?? 0,
    //                 'image' => $data['image'] ?? $cartable->image_url ?? '',
    //                 'variant_attributes' => $attributes,
        
    //                 // ✅ Thêm type để view phân biệt hiển thị hàng mới, cũ,...
    //                 'cartable_type' => $cartableType,
    //             ];
    //         })->filter(); // loại bỏ null
    //     }
    

    //     // Tính tổng tiền
    //     $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);

    //     // Giảm giá (nếu có)
    //     $appliedCoupon = session('applied_coupon');
    //     $discount = $appliedCoupon['discount'] ?? 0;
    //     $voucherCode = $appliedCoupon['code'] ?? null;

    //     $total = max(0, $subtotal - $discount);
    //     // dd($items);
    //     return view('users.cart.layout.main', compact(
    //         'items', 'subtotal', 'discount', 'total', 'voucherCode'
    //     ));
    // }
    public function index()
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
                    $attributes = $variant->attributeValues->mapWithKeys(function ($attrVal) {
                        return [$attrVal->attribute->name => $attrVal->value];
                    });

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
                        'cartable_type' => $item->cartable_type,
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
                    case ProductVariant::class:
                        $cartable = ProductVariant::with(['product', 'attributeValues.attribute'])
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
                    'cartable_type' => $cartableType,
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

        return view('users.cart.layout.main', compact('items', 'subtotal', 'discount', 'total', 'voucherCode'));
    }

    // thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
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
        if (!isset($cart[$itemKey])) {
            session()->forget(['applied_coupon', 'discount', 'applied_voucher']);
        }

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
                                'message' => "Bạn cần giữ đơn hàng tối thiểu " . number_format($coupon->min_order_amount, 0, ',', '.') . "₫ để tiếp tục sử dụng mã “{$coupon->code}”. Hiện tại đơn hàng còn lại là " . number_format($subtotal, 0, ',', '.') . "₫."
                            ]);
                        } else {
                            session()->forget('applied_coupon');
                            $voucherRemoved = true;
                            $discount = 0;
                        }
                    }
                }

                $item->delete();
            } else {
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
                            session()->forget('applied_coupon');
                            $voucherRemoved = true;
                            $discount = 0;
                        }
                    }
                }

                unset($cart[$itemId]);
                if (empty($cart)) {
                    session()->forget(['cart', 'applied_coupon']);
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

    // public function updateQuantity(Request $request)
    // {
    //     $itemId = $request->input('item_id');
    //     $quantity = $request->input('quantity');

    //     if ($quantity < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Số lượng không hợp lệ.'
    //         ], 422);
    //     }

    //     $newPrice = 0;
    //     $totalBeforeDiscount = 0;
    //     $discount = 0;
    //     $totalAfterDiscount = 0;
    //     $voucherRemoved = false;
    //     $forceUpdate = $request->boolean('force_update', false);

    //     if (auth()->check()) {
    //         // $item = CartItem::with('productVariant')->findOrFail($itemId);
    //         $item = CartItem::with('cartable')->findOrFail($itemId);
    //         \Log::info('CartItem found: ' . $item->id);
    //         $variant = $item->cartable;

    //         // Tính giá sale nếu có
    //         $now = now();
    //         $isOnSale = $variant->sale_price &&
    //             (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
    //             (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
    //         $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

    //         // Kiểm tra tồn kho
    //         $availableStock = \DB::table('inventories')
    //             ->where('product_variant_id', $variant->id)
    //             ->where('inventory_type', 'new')
    //             ->sum('quantity');

    //         if ($quantity > $availableStock) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Sản phẩm chỉ còn {$availableStock} sản phẩm trong kho.",
    //             ], 422);
    //         }

    //         // Tính lại tổng giá trước giảm giá
    //         $cart = auth()->user()?->cart()->with('items')->first();
    //         $items = $cart->items->map(function ($i) use ($itemId, $quantity, $newPrice) {
    //             return (object)[
    //                 'price' => ($i->id == $itemId) ? $newPrice : $i->price,
    //                 'quantity' => ($i->id == $itemId) ? $quantity : $i->quantity,
    //             ];
    //         });
    //         $totalBeforeDiscount = $items->sum(fn($i) => $i->price * $i->quantity);

    //         // Kiểm tra và áp dụng mã giảm giá
    //         $appliedCoupon = session('applied_coupon');
    //         if ($appliedCoupon && isset($appliedCoupon['code'])) {
    //             $coupon = Coupon::where('code', $appliedCoupon['code'])->first();
    //             if ($coupon) {
    //                 if ($coupon->min_order_amount && $totalBeforeDiscount < $coupon->min_order_amount) {
    //                     if (!$forceUpdate) {
    //                         return response()->json([
    //                             'success' => false,
    //                             'need_rollback_quantity' => true,
    //                             'message' => 'Giá trị đơn hàng không đủ điều kiện sử dụng mã giảm giá (tối thiểu ' .
    //                                 number_format($coupon->min_order_amount, 0, ',', '.') . '₫).',
    //                         ]);
    //                     } else {
    //                         session()->forget('applied_coupon');
    //                         $voucherRemoved = true;
    //                     }
    //                 } else {
    //                     $discount = round($coupon->calculateDiscount($totalBeforeDiscount));
    //                 }
    //             }
    //         }

    //         $totalAfterDiscount = max($totalBeforeDiscount - $discount, 0);

    //         // ✅ Cập nhật item
    //         $item->quantity = $quantity;
    //         $item->price = $newPrice;
    //         \Log::info('Saving CartItem id: ' . $item->id . ', quantity: ' . $item->quantity . ', price: ' . $item->price);
    //         $item->save();
    //     } else {
    //         // Guest
    //         $cart = session()->get('cart', []);
    //         if (!isset($cart[$itemId])) {
    //             return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
    //         }

    //         $variant = ProductVariant::find($cart[$itemId]['variant_id']);
    //         if (!$variant) {
    //             return response()->json(['success' => false, 'message' => 'Sản phẩm không hợp lệ.'], 404);
    //         }

    //         $now = now();
    //         $isOnSale = $variant->sale_price &&
    //             (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
    //             (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
    //         $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

    //         $cart[$itemId]['quantity'] = $quantity;
    //         $cart[$itemId]['price'] = $newPrice;

    //         $totalBeforeDiscount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

    //         $appliedCoupon = session('applied_coupon');
    //         if ($appliedCoupon && isset($appliedCoupon['code'])) {
    //             $coupon = Coupon::where('code', $appliedCoupon['code'])->first();
    //             if ($coupon) {
    //                 if ($coupon->min_order_amount && $totalBeforeDiscount < $coupon->min_order_amount) {
    //                     if (!$forceUpdate) {
    //                         return response()->json([
    //                             'success' => false,
    //                             'need_rollback_quantity' => true,
    //                             'message' => 'Giá trị đơn hàng không đủ điều kiện sử dụng mã giảm giá (tối thiểu ' .
    //                                 number_format($coupon->min_order_amount, 0, ',', '.') . '₫).',
    //                         ]);
    //                     } else {
    //                         session()->forget('applied_coupon');
    //                         $voucherRemoved = true;
    //                     }
    //                 } else {
    //                     $discount = round($coupon->calculateDiscount($totalBeforeDiscount));
    //                 }
    //             }
    //         }

    //         $totalAfterDiscount = max($totalBeforeDiscount - $discount, 0);
    //         session()->put('cart', $cart);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'subtotal_before_dc' => number_format($totalBeforeDiscount, 0, ',', '.') . '₫',
    //         'discount' => $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫',
    //         'total_after_dc' => number_format($totalAfterDiscount, 0, ',', '.') . '₫',
    //         'voucher_removed' => $voucherRemoved,
    //     ]);
    // }
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

    $newPrice = 0;
    $totalBeforeDiscount = 0;
    $discount = 0;
    $totalAfterDiscount = 0;
    $voucherRemoved = false;
    $forceUpdate = $request->boolean('force_update', false);

    if (auth()->check()) {
        $item = CartItem::with('cartable')->findOrFail($itemId);
        \Log::info('CartItem found: ' . $item->id);
        $variant = $item->cartable;

        $now = now();
        $isOnSale = $variant->sale_price &&
            (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
            (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

        // Kiểm tra tồn kho sử dụng bảng product_inventories qua model
        $availableStock = \App\Models\ProductInventory::where('product_variant_id', $variant->id)
            ->where('inventory_type', 'new')
            ->sum('quantity');

        if ($quantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm chỉ còn {$availableStock} sản phẩm trong kho.",
            ], 422);
        }

        // Tính lại tổng giá trước giảm giá
        $cart = auth()->user()?->cart()->with('items')->first();
        $items = $cart->items->map(function ($i) use ($itemId, $quantity, $newPrice) {
            return (object)[
                'price' => ($i->id == $itemId) ? $newPrice : $i->price,
                'quantity' => ($i->id == $itemId) ? $quantity : $i->quantity,
            ];
        });
        $totalBeforeDiscount = $items->sum(fn($i) => $i->price * $i->quantity);

        // Kiểm tra và áp dụng mã giảm giá
        $appliedCoupon = session('applied_coupon');
        if ($appliedCoupon && isset($appliedCoupon['code'])) {
            $coupon = Coupon::where('code', $appliedCoupon['code'])->first();
            if ($coupon) {
                if ($coupon->min_order_amount && $totalBeforeDiscount < $coupon->min_order_amount) {
                    if (!$forceUpdate) {
                        return response()->json([
                            'success' => false,
                            'need_rollback_quantity' => true,
                            'message' => 'Giá trị đơn hàng không đủ điều kiện sử dụng mã giảm giá (tối thiểu ' .
                                number_format($coupon->min_order_amount, 0, ',', '.') . '₫).',
                        ]);
                    } else {
                        session()->forget('applied_coupon');
                        $voucherRemoved = true;
                    }
                } else {
                    $discount = round($coupon->calculateDiscount($totalBeforeDiscount));
                }
            }
        }

        $totalAfterDiscount = max($totalBeforeDiscount - $discount, 0);

        // Cập nhật số lượng và giá mới cho CartItem
        $item->quantity = $quantity;
        $item->price = $newPrice;
        \Log::info('Saving CartItem id: ' . $item->id . ', quantity: ' . $item->quantity . ', price: ' . $item->price);
        $item->save();

    } else {
        // Guest
        $cart = session()->get('cart', []);
        if (!isset($cart[$itemId])) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $variant = \App\Models\ProductVariant::find($cart[$itemId]['variant_id']);
        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không hợp lệ.'], 404);
        }

        $now = now();
        $isOnSale = $variant->sale_price &&
            (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
            (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $newPrice = $isOnSale ? $variant->sale_price : $variant->price;

        // Cập nhật số lượng và giá trong session cart
        $cart[$itemId]['quantity'] = $quantity;
        $cart[$itemId]['price'] = $newPrice;

        $totalBeforeDiscount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        $appliedCoupon = session('applied_coupon');
        if ($appliedCoupon && isset($appliedCoupon['code'])) {
            $coupon = Coupon::where('code', $appliedCoupon['code'])->first();
            if ($coupon) {
                if ($coupon->min_order_amount && $totalBeforeDiscount < $coupon->min_order_amount) {
                    if (!$forceUpdate) {
                        return response()->json([
                            'success' => false,
                            'need_rollback_quantity' => true,
                            'message' => 'Giá trị đơn hàng không đủ điều kiện sử dụng mã giảm giá (tối thiểu ' .
                                number_format($coupon->min_order_amount, 0, ',', '.') . '₫).',
                        ]);
                    } else {
                        session()->forget('applied_coupon');
                        $voucherRemoved = true;
                    }
                } else {
                    $discount = round($coupon->calculateDiscount($totalBeforeDiscount));
                }
            }
        }

        $totalAfterDiscount = max($totalBeforeDiscount - $discount, 0);
        session()->put('cart', $cart);
    }

    return response()->json([
        'success' => true,
        'subtotal_before_dc' => number_format($totalBeforeDiscount, 0, ',', '.') . '₫',
        'discount' => $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫',
        'total_after_dc' => number_format($totalAfterDiscount, 0, ',', '.') . '₫',
        'voucher_removed' => $voucherRemoved,
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
                    ->where('cartable_type', \App\Models\ProductVariant::class)
                    ->where('cartable_id', $variant->id)
                    ->first();
                if ($dbItem) {
                    $quantityInCart = $dbItem->quantity;
                }
            }

            $totalRequested = $quantityInCart + $item['quantity'];

            // Kiểm kho nếu quản lý kho
            if ($variant->manage_stock) {
                $availableStock = ProductInventory::where('product_variant_id', $variant->id)
                    ->where('inventory_type', 'new')
                    ->sum('quantity');
                
                if ($totalRequested > $availableStock) {
                    $remaining = max(0, $availableStock - $quantityInCart);

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
