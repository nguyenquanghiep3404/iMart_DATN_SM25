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
    //     // 1. Xác thực email + password
    //     $request->authenticate();

    //     // 2. Regenerate session
    //     $request->session()->regenerate();

    //     // 3. Lấy user vừa đăng nhập
    //     $user = Auth::user();

    //     // 4. Chuyển hướng theo role:
    //     //    - Nếu user có role_id = 2 (customer) → chuyển đến /dashboard (user dashboard)
    //     //    - Nếu user có role_id thuộc [1,4,5] (admin roles) → chuyển đến /admin/dashboard
    //     if ($user->roles->contains('id', 2)) {
    //         return redirect()->intended(route('users.home'));
    //     }

    //     if ($user->roles->contains('id', 1) ||
    //         $user->roles->contains('id', 4) ||
    //         $user->roles->contains('id', 6) ||
    //         $user->roles->contains('id', 5)
    //     ) {
    //         return redirect()->intended(route('admin.dashboard'));
    //     }

    //     return redirect()->intended(route('shipper.dashboard'));
    // }
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Xác thực đăng nhập
        $request->authenticate();
    
        // 2. Regenerate session
        $request->session()->regenerate();
    
        // 3. Lấy user đăng nhập
        $user = Auth::user();
    
        // 4. Xử lý giỏ hàng session nếu có
        if (session()->has('cart')) {
            $sessionCart = session('cart');
    
            DB::beginTransaction();
    
            try {
                // Lấy hoặc tạo cart của user
                $cart = \App\Models\Cart::firstOrCreate(['user_id' => $user->id]);
    
                foreach ($sessionCart as $item) {
                    // Validate dữ liệu đầu vào
                    $variantId = $item['variant_id'] ?? null;
                    if (!$variantId || !is_numeric($variantId)) {
                        Log::warning('Cart item missing or invalid variant_id', ['item' => $item]);
                        continue;
                    }
    
                    $quantityRequested = isset($item['quantity']) && is_numeric($item['quantity']) && $item['quantity'] > 0
                        ? (int)$item['quantity'] : 1;
    
                    $price = isset($item['price']) && is_numeric($item['price'])
                        ? (float)$item['price'] : 0;
    
                    // Kiểm tra tồn tại biến thể
                    $variant = \App\Models\ProductVariant::find($variantId);
                    if (!$variant) {
                        Log::warning("ProductVariant not found: ID {$variantId}");
                        continue;
                    }
    
                    // Lấy tồn kho mới nhất từ product_inventories (tổng quantity)
                    $totalStock = \App\Models\ProductInventory::where('product_variant_id', $variantId)->sum('quantity');
                    if ($totalStock < 1) {
                        Log::info("Out of stock variant: ID {$variantId}");
                        continue;
                    }
    
                    // Lấy item đã tồn tại trong giỏ hàng db
                    $existingItem = \App\Models\CartItem::where('cart_id', $cart->id)
                        ->where('cartable_id', $variantId)
                        ->where('cartable_type', \App\Models\ProductVariant::class)
                        ->lockForUpdate()  // khóa để tránh race condition
                        ->first();
    
                    if ($existingItem) {
                        $newQuantity = $existingItem->quantity + $quantityRequested;
                        $finalQuantity = min($newQuantity, $totalStock);
    
                        if ($finalQuantity != $existingItem->quantity) {
                            $existingItem->quantity = $finalQuantity;
                            $existingItem->save();
                        }
                    } else {
                        $finalQuantity = min($quantityRequested, $totalStock);
    
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
    
                // Xóa giỏ hàng session
                session()->forget('cart');
    
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error while transferring cart from session to DB: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'cart_data' => $sessionCart,
                ]);
                // Có thể thêm thông báo lỗi hoặc throw exception tùy yêu cầu
            }
        }
    
        // 5. Chuyển hướng theo role
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
        Auth::guard('web')->logout();

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
