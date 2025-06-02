<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CartItem;
use App\Models\Cart; // Đã import ở CartFactory
use App\Models\ProductVariant;


class CartItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lấy một biến thể sản phẩm ngẫu nhiên đang hoạt động
        $productVariant = ProductVariant::where('status', 'active')
                                        // ->where('stock_quantity', '>', 0) // Có thể thêm điều kiện này nếu muốn đảm bảo còn hàng
                                        ->inRandomOrder()
                                        ->first();

        // Nếu không tìm thấy biến thể sản phẩm, tạo một cái mới (chỉ cho mục đích seeding)
        if (!$productVariant) {
            $productVariant = ProductVariant::factory()->create(['status' => 'active', 'stock_quantity' => $this->faker->numberBetween(5, 50)]);
        }

        $quantity = $this->faker->numberBetween(1, 3);
        $priceAtCart = $productVariant->sale_price ?? $productVariant->price; // Lấy giá khuyến mãi nếu có

        return [
            'cart_id' => Cart::factory(), // Sẽ tạo một Cart mới nếu không được cung cấp cart_id cụ thể khi gọi factory
            'product_variant_id' => $productVariant->id,
            'quantity' => $quantity,
            'price' => $priceAtCart, // Lưu giá tại thời điểm thêm vào giỏ (tùy chọn)
            // created_at và updated_at sẽ được tự động điền
        ];
    }

    /**
     * Gắn cart item này vào một cart cụ thể.
     */
    public function forCart(Cart $cart): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_id' => $cart->id,
        ]);
    }

    /**
     * Gắn cart item này với một product variant cụ thể.
     */
    public function forProductVariant(ProductVariant $productVariant): static
    {
        $priceAtCart = $productVariant->sale_price ?? $productVariant->price;
        return $this->state(fn (array $attributes) => [
            'product_variant_id' => $productVariant->id,
            'price' => $priceAtCart,
        ]);
    }
}
