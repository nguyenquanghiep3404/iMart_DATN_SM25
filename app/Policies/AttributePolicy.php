<?php

namespace App\Policies;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
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
        return $user->hasPermissionTo('browse_attributes');
    }

    public function view(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('browse_attributes');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_attributes');
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('edit_attributes');
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('delete_attributes');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attribute $attribute): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attribute $attribute): bool
    {
        return false;
    }
}
