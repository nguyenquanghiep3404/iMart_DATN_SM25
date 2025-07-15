<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductInventory;
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

        return [
            'product_id' => $product->id,
            'sku' => $skuPrefix . '-' . $this->faker->unique()->numerify('###') . Str::upper(Str::random(2)),
            'price' => $price,
            'sale_price' => $salePrice,
            'sale_price_starts_at' => $salePrice ? $this->faker->dateTimeBetween('-2 weeks', '+2 weeks') : null,
            'sale_price_ends_at' => $salePrice ? $this->faker->dateTimeBetween('+3 weeks', '+2 months') : null,
            // XÓA BỎ 'stock_quantity' khỏi đây.
            'manage_stock' => true,
            'stock_status' => 'in_stock', // Sẽ được cập nhật lại ở dưới
            'weight' => $this->faker->optional()->randomFloat(3, 0.050, 3.500),
            'dimensions_length' => $this->faker->optional()->randomFloat(1, 5, 50),
            'dimensions_width' => $this->faker->optional()->randomFloat(1, 5, 30),
            'dimensions_height' => $this->faker->optional()->randomFloat(1, 0.5, 10),
            'is_default' => false,
            'status' => 'active',
        ];
    }

    /**
     * Cấu hình factory để tự động tạo tồn kho sau khi biến thể được tạo.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProductVariant $variant) {
            // 1. Luôn tạo tồn kho cho hàng "mới"
            ProductInventory::factory()->create([
                'product_variant_id' => $variant->id,
            ]);

            // 2. Thỉnh thoảng tạo thêm tồn kho cho hàng "mở hộp"
            if ($this->faker->boolean(30)) { // 30% cơ hội
                ProductInventory::factory()->openBox()->create([
                    'product_variant_id' => $variant->id,
                ]);
            }

            // 3. Cập nhật lại `stock_status` dựa trên tổng tồn kho có thể bán
            $sellableStock = $variant->getSellableStockAttribute(); // Dùng hàm bạn đã tạo trong Model

            $status = 'out_of_stock';
            if ($sellableStock > 10) {
                $status = 'in_stock';
            } elseif ($sellableStock > 0) {
                $status = 'on_backorder'; // Hoặc 'low_stock' tùy bạn định nghĩa
            }

            $variant->stock_status = $status;
            $variant->save();
        });
    }
}