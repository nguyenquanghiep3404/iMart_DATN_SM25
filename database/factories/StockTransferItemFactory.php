<?php
namespace Database\Factories;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransferItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stock_transfer_id' => StockTransfer::factory(),
            'product_variant_id' => ProductVariant::query()->inRandomOrder()->first()->id,
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
