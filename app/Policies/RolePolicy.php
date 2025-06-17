<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class RolePolicy
{
    use HandlesAuthorization;
    // Admin có toàn quyền, chạy trước các phương thức khác
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    // Quyền xem danh sách vai trò
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_roles');
    }

    // Quyền xem chi tiết vai trò
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('browse_roles');
    }

    // Quyền tạo vai trò mới
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_roles');
    }

    // Quyền cập nhật vai trò
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('edit_roles');
    }

    // Quyền xóa vai trò
    public function delete(User $user, Role $role): bool
    {
        // Thêm logic để không cho xóa vai trò admin và customer
        if (in_array($role->name, ['admin', 'customer'])) {
            return false;
        }
        return $user->hasPermissionTo('delete_roles');
    }
    public function assignPermissions(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('assign_permissions_to_role');
    }
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
