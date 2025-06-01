<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $product = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();
        $skuPrefix = $product->sku_prefix ?? strtoupper(Str::random(3));

        $price = $this->faker->numberBetween(200, 8000) * 10000;
        $salePrice = $this->faker->optional(0.4, null)->numberBetween(150, ($price / 10000) - 50) * 10000;
        $stockQuantity = $this->faker->numberBetween(0, 250);

        return [
            'product_id' => $product->id,
            'sku' => $skuPrefix . '-' . $this->faker->unique()->numerify('###') . Str::upper(Str::random(2)),
            'price' => $price,
            'sale_price' => $salePrice,
            'sale_price_starts_at' => $salePrice ? $this->faker->dateTimeBetween('-2 weeks', '+2 weeks') : null,
            'sale_price_ends_at' => $salePrice ? $this->faker->dateTimeBetween('+3 weeks', '+2 months') : null,
            'stock_quantity' => $stockQuantity,
            'manage_stock' => true,
            'stock_status' => $stockQuantity > 0 ? ($stockQuantity < 10 ? 'on_backorder' : 'in_stock') : 'out_of_stock',
            'weight' => $this->faker->optional()->randomFloat(3, 0.050, 3.500),
            'dimensions_length' => $this->faker->optional()->randomFloat(1, 5, 50),
            'dimensions_width' => $this->faker->optional()->randomFloat(1, 5, 30),
            'dimensions_height' => $this->faker->optional()->randomFloat(1, 0.5, 10),
            'is_default' => false,
            'status' => 'active',
        ];
    }
}
