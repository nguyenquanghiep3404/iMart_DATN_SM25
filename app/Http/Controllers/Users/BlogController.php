<?php

namespace App\Http\Controllers\Users;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class BlogController extends Controller
{
    /**
     * Hiển thị danh sách bài viết với phân trang và tìm kiếm.
     */
    public function home(Request $request)
    {
        // 1 bài viết mới nhất
        $latestPost = Post::where('status', 'published')
            ->latest()
            ->first();

        // 3 bài viết tiếp theo (trừ bài mới nhất)
        $nextPosts = Post::where('status', 'published')
            ->when($latestPost, fn($query) => $query->where('id', '!=', $latestPost->id))
            ->latest()
            ->take(3)
            ->get();

        // 4 bài viết được xem nhiều nhất (bao gồm cả những bài ở trên)
        $popularPosts = Post::where('status', 'published')
            ->orderByDesc('view_count')
            ->take(4)
            ->get();

        // Danh mục cha
        $parentCategories = PostCategory::whereNull('parent_id')->get();

        // Bài viết nổi bật
        $featuredPosts = Post::where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('users.blogs.home', compact(
            'latestPost',
            'nextPosts',
            'popularPosts',
            'featuredPosts',
            'parentCategories'
        ));
    }
    /**
     * Hiển thị danh sách bài viết với phân trang và lọc theo danh mục hoặc tag.
     */
    public function index(Request $request)
    {
        // 🟦 Lấy danh sách danh mục cha (parent_id = null)
        $parentCategories = PostCategory::withCount('posts')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Lấy bài viết nổi bật (cho sidebar)
        $featuredPosts = Post::where('is_featured', true)
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        // Lấy danh sách bài viết chính
        $query = Post::with(['coverImage', 'category', 'user'])
            ->where('status', 'published');

        // Biến để truyền tên danh mục (nếu có) ra view
        $currentCategory = null;

        // Nếu có lọc theo category
        if ($request->has('category')) {
            $currentCategory = PostCategory::where('slug', $request->category)->first();

            if ($currentCategory) {
                // Lọc bài viết theo danh mục
                $query->where('post_category_id', $currentCategory->id);
            } else {
                // Nếu không có danh mục hợp lệ, trả về danh sách rỗng
                $posts = collect();
                return view('users.blogs.index', compact('posts', 'parentCategories', 'featuredPosts', 'currentCategory'));
            }
        }

        // Nếu có lọc theo tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Lấy danh sách phân trang
        $posts = $query->latest()->paginate(10);

        return view('users.blogs.index', compact('posts', 'parentCategories', 'featuredPosts', 'currentCategory'));
    }

    /**
     * Hiển thị chi tiết bài viết.
     */
    public function show($slug)
    {
        $post = Post::with(['coverImage', 'category', 'tags', 'user'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedPosts = Post::where('id', '!=', $post->id)
            ->where('status', 'published')
            ->latest()
            ->take(3)
            ->get();

        $featuredPosts = Post::with('coverImage')
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();
        // 🟦 Lấy danh sách danh mục cha (parent_id = null)
        $parentCategories = PostCategory::withCount('posts')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();


        $allTags = PostTag::latest()->get();

        return view('users.blogs.show', compact(
            'post',
            'relatedPosts',
            'featuredPosts',
            'parentCategories',
        ));
    }

    /**
     * Hiển thị bài viết theo tag.
     */
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

    /**
     * Tìm kiếm bài viết và sản phẩm.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        // ======= Tìm sản phẩm =======
        $products = Product::with('category')
            ->where(function ($q2) use ($query) {
                $q2->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->where('status', 'published')
            ->paginate(12);

        // ======= Tìm bài viết liên quan =======
        $posts = Post::with('coverImage')
            ->where('status', 'published')
            ->where(function ($q2) use ($query) {
                $q2->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        return view('users.shop', [
            'products' => $products,
            'posts' => $posts, // truyền bài viết ra view
            'categories' => Category::all(),
            'searchQuery' => $query,
            'suggestedProducts' => [],
            'currentCategory' => null,
        ]);
    }

    /**
     * Tăng lượt xem bài viết.
     */
    public function increaseViews(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->increment('view_count');

        return response()->json(['message' => 'View count increased']);
    }
}
