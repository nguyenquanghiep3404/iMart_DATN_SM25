<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use App\Rules\NotDescendantOf;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostCategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Tự động áp dụng PostCategoryPolicy cho tất cả các phương thức CRUD
        $this->authorizeResource(PostCategory::class, 'post_category');
    }
    // Hiển thị danh sách danh mục
    public function index(Request $request)
    {
        $query = PostCategory::with('children');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                  ->orWhere('slug', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%");
            });
        } else {
            $query->whereNull('parent_id');
        }

        $categories_post = $query->orderBy('name')->paginate(10)->appends($request->all());

        return view('admin.post_categories.index', compact('categories_post'));
    }

    // Hiển thị form thêm danh mục
    public function create()
    {
        $categories = PostCategory::whereNull('parent_id')->get();
        return view('admin.post_categories.add', compact('categories'));
    }

    public function destroy($id)
    {
        $category = PostCategory::findOrFail($id);

        // Nếu bạn muốn kiểm tra xem danh mục cha có con không,
        // để ngăn chặn xóa danh mục cha có con, có thể làm thêm:
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories_post.index')
                ->with('error', 'Danh mục này có danh mục con, không thể xóa!');
        }

        $category->delete();

        return redirect()->route('admin.categories_post.index')
            ->with('success', 'Xóa danh mục bài viết thành công!');
    }
    // Lưu danh mục mới
    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'parent_id' => 'nullable|exists:post_categories,id',
            'name' => 'required_if:parent_id,|string|max:255|unique:post_categories,name',
            'slug' => 'nullable|string|unique:post_categories,slug',
            'description' => 'nullable|string',
            'children' => 'array',
            'children.*' => 'nullable|string|max:255',
        ], [
            'name.required_if' => 'Tên danh mục là bắt buộc khi không chọn danh mục cha.',
            'name.unique' => 'Tên danh mục đã tồn tại, vui lòng chọn tên khác.',
            'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
        ]);

        // Tạo slug chính
        $slug = $request->slug ?: Str::slug($request->name ?? ''); // Nếu không có name thì slug rỗng
        $originalSlug = $slug;
        $i = 1;

        while ($slug && PostCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        // Tạo danh mục cha hoặc danh mục con
        if ($request->parent_id) {
            // Nếu chọn danh mục cha, thì chỉ tạo danh mục con với parent_id = danh mục cha đã chọn
            $parentId = $request->parent_id;

            // Tạo tất cả danh mục con trong mảng children
            if ($request->children && count(array_filter($request->children)) > 0) {
                foreach ($request->children as $childName) {
                    $childName = trim($childName);
                    if ($childName === '') continue;

                    // Tạo slug cho con
                    $childSlug = Str::slug($childName);
                    $originalChildSlug = $childSlug;
                    $j = 1;
                    while (PostCategory::where('slug', $childSlug)->exists()) {
                        $childSlug = $originalChildSlug . '-' . $j++;
                    }

                    PostCategory::create([
                        'name' => $childName,
                        'slug' => $childSlug,
                        'parent_id' => $parentId,
                        'description' => null,
                    ]);
                }
            }

            // Nếu không có children thì không tạo gì thêm
        } else {
            // Nếu không chọn danh mục cha, tạo danh mục cha mới
            $category = PostCategory::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'parent_id' => null,
            ]);

            // Tạo danh mục con nếu có
            if ($request->children && count(array_filter($request->children)) > 0) {
                foreach ($request->children as $childName) {
                    $childName = trim($childName);
                    if ($childName === '') continue;

                    $childSlug = Str::slug($childName);
                    $originalChildSlug = $childSlug;
                    $j = 1;
                    while (PostCategory::where('slug', $childSlug)->exists()) {
                        $childSlug = $originalChildSlug . '-' . $j++;
                    }

                    PostCategory::create([
                        'name' => $childName,
                        'slug' => $childSlug,
                        'parent_id' => $category->id,
                        'description' => null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.categories_post.index')->with('success', 'Thêm danh mục thành công!');
    }

    public function edit($id)
    {
        $category = PostCategory::findOrFail($id);

        // Lấy danh sách danh mục trừ chính nó để chọn danh mục cha
        $allCategories = PostCategory::where('id', '!=', $id)->get();

        return view('admin.post_categories.edit', [
            'categories_post' => $category,
            'allCategories' => $allCategories
        ]);
    }

    // Cập nhật dữ liệu sau khi submit form
    public function update(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:post_categories,id',
            'name' => 'required_if:parent_id,|string|max:255|unique:post_categories,name',
            'slug' => 'nullable|string|unique:post_categories,slug',
            'description' => 'nullable|string',
            'children' => 'array',
            'children.*' => 'nullable|string|max:255',
        ], [
            'name.required_if' => 'Tên danh mục là bắt buộc khi không chọn danh mục cha.',
        ]);

        $category = PostCategory::findOrFail($id);
        $category->name = $request->name;
        $category->slug = $request->slug ?? \Str::slug($request->name);
        $category->parent_id = $request->parent_id;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('admin.categories_post.index')
                         ->with('success', 'Cập nhật danh mục thành công.');
    }
    public function show($id)
    {
        $category = PostCategory::with(['parent', 'children'])->findOrFail($id);

        return view('admin.post_categories.show', [
            'categories_post' => $category
        ]);
    }
}
