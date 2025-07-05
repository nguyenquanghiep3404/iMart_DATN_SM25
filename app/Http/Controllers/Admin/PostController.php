<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\User;
use App\Models\PostTag;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Hàm kiểm tra quyền truy cập bài viết
    protected function canAccessPost(Post $post): bool
    {
        return auth()->user()->hasRole('admin') || $post->user_id === auth()->id();
    }

    public function index(Request $request)
    {
        $categories = PostCategory::all();
        $tags = PostTag::all();

        $users = User::select('id', 'name')
            ->whereHas('roles', function ($query) {
                $query->whereIn('id', [1, 4]);
            })
            ->orderBy('name')
            ->get();

        $query = Post::with(['category', 'tags', 'coverImage', 'user']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('content', 'like', "%$search%")
                    ->orWhereHas('tags', fn($q) => $q->where('name', 'like', "%$search%"));
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('post_category_id', $categoryId);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $posts = $query->latest()->paginate(10);

        return view('admin.posts.index', compact('posts', 'categories', 'tags', 'users'));
    }

    public function create()
    {
        $categories = PostCategory::all();
        $tags = PostTag::all();
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/|unique:posts,slug',
            'content' => 'required|string|max:100000',
            'excerpt' => 'nullable|string|max:500',
            'post_category_id' => 'required|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'post_cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:published,draft,pending_review',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);
        
        $data['slug'] = Str::slug($request->input('slug'));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['user_id'] = auth()->id();
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        DB::beginTransaction();
        try {
            Storage::makeDirectory('uploads/posts', 0755, true, 'public');
            $post = Post::create($data);
            $post->tags()->sync($data['tags'] ?? []);

            if ($request->hasFile('post_cover_image')) {
                $file = $request->file('post_cover_image');
                $path = $file->store('uploads/posts', 'public');

                UploadedFile::create([
                    'attachable_id' => $post->id,
                    'attachable_type' => Post::class,
                    'path' => $path,
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => 'public',
                    'type' => 'post_cover_image',
                    'user_id' => $data['user_id'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating post: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tạo bài viết: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Post $post)
    {
        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.index')->with('error', 'Bạn không được phép sửa bài viết này.');
        }

        $categories = PostCategory::all();
        $tags = PostTag::all();
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.index')->with('error', 'Bạn không được phép sửa bài viết này.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/|unique:posts,slug,' . $post->id,
            'content' => 'required|string|max:100000',
            'excerpt' => 'nullable|string|max:500',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'post_cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:published,draft,pending_review',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        DB::beginTransaction();
        try {
            $post->update($data);
            $post->tags()->sync($data['tags'] ?? []);

            if ($request->hasFile('post_cover_image')) {
                if ($post->coverImage && Storage::disk($post->coverImage->disk)->exists($post->coverImage->path)) {
                    Storage::disk($post->coverImage->disk)->delete($post->coverImage->path);
                    $post->coverImage->delete();
                }

                $file = $request->file('post_cover_image');
                $path = $file->store('uploads/posts', 'public');

                UploadedFile::create([
                    'attachable_id' => $post->id,
                    'attachable_type' => Post::class,
                    'path' => $path,
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => 'public',
                    'type' => 'post_cover_image',
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi khi cập nhật bài viết: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Post $post)
    {
        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.index')->with('error', 'Bạn không được phép xem bài viết này.');
        }

        $post->load(['category', 'tags', 'coverImage', 'user']);
        return view('admin.posts.show', compact('post'));
    }

    public function preview($id)
    {
        $post = Post::with(['category', 'tags', 'coverImage', 'user'])->findOrFail($id);

        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.index')->with('error', 'Bạn không được phép xem trước bài viết này.');
        }

        return view('admin.posts.preview', compact('post'));
    }

    public function destroy(Post $post)
    {
        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.index')->with('error', 'Bạn không được phép xoá bài viết này.');
        }

        try {
            $post->delete();
            return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được chuyển vào thùng rác.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xoá bài viết: ' . $e->getMessage());
        }
    }

    public function trashed()
    {
        $posts = Post::onlyTrashed()->with(['category', 'user', 'coverImage'])->latest()->paginate(10);
        return view('admin.posts.trashed', compact('posts'));
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.trashed')->with('error', 'Bạn không được phép khôi phục bài viết này.');
        }

        try {
            $post->restore();
            return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được khôi phục.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi khôi phục bài viết: ' . $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        if (!$this->canAccessPost($post)) {
            return redirect()->route('admin.posts.trashed')->with('error', 'Bạn không được phép xoá vĩnh viễn bài viết này.');
        }

        try {
            if ($post->coverImage && Storage::disk($post->coverImage->disk)->exists($post->coverImage->path)) {
                Storage::disk($post->coverImage->disk)->delete($post->coverImage->path);
                $post->coverImage->delete();
            }

            $post->forceDelete();
            return redirect()->route('admin.posts.trashed')->with('success', 'Bài viết đã bị xoá vĩnh viễn.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xoá vĩnh viễn: ' . $e->getMessage());
        }
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/posts', $filename, 'public');

            return response()->json([
                'uploaded' => true,
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => ['message' => 'Không thể tải ảnh lên.']
        ]);
    }
}
