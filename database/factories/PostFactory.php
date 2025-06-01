<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(rand(5,12));
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(3)),
            'content' => '<p>' . implode('</p><p>', $this->faker->paragraphs(rand(10, 25))) . '</p>',
            'excerpt' => $this->faker->paragraph(3),
            'user_id' => User::query()->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'content_manager']))->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'post_category_id' => PostCategory::query()->inRandomOrder()->first()?->id ?? PostCategory::factory()->create()->id,
            'status' => $this->faker->randomElement(['published', 'draft', 'pending_review']),
            'is_featured' => $this->faker->boolean(15),
            'view_count' => $this->faker->numberBetween(0, 10000),
            'meta_title' => $title,
            'meta_description' => $this->faker->sentence(25),
            'meta_keywords' => implode(', ', $this->faker->words(rand(5,10))),
            'published_at' => $this->faker->optional(0.8)->dateTimeThisYear(),
        ];
    }
}
