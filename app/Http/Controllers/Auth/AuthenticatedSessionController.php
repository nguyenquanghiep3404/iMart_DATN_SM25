<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
        // 1. Xác thực email + password
        $request->authenticate();

        // 2. Regenerate session
        $request->session()->regenerate();

        // 3. Lấy user vừa đăng nhập
        $user = Auth::user();

        // 4. Chuyển hướng theo role:
        //    - Nếu user có role_id = 2 (customer) → chuyển đến /dashboard (user dashboard)
        //    - Nếu user có role_id thuộc [1,4,5] (admin roles) → chuyển đến /admin/dashboard
        if ($user->roles->contains('id', 2)) {
            return redirect()->intended(route('users.home'));
        }

        if ($user->roles->contains('id', 1) ||
            $user->roles->contains('id', 4) ||
            $user->roles->contains('id', 5)
        ) {
            return redirect()->intended(route('admin.admin.dashboard'));
        }

        // 5. Nếu không thuộc nhóm nào trên, dùng redirect mặc định
        return redirect()->intended(route('dashboard'));
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
}
