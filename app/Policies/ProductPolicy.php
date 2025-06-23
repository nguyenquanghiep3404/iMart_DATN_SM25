<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
        return $user->hasPermissionTo('browse_products');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('browse_products');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('edit_products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('delete_products');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
