<?php

namespace App\Policies;

use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributeValuePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Admin có toàn quyền
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('browse_attribute_values');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttributeValue $attributeValue): bool
    {
        return $user->hasPermissionTo('read_attribute_values');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('add_attribute_values');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttributeValue $attributeValue): bool
    {
        return $user->hasPermissionTo('edit_attribute_values');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttributeValue $attributeValue): bool
    {
        return $user->hasPermissionTo('delete_attribute_values');
    }
}
