<?php

namespace Database\Factories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $productNames = [
            'iPhone', 'iPad', 'MacBook Pro', 'MacBook Air', 'Apple Watch', 'AirPods Pro', 'iMac', 'Mac Mini', 'Apple TV', 'HomePod'
        ];
        $productSuffixes = [
            'Ultra', 'Max', 'Plus', 'SE', 'Series 9', 'M3', 'M2', 'Gen 5', ''
        ];
        $name = $this->faker->randomElement($productNames) . ' ' . $this->faker->randomElement($productSuffixes) . ' ' . $this->faker->colorName() . ' ' . $this->faker->randomDigitNotNull . 'TB';
        $name = Str::title(trim(str_replace('  ', ' ', $name)));

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'category_id' => Category::query()->whereNotNull('parent_id')->inRandomOrder()->first()?->id ?? Category::factory()->create(['parent_id' => Category::factory()->create()->id])->id,
            'description' => '<p>' . implode('</p><p>', $this->faker->paragraphs(rand(4, 8))) . '</p>',
            'short_description' => $this->faker->sentence(15),
            'sku_prefix' => strtoupper(Str::random(4)),
            'type' => $this->faker->randomElement(['simple', 'variable']),
            'status' => $this->faker->randomElement(['published', 'draft', 'pending_review']),
            'is_featured' => $this->faker->boolean(30),
            'meta_title' => 'Mua ' . $name . ' Chính hãng, Giá tốt',
            'meta_description' => 'Đặt mua ' . $name . ' với nhiều ưu đãi hấp dẫn. ' . $this->faker->sentence(10),
            'meta_keywords' => strtolower($name) . ', apple, chính hãng, ' . $this->faker->words(3, true),
            'tags' => implode(',', $this->faker->words(rand(2, 5))),
            'view_count' => $this->faker->numberBetween(50, 15000),
            'warranty_information' => $this->faker->randomElement(['Bảo hành 12 tháng chính hãng.', 'Bảo hành 24 tháng tại cửa hàng.', null]),
            'created_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()?->id,
            'updated_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()?->id,
        ];
    }

    public function simpleProduct(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'simple']);
    }

    public function variableProduct(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'variable']);
    }
}
