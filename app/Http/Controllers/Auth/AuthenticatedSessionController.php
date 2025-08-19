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

                    // ✅ Kiểm tra tồn kho khả dụng (chỉ inventory_type = 'new')
                    $availableStock = $variant->inventories()
                        ->where('inventory_type', 'new')
                        ->selectRaw('SUM(quantity - quantity_committed) as available_stock')
                        ->value('available_stock');

                    $availableStock = $availableStock ?? 0;
                    Log::info("Checking stock for variant ID {$variantId}: availableStock = {$availableStock}");

                    // Nếu hết hàng => bỏ qua + xóa voucher
                    if ($availableStock < 1) {
                        $voucherKeys = ['voucher_code', 'applied_coupon', 'applied_voucher', 'discount'];
                        foreach ($voucherKeys as $key) {
                            if (session()->has($key)) {
                                session()->forget($key);
                            }
                        }
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

                    // Nếu vượt quá tồn kho khả dụng => xóa voucher
                    if ($totalRequested > $availableStock) {
                        $voucherKeys = ['voucher_code', 'applied_coupon', 'applied_voucher', 'discount'];
                        foreach ($voucherKeys as $key) {
                            if (session()->has($key)) {
                                session()->forget($key);
                            }
                        }
                    }

                    $finalQuantity = min($totalRequested, $availableStock);

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
public function logoutGuest(Request $request)
{
    Auth::guard('web')->logout();

    // $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/login'); // Hoặc redirect()->route('login')
}

}
