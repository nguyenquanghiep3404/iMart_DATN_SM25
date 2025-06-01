<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\UploadedFile;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Post::truncate(); // Cẩn thận
        // PostCategory::truncate(); // Cẩn thận
        // PostTag::truncate(); // Cẩn thận
        // DB::table('post_post_tag')->truncate(); // Cẩn thận
        // UploadedFile::where('attachable_type', Post::class)->delete();

        PostCategory::factory(5)->create()->each(function ($category) {
            PostCategory::factory(rand(0,3))->create(['parent_id' => $category->id]);
        });

        $tags = PostTag::factory(15)->create();

        Post::factory(40)->create()->each(function ($post) use ($tags) {
            UploadedFile::factory()->attachedTo($post, 'cover_image')->create();
            $post->tags()->attach(
                $tags->random(rand(1, 4))->pluck('id')->toArray()
            );
        });
        $this->command->info('Post Categories, Tags, and Posts seeded successfully!');
    }
}
