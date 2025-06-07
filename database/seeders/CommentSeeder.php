<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        // Comment::truncate(); // Cẩn thận

        $posts = Post::where('status', 'published')->get();
        $reviews = Review::where('status', 'approved')->get();
        $users = User::where('status', 'active')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No active users to create comments. Skipping CommentSeeder.');
            return;
        }

        foreach ($posts as $post) {
            Comment::factory(rand(0, 5))
                ->forPost($post)
                ->create(['user_id' => $users->random()->id]);
        }

        foreach ($reviews as $review) {
            // Tạo reply cho review
            Comment::factory(rand(0, 2))
                ->forReviewReply($review)
                ->create(['user_id' => $users->random()->id]);
        }
        $this->command->info('Comments seeded successfully!');
    }
}
