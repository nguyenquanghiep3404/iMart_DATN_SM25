<?php

namespace Database\Factories;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $actions = ['browse', 'read', 'edit', 'add', 'delete', 'manage'];
        $resources = ['users', 'products', 'categories', 'orders', 'posts', 'banners', 'coupons', 'settings', 'attributes', 'reviews'];
        $action = $this->faker->randomElement($actions);
        $resource = $this->faker->randomElement($resources);
        $name = $action . '_' . $resource;

        return [
            'name' => $name,
            'description' => 'Allow to ' . str_replace('_', ' ', $name),
        ];
    }
}
