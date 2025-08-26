<?php

namespace App\Policies;

use App\Models\PostTag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostTagPolicy
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
        return $user->hasPermissionTo('browse_post_tags');
    }

    // Quyền xem chi tiết
    public function view(User $user, PostTag $postTag): bool
    {
        return $user->hasPermissionTo('browse_post_tags');
    }

    // Quyền tạo mới
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_post_tags');
    }

    // Quyền cập nhật
    public function update(User $user, PostTag $postTag): bool
    {
        return $user->hasPermissionTo('edit_post_tags');
    }

    // Quyền xóa
    public function delete(User $user, PostTag $postTag): bool
    {
        return $user->hasPermissionTo('delete_post_tags');
    }
}
