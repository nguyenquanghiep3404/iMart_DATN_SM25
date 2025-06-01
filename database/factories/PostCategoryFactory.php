<?php

namespace Database\Factories;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostCategoryFactory extends Factory
{
    protected $model = PostCategory::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(rand(2,4), true));
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null,
            'description' => $this->faker->optional()->sentence,
        ];
    }
}
