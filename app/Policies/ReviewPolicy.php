<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
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
        return $user->hasPermissionTo('browse_reviews');
    }

    // Quyền xem chi tiết
    public function view(User $user, Review $review): bool
    {
        return $user->hasPermissionTo('browse_reviews');
    }

    // Admin không tạo review, nên trả về false
    public function create(User $user): bool
    {
        return false;
    }

    // Quyền cập nhật (thay đổi trạng thái)
    public function update(User $user, Review $review): bool
    {
        return $user->hasPermissionTo('edit_reviews_status');
    }

    // Quyền xóa
    public function delete(User $user, Review $review): bool
    {
        return $user->hasPermissionTo('delete_reviews');
    }
}
