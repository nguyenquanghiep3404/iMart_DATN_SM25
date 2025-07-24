<?php

namespace Database\Factories;

use App\Models\ProductInventory;
use App\Models\StoreLocation; // Thêm dòng này
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductInventoryFactory extends Factory
{
    protected $model = ProductInventory::class;

    public function definition(): array
    {
        // Lấy một cửa hàng ngẫu nhiên để gán tồn kho.
        // Nếu chưa có cửa hàng nào, tự động tạo một cửa hàng "Kho Online".
        $storeLocation = StoreLocation::query()->inRandomOrder()->first() 
            ?? StoreLocation::factory()->create(['name' => 'Kho Online Chính']);

        return [
            'store_location_id' => $storeLocation->id,
            
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
