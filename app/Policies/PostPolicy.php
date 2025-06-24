<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Ai cũng có thể xem danh sách bài viết nếu đã đăng nhập
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ai cũng có thể xem chi tiết bài viết
     */
    public function view(User $user, Post $post): bool
    {
        return true;
    }

    /**
     * Nhân viên content hoặc admin đều có thể tạo bài viết
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'content']);
    }

    /**
     * Chỉ admin hoặc chính tác giả mới được update
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === 'admin';
    }

    /**
     * Chỉ admin hoặc chính tác giả mới được xóa
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === 'admin';
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->role === 'admin';
    }
}
