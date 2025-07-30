<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Thêm use cho Log
use App\Models\Cart; // Thêm use cho Cart model
use App\Models\CartItem; // Thêm use cho CartItem model
use App\Models\ProductVariant; // Thêm use cho ProductVariant model
use App\Models\ProductInventory; // Thêm use cho ProductInventory model
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */

    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     // 1. Xác thực đăng nhập
    //     $request->authenticate();

    //     // 2. Regenerate session (tạo lại session mới)
    //     $request->session()->regenerate();

    //     // 3. Lấy user đăng nhập
    //     $user = Auth::user();

    //     // 4. Xử lý giỏ hàng session nếu có
    //     if (session()->has('cart')) {
    //         $sessionCart = session('cart');

    //         // Bắt đầu transaction để đảm bảo tính nhất quán
    //         DB::beginTransaction();

    //         try {
    //             // 5. Lấy hoặc tạo cart của user
    //             $cart = \App\Models\Cart::firstOrCreate(['user_id' => $user->id]);

    //             // 6. Duyệt qua từng item trong giỏ hàng session
    //             foreach ($sessionCart as $item) {
    //                 // 6.1. Kiểm tra và xác thực variant_id
    //                 $variantId = $item['variant_id'] ?? null;
    //                 if (!$variantId || !is_numeric($variantId)) {
    //                     Log::warning('Cart item missing or invalid variant_id', ['item' => $item]);
    //                     continue;
    //                 }

    //                 // 6.2. Lấy số lượng yêu cầu từ session, mặc định là 1 nếu không có
    //                 $quantityRequested = isset($item['quantity']) && is_numeric($item['quantity']) && $item['quantity'] > 0
    //                     ? (int)$item['quantity'] : 1;

    //                 // 6.3. Lấy giá sản phẩm từ session
    //                 $price = isset($item['price']) && is_numeric($item['price'])
    //                     ? (float)$item['price'] : 0;

    //                 // 6.4. Kiểm tra tồn tại biến thể sản phẩm (product variant)
    //                 $variant = \App\Models\ProductVariant::find($variantId);
    //                 if (!$variant) {
    //                     Log::warning("ProductVariant not found: ID {$variantId}");
    //                     continue;
    //                 }

    //                 // 6.5. Kiểm tra tồn kho từ product_inventories (tổng quantity)
    //                 $totalStock = \App\Models\ProductInventory::where('product_variant_id', $variantId)->sum('quantity');
    //                 if ($totalStock < 1) {
    //                     Log::info("Out of stock variant: ID {$variantId}");
    //                     continue;
    //                 }

    //                 // 6.6. Kiểm tra giỏ hàng đã có sản phẩm này chưa
    //                 $existingItem = \App\Models\CartItem::where('cart_id', $cart->id)
    //                     ->where('cartable_id', $variantId)
    //                     ->where('cartable_type', \App\Models\ProductVariant::class)
    //                     ->lockForUpdate()  // Khóa bản ghi để tránh race condition
    //                     ->first();

    //                 // 6.7. Cập nhật hoặc thêm mới sản phẩm vào giỏ hàng
    //                 if ($existingItem) {
    //                     // Nếu item đã có trong giỏ hàng, cộng thêm số lượng
    //                     $newQuantity = $existingItem->quantity + $quantityRequested;

    //                     // Giới hạn số lượng không vượt quá tồn kho
    //                     $finalQuantity = min($newQuantity, $totalStock);

    //                     // Cập nhật số lượng sản phẩm trong giỏ hàng
    //                     if ($finalQuantity != $existingItem->quantity) {
    //                         $existingItem->quantity = $finalQuantity;
    //                         $existingItem->save();
    //                     }
    //                 } else {
    //                     // Nếu item chưa có trong giỏ hàng, tạo mới với số lượng yêu cầu
    //                     $finalQuantity = min($quantityRequested, $totalStock);  // Giới hạn theo tồn kho

    //                     if ($finalQuantity > 0) {
    //                         \App\Models\CartItem::create([
    //                             'cart_id' => $cart->id,
    //                             'cartable_id' => $variantId,
    //                             'cartable_type' => \App\Models\ProductVariant::class,
    //                             'quantity' => $finalQuantity,
    //                             'price' => $price,
    //                         ]);
    //                     }
    //                 }
    //             }

    //             // 7. Xóa giỏ hàng session sau khi xử lý thành công
    //             session()->forget('cart');

    //             // Commit transaction nếu mọi thứ ổn
    //             DB::commit();

    //         } catch (\Exception $e) {
    //             // Rollback nếu có lỗi xảy ra
    //             DB::rollBack();

    //             // Log lỗi để kiểm tra sau
    //             Log::error('Error while transferring cart from session to DB: ' . $e->getMessage(), [
    //                 'user_id' => $user->id,
    //                 'cart_data' => $sessionCart,
    //             ]);
    //         }
    //     }

    //     // 8. Chuyển hướng theo role của người dùng
    //     if ($user->roles->contains('id', 2)) {
    //         return redirect()->intended(route('users.home'));
    //     }

    //     if ($user->roles->contains('id', 1) || 
    //         $user->roles->contains('id', 4) || 
    //         $user->roles->contains('id', 5) || 
    //         $user->roles->contains('id', 6)) {
    //         return redirect()->intended(route('admin.dashboard'));
    //     }

    //     // Nếu không thuộc các role trên, chuyển hướng đến shipper dashboard
    //     return redirect()->intended(route('shipper.dashboard'));
    // }
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Xác thực đăng nhập
        $request->authenticate();

        // 2. Regenerate session (tạo lại session mới)
        $request->session()->regenerate();

        // 3. Lấy user đăng nhập
        $user = Auth::user();

        // 4. Xử lý giỏ hàng session nếu có
        if (session()->has('cart')) {
            $sessionCart = session('cart');

            // Bắt đầu transaction để đảm bảo tính nhất quán
            DB::beginTransaction();

            try {
                // 5. Lấy hoặc tạo cart của user
                $cart = \App\Models\Cart::firstOrCreate(['user_id' => $user->id]);

                // 6. Duyệt qua từng item trong giỏ hàng session
                foreach ($sessionCart as $item) {
                    $variantId = $item['variant_id'] ?? null;
                    if (!$variantId || !is_numeric($variantId)) {
                        Log::warning('Cart item missing or invalid variant_id', ['item' => $item]);
                        continue;
                    }

                    $quantityRequested = isset($item['quantity']) && is_numeric($item['quantity']) && $item['quantity'] > 0
                        ? (int)$item['quantity'] : 1;

                    $price = isset($item['price']) && is_numeric($item['price'])
                        ? (float)$item['price'] : 0;

                    $variant = \App\Models\ProductVariant::find($variantId);
                    if (!$variant) {
                        Log::warning("ProductVariant not found: ID {$variantId}");
                        continue;
                    }

                    // Kiểm tra tồn kho tổng
                    $totalStock = \App\Models\ProductInventory::where('product_variant_id', $variantId)->sum('quantity');
                    Log::info("Checking stock for variant ID {$variantId}: totalStock = {$totalStock}");

                    if ($totalStock < 1) {
                        Log::info("Out of stock variant: ID {$variantId}");

                        // Hủy voucher nếu có
                        $voucherKeys = ['voucher_code', 'applied_coupon', 'applied_voucher', 'discount'];
                        foreach ($voucherKeys as $key) {
                            if (session()->has($key)) {
                                Log::info("Found voucher session key '{$key}' before removal", [
                                    'variant_id' => $variantId,
                                    'user_id' => $user->id,
                                    'session_value' => session($key),
                                ]);
                                session()->forget($key);
                                Log::info("Voucher session key '{$key}' removed due to out of stock product variant", [
                                    'variant_id' => $variantId,
                                    'user_id' => $user->id,
                                    'session_after_removal' => session($key),
                                ]);
                            }
                        }
                        // Bỏ qua sản phẩm này vì hết hàng
                        continue;
                    }

                    // Kiểm tra giỏ hàng DB đã có sản phẩm này chưa
                    $existingItem = \App\Models\CartItem::where('cart_id', $cart->id)
                        ->where('cartable_id', $variantId)
                        ->where('cartable_type', \App\Models\ProductVariant::class)
                        ->lockForUpdate()
                        ->first();

                    $quantityInDb = $existingItem ? $existingItem->quantity : 0;
                    $totalRequested = $quantityInDb + $quantityRequested;

                    // Nếu tổng số lượng yêu cầu vượt tồn kho thì hủy voucher
                    if ($totalRequested > $totalStock) {
                        Log::info("Total requested quantity {$totalRequested} exceeds stock {$totalStock} for variant {$variantId}, removing voucher.");

                        $voucherKeys = ['voucher_code', 'applied_coupon', 'applied_voucher', 'discount'];
                        foreach ($voucherKeys as $key) {
                            if (session()->has($key)) {
                                Log::info("Found voucher session key '{$key}' before removal", [
                                    'variant_id' => $variantId,
                                    'user_id' => $user->id,
                                    'session_value' => session($key),
                                ]);
                                session()->forget($key);
                                Log::info("Voucher session key '{$key}' removed due to insufficient stock", [
                                    'variant_id' => $variantId,
                                    'user_id' => $user->id,
                                    'session_after_removal' => session($key),
                                ]);
                            }
                        }
                    }

                    $finalQuantity = min($totalRequested, $totalStock);

                    if ($existingItem) {
                        if ($existingItem->quantity != $finalQuantity) {
                            $existingItem->quantity = $finalQuantity;
                            $existingItem->save();
                        }
                    } else {
                        if ($finalQuantity > 0) {
                            \App\Models\CartItem::create([
                                'cart_id' => $cart->id,
                                'cartable_id' => $variantId,
                                'cartable_type' => \App\Models\ProductVariant::class,
                                'quantity' => $finalQuantity,
                                'price' => $price,
                            ]);
                        }
                    }
                }

                // 7. Xóa giỏ hàng session sau khi xử lý thành công
                session()->forget('cart');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error while transferring cart from session to DB: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'cart_data' => $sessionCart,
                ]);
            }
        }

        // 8. Chuyển hướng theo role của người dùng
        if ($user->roles->contains('id', 2)) {
            return redirect()->intended(route('users.home'));
        }

        if ($user->roles->contains('id', 1) || 
            $user->roles->contains('id', 4) || 
            $user->roles->contains('id', 5) || 
            $user->roles->contains('id', 6)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('shipper.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Xóa giỏ hàng trong session khi đăng xuất
        session()->forget('cart');

        // Thực hiện đăng xuất
        Auth::guard('web')->logout();

        // Xóa session và regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
    public function ajaxStore(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $request->session()->regenerate();

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {
            
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() // Lấy thông báo lỗi đầu tiên
            ], 422); // 422 Unprocessable Entity
        }
    }

}
