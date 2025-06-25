<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_categories');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('browse_categories');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_categories');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('edit_categories');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('delete_categories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }
}
