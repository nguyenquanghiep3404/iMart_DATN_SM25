<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostTagController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Tự động áp dụng PostTagPolicy cho tất cả các phương thức CRUD
        $this->authorizeResource(PostTag::class, 'post_tag');
    }
    /**
     * Hiển thị danh sách các thẻ bài viết với tìm kiếm và phân trang.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = PostTag::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $tags = $query->paginate(10);

        return view('admin.post_tags.index', compact('tags'));
    }

    /**
     * Hiển thị form tạo mới thẻ bài viết.
     */
    public function create()
    {
        return view('admin.post_tags.create');
    }

    /**
     * Xử lý lưu thẻ bài viết mới.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:post_tags,name',
        ]);

        // Tạo slug từ name và đảm bảo duy nhất
        $data['slug'] = $this->generateUniqueSlug(Str::slug($data['name']));

        PostTag::create($data);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Đã thêm tag thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa thẻ bài viết.
     */
    public function edit(PostTag $postTag)
    {
        return view('admin.post_tags.edit', compact('postTag'));
    }

    /**
     * Cập nhật thông tin thẻ bài viết.
     */
    public function update(Request $request, PostTag $postTag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:post_tags,name,' . $postTag->id,
        ]);

        // Tạo slug từ name và đảm bảo duy nhất (trừ chính nó)
        $data['slug'] = $this->generateUniqueSlug(Str::slug($data['name']), $postTag->id);

        $postTag->update($data);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Đã cập nhật tag thành công.');
    }

    /**
     * Xóa thẻ bài viết nếu không có bài viết liên quan.
     */
    public function destroy(PostTag $postTag)
    {
        // Nếu có bài viết đang sử dụng tag thì không cho xóa
        if ($postTag->posts()->exists()) {
            return redirect()->route('admin.post-tags.index')
                ->with('error', 'Không thể xóa vì thẻ này đang được sử dụng.');
        }

        $postTag->delete();

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Đã xóa tag thành công.');
    }

    /**
     * Hiển thị chi tiết thẻ bài viết cùng danh sách bài viết liên quan.
     */
    public function show(PostTag $postTag)
    {
        $posts = $postTag->posts()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.post_tags.show', compact('postTag', 'posts'));
    }

    /**
     * Tạo slug duy nhất trong bảng post_tags.
     */
    private function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (
            PostTag::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }
}
