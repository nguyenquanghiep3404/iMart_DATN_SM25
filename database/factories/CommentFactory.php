<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User; // Đã import
use App\Models\Post; // Đã import
use App\Models\Review; // Đã import
use Illuminate\Database\Eloquent\Factories\Factory; // Đã import

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'parent_id' => null,
            'content' => $this->faker->paragraph(rand(1,3)),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'spam']),
            'commentable_id' => 1, // Cần được override
            'commentable_type' => Post::class, // Cần được override
        ];
    }

    public function forPost(Post $post, Comment $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'parent_id' => $parent?->id,
        ]);
    }

    public function forReviewReply(Review $review, Comment $parent = null): static
    {
         return $this->state(fn (array $attributes) => [
            'commentable_id' => $review->id,
            'commentable_type' => Review::class,
            'parent_id' => $parent?->id,
        ]);
    }
}