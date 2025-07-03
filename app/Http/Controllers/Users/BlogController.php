<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTag;

class BlogController extends Controller
{
    public function show($slug)
{
    $post = Post::with(['coverImage', 'category', 'tags'])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

    // Tăng lượt xem
    $post->increment('view_count');

    $relatedPosts = Post::where('id', '!=', $post->id)
        ->where('status', 'published')
        ->latest()
        ->take(3)
        ->get();

    $featuredPosts = Post::where('status', 'published')
        ->where('id', '!=', $post->id)
        ->orderBy('view_count', 'desc')
        ->take(3)
        ->get();

    $allTags = PostTag::latest()->get();

    return view('users.blogs.show', compact(
        'post',
        'relatedPosts',
        'featuredPosts',
        'allTags'
    ));
}


    public function tag($slug)
    {
        $tag = PostTag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->with('coverImage')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->paginate(6);

        return view('users.blogs.tag', compact('tag', 'posts'));
    }
}
