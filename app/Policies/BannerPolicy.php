<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BannerPolicy
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

    // Quyền xem danh sách banner
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_banners');
    }

    // Quyền xem chi tiết banner (thường dùng chung quyền browse)
    public function view(User $user, Banner $banner): bool
    {
        return $user->hasPermissionTo('browse_banners');
    }

    // Quyền tạo banner mới
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_banners');
    }

    // Quyền cập nhật banner
    public function update(User $user, Banner $banner): bool
    {
        return $user->hasPermissionTo('edit_banners');
    }

    // Quyền xóa banner
    public function delete(User $user, Banner $banner): bool
    {
        return $user->hasPermissionTo('delete_banners');
    }
}
