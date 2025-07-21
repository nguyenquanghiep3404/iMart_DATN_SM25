<?php
namespace Database\Factories;
use App\Models\ProductInventory;
use App\Models\Stocktake;
use Illuminate\Database\Eloquent\Factories\Factory;

class StocktakeItemFactory extends Factory
{
    public function definition(): array
    {
        $inventory = ProductInventory::query()->inRandomOrder()->first();
        $counted = $inventory->quantity + $this->faker->numberBetween(-2, 1); // Giả lập chênh lệch
        return [
            'stocktake_id' => Stocktake::factory(),
            'product_variant_id' => $inventory->product_variant_id,
            'inventory_type' => $inventory->inventory_type,
            'system_quantity' => $inventory->quantity,
            'counted_quantity' => max(0, $counted), // Đảm bảo không âm
        ];
    }
}