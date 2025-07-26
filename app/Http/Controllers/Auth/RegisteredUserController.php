<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Notifications\NewUserRegistered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^0[0-9]{9}$/', 'unique:users,phone_number'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
            ],
        ], [
            // Tên
            'name.required' => 'Vui lòng nhập họ tên',
            'name.max' => 'Tên không được vượt quá 255 ký tự',

            // SĐT
            'phone_number.required' => 'Vui lòng nhập số điện thoại',
            'phone_number.regex' => 'Số điện thoại không hợp lệ',
            'phone_number.unique' => 'Số điện thoại đã được sử dụng.',

            // Email
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã tồn tại',

            // Mật khẩu
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải ít nhất 8 ký tự',
            'password.mixed' => 'Mật khẩu phải chứa ít nhất một chữ hoa và một chữ thường',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một chữ số',
        ]);


        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $admin = User::find(1); // hoặc lấy theo vai trò
        $admin->notify(new NewUserRegistered($user));
        $user->roles()->attach(2);
        event(new Registered($user));
        Auth::login($user);
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
        
                // Kiểm tra biến thể còn tồn tại
                $variantExists = \App\Models\ProductVariant::where('id', $variantId)->exists();
                if (!$variantExists) {
                    continue;
                }
        
                $existingItem = \App\Models\CartItem::where('cart_id', $cart->id)
                    ->where('cartable_id', $variantId)
                    ->where('cartable_type', \App\Models\ProductVariant::class)
                    ->first();
        
                if ($existingItem) {
                    $existingItem->quantity += $quantity;
                    $existingItem->save();
                } else {
                    \App\Models\CartItem::create([
                        'cart_id' => $cart->id,
                        'cartable_id' => $variantId,
                        'cartable_type' => \App\Models\ProductVariant::class,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }
            }
        
            session()->forget('cart');
        }
        return redirect(route('users.home', absolute: false));
    }
}
