<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    /**
     * Xử lý một request đến.
     *
     * Middleware này kiểm tra người dùng có quyền hạn cụ thể hay không.
     * Quyền hạn được thừa hưởng từ các vai trò mà người dùng được gán.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission Tên của quyền hạn cần kiểm tra.
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * Cách dùng trong file routes/web.php:
     * ->middleware('permission:manage_products');
     * ->middleware('permission:view_reports');
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa.
        if (!Auth::check()) {
            abort(401, 'Chưa xác thực.');
        }

        // 2. Lấy đối tượng người dùng.
        $user = $request->user();

        // 3. Sử dụng helper 'hasPermissionTo' đã tạo trong model User.
        // Nếu người dùng không có quyền hạn được yêu cầu, từ chối truy cập.
        if (!$user->hasPermissionTo($permission)) {
            // 403 Forbidden - Đã đăng nhập nhưng không có quyền thực hiện hành động này.
            abort(403, 'BẠN KHÔNG CÓ QUYỀN THỰC HIỆN HÀNH ĐỘNG NÀY.');
        }

        // 4. Nếu có quyền, cho phép request đi tiếp.
        return $next($request);
    }
}