<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfShipper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu người dùng đã đăng nhập VÀ có vai trò là 'shipper'
        if (Auth::check() && $request->user()->hasRole('shipper')) {

            // Nếu đúng, lập tức chuyển hướng họ về dashboard của shipper
            // Ngăn họ đi tiếp vào trang của người dùng
            return redirect()->route('shipper.dashboard');
        }

        // Nếu không phải shipper (hoặc chưa đăng nhập), cho phép request đi tiếp
        return $next($request);
    }
}
