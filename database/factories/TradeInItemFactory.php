<?php
namespace Database\Factories;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TradeInItemFactory extends Factory
{
    public function definition(): array {
        $variant = ProductVariant::query()->inRandomOrder()->first() ?? ProductVariant::factory()->create();
        return [
            'product_variant_id' => $variant->id,
            'store_location_id' => StoreLocation::query()->inRandomOrder()->first()?->id ?? StoreLocation::factory()->create()->id,
            'type' => $this->faker->randomElement(['used', 'open_box']),
            'sku' => 'USED-' . Str::upper(Str::random(10)),
            'condition_grade' => $this->faker->randomElement(['99%', '98%', '95%', 'Trầy xước']),
            'condition_description' => 'Máy ' . $this->faker->randomElement(['xước nhẹ ở cạnh', 'cấn nhẹ góc', 'đẹp như mới']) . '. Pin ' . $this->faker->numberBetween(85, 99) . '%.',
            'selling_price' => $variant->price * $this->faker->randomFloat(2, 0.6, 0.8),
            'imei_or_serial' => $this->faker->unique()->numerify('###############'),
            'status' => 'available',
        ];
    }
}