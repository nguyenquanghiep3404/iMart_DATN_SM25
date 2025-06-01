<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cart;
use App\Models\User;

class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lấy một user ngẫu nhiên có vai trò 'customer' để gán cho giỏ hàng
        // Đảm bảo UserSeeder và RolePermissionSeeder đã chạy trước
        $customer = User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })->inRandomOrder()->first();

        // Nếu không tìm thấy customer, tạo một customer mới
        if (!$customer) {
            $customer = User::factory()->create();
            // Gán vai trò customer cho user mới tạo nếu cần
            // $customerRole = \App\Models\Role::where('name', 'customer')->first();
            // if ($customerRole) {
            //     $customer->roles()->attach($customerRole);
            // }
        }

        return [
            'user_id' => $customer->id,
            // created_at và updated_at sẽ được tự động điền
        ];
    }
}
