<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Xử lý một request đến.
     *
     * Middleware này cho phép bạn truyền vào một hoặc nhiều tên vai trò.
     * Request sẽ được cho qua nếu người dùng sở hữu ÍT NHẤT MỘT trong các vai trò đó.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Danh sách các tên vai trò được chấp nhận.
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * Cách dùng trong file routes/web.php:
     * - Kiểm tra một vai trò: ->middleware('role:admin');
     * - Kiểm tra nhiều vai trò: ->middleware('role:admin,content_manager');
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Kiểm tra xem người dùng đã được xác thực (đăng nhập) chưa.
        // `Auth::user()` sẽ trả về null nếu chưa đăng nhập.
        if (!Auth::check()) {
            // Nếu bạn muốn chuyển hướng đến trang login thay vì báo lỗi:
            // return redirect('login');
            abort(401, 'Chưa xác thực.'); // 401 Unauthorized
        }

        // 2. Lấy đối tượng người dùng hiện tại.
        $user = $request->user();

        // 3. Sử dụng helper 'hasAnyRole' trong model User để kiểm tra.
        // Nếu người dùng không có bất kỳ vai trò nào trong danh sách $roles, từ chối truy cập.
        if (!$user->hasAnyRole($roles)) {
            // 403 Forbidden - Đã đăng nhập nhưng không có quyền.
            abort(403, 'TRUY CẬP BỊ TỪ CHỐI.');
        }

        // 4. Nếu tất cả kiểm tra đều qua, cho phép request tiếp tục.
        return $next($request);
    }
}