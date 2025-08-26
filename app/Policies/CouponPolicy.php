<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    // Admin có toàn quyền
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    // Quyền xem danh sách
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_coupons');
    }

    // Quyền xem chi tiết
    public function view(User $user, Coupon $coupon): bool
    {
        return $user->hasPermissionTo('browse_coupons');
    }

    // Quyền tạo mới
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_coupons');
    }

    // Quyền cập nhật
    public function update(User $user, Coupon $coupon): bool
    {
        return $user->hasPermissionTo('edit_coupons');
    }

    // Quyền xóa
    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->hasPermissionTo('delete_coupons');
    }
}
