<?php

namespace Database\Factories;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(rand(1, 4), true));
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(3)),
            'parent_id' => null,
            'description' => $this->faker->optional(0.7)->paragraph,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'order' => $this->faker->numberBetween(0, 100),
            'meta_title' => $name,
            'meta_description' => $this->faker->optional(0.8)->sentence(20),
            'meta_keywords' => $this->faker->optional(0.6)->words(rand(3, 7), true),
        ];
    }

    public function subCategory(int $parentId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId ?? Category::query()->inRandomOrder()->first()?->id,
        ]);
    }
}
