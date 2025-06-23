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
    public function delete(User $user, User $model) {
        return $user->hasPermissionTo('delete_users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
