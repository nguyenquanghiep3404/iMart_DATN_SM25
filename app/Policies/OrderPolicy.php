<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Luôn cho phép admin thực hiện mọi hành động.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    /**
     * Quyền xem danh sách tất cả đơn hàng.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_orders');
    }

    /**
     * Quyền xem chi tiết một đơn hàng.
     * Đây là logic quan trọng:
     * - Admin/nhân viên có quyền 'read_orders' sẽ được xem.
     * - Hoặc, người dùng là chủ của đơn hàng đó cũng được xem.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->hasPermissionTo('read_orders')) {
            return true;
        }
        return $user->id === $order->user_id;
    }

    /**
     * Quyền tạo đơn hàng.
     * Thường không cho phép admin tạo đơn hàng, nên trả về false.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Quyền cập nhật đơn hàng (ví dụ: cập nhật trạng thái).
     */
    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('edit_orders_status');
    }

    /**
     * Quyền xóa/hủy đơn hàng.
     */
    public function delete(User $user, Order $order): bool
    {
        // Ta dùng quyền 'cancel_orders' thay vì 'delete_orders'
        return $user->hasPermissionTo('cancel_orders');
    }
}
