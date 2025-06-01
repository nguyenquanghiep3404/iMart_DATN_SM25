<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostTag;
use Illuminate\Support\Str; // Đã import
class PostTagFactory extends Factory
{
    protected $model = PostTag::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->word);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
