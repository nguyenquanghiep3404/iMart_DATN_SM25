<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
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
        // 1. Xác thực email + password
        $request->authenticate();
    
        // 2. Regenerate session
        $request->session()->regenerate();
    
        // 3. Lấy user vừa đăng nhập
        $user = Auth::user();
    
        // 4. Nếu có giỏ hàng trong session thì chuyển vào database
        if (session()->has('cart')) {
            $sessionCart = session('cart');
            $cart = \App\Models\Cart::firstOrCreate(['user_id' => $user->id]);
        
            foreach ($sessionCart as $item) {
                $variantId = $item['variant_id'] ?? null;
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                $price = isset($item['price']) ? (float)$item['price'] : 0;
        
                if (!$variantId) {
                    continue;
                }
        
                $existingItem = \App\Models\CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $variantId)
                    ->first();
        
                if ($existingItem) {
                    $existingItem->quantity += $quantity;
                    $existingItem->save();
                } else {
                    \App\Models\CartItem::create([
                        'cart_id' => $cart->id,
                        'product_variant_id' => $variantId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }
            }
        
            session()->forget('cart');
        }
        
    
        // 5. Chuyển hướng theo role:
        if ($user->roles->contains('id', 2)) {
            return redirect()->intended(route('users.home'));
        }
    
        if ($user->roles->contains('id', 1) ||
            $user->roles->contains('id', 4) ||
            $user->roles->contains('id', 6) ||
            $user->roles->contains('id', 5)
        ) {
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
