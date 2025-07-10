<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
   public function before(User $user, $ability) {
        if ($user->hasRole('admin')) return true;
    }
    public function viewAny(User $user) {
        return $user->hasPermissionTo('browse_users');
    }
    public function create(User $user) {
        return $user->hasPermissionTo('add_users');
    }
    public function update(User $user, User $model) {
        return $user->hasPermissionTo('edit_users');
    }
   /**
     * Quyền xóa người dùng.
     */
    public function delete(User $currentUser, User $userToDelete): bool
    {
        // 1. Không cho phép người dùng tự xóa chính mình
        if ($currentUser->id === $userToDelete->id) {
            return false;
        }

        // 2. KHÔNG CHO PHÉP XÓA người dùng có vai trò 'admin'
        if ($userToDelete->hasRole('admin')) {
            return false;
        }

        // 3. Kiểm tra permission như bình thường
        return $currentUser->hasPermissionTo('delete_users');
    }
/**
     * Quyền khôi phục người dùng đã bị xóa mềm.
     */
    public function restore(User $user, User $model): bool
    {
        // Thường người có quyền sửa sẽ có quyền khôi phục
        return $user->hasPermissionTo('edit_users');
    }

    /**
     * Quyền xóa vĩnh viễn người dùng.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Thường chỉ người có quyền xóa mới được xóa vĩnh viễn
        return $user->hasPermissionTo('delete_users');
    }
}
