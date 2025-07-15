<?php

namespace Database\Factories;

use App\Models\ProductInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductInventoryFactory extends Factory
{
    protected $model = ProductInventory::class;

    public function definition(): array
    {
        // Mặc định, chúng ta sẽ tạo tồn kho cho hàng mới.
        // Các loại khác sẽ được định nghĩa qua các state.
        return [
            'inventory_type' => 'new',
            'quantity' => $this->faker->numberBetween(50, 250),
        ];
    }

    // State cho hàng mở hộp (đã qua sử dụng)
    public function openBox(): static
    {
        return $this->state(fn (array $attributes) => [
            'inventory_type' => 'open_box',
            'quantity' => $this->faker->numberBetween(1, 10),
        ]);
    }

    // State cho hàng lỗi
    public function defective(): static
    {
        return $this->state(fn (array $attributes) => [
            'inventory_type' => 'defective',
            'quantity' => $this->faker->numberBetween(1, 5),
        ]);
    }
}