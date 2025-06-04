<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    //
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('id', 'desc')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(10);
        return view('admin.category.index', compact('categories'));
    }
    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.category.create', compact('parents'));
    }
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được tạo thành công.');
    }
    public function show() {}
    public function edit() {}
    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category->update($data);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được cập nhật thành công.');
    }
    public function destroy(Category $category)
    {
        $currentPage = request('current_page', 1);
        $perPage = request('per_page', 10);
        $children = $category->children()->pluck('name');
        if ($children->isNotEmpty()) {
            return back()->with('error', 'Không thể xóa vì có danh mục con: ' . $children->implode(', '));
        }
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            return back()->with('error', "Không thể xóa vì có {$productCount} sản phẩm liên kết.");
        }
        $category->delete();
        $total = Category::count();
        $maxPage = max(ceil($total / $perPage), 1);
        $currentPage = min($currentPage, $maxPage);
        return redirect()->route('admin.categories.index', [
            'page' => $currentPage,
            'per_page' => $perPage
        ])->with('success', 'Danh mục đã được xóa thành công.');
    }
}
