<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $sortField = in_array($request->sort, [
            'id',
            'name',
            'order',
            'status',
            'description',
            'parent'
        ]) ? $request->sort : 'id';
        $sortDirection = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query = Category::select('categories.*')
            ->with(['parent' => fn($q) => $q->select('id', 'name')]);
        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($sortField === 'parent') {
            $query->leftJoin('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
                ->orderBy('parent_categories.name', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        if ($sortField !== 'id') {
            $query->orderBy('id', 'desc');
        }
        $categories = $query->paginate(10)->withQueryString();
        return view('admin.category.index', compact('categories', 'sortField', 'sortDirection'));
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
        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được tạo thành công.');
    }

    public function show(Category $category)
    {
        $category->load('parent', 'children')->loadCount('products');
        return view('admin.category.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->whereNot('id', $category->id)
            ->orderBy('name', "asc")
            ->pluck('name', 'id');
        return view('admin.category.edit', compact('category', 'parents'));
    }

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
        $page = request('page', 1);
        $perPage = request('per_page', 10);
        $children = $category->children()->pluck('name');
        if ($children->isNotEmpty()) {
            return back()
                ->with('error', 'Không thể xóa vì có danh mục con: ' . $children->implode(', '));
        }
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            return back()->with('error', "Không thể xóa vì có {$productCount} sản phẩm liên kết.");
        }
        $category->delete();
        $total = Category::count();
        $maxPage = max(ceil($total / $perPage), 1);
        $page = min($page, $maxPage);
        return redirect()->route('admin.categories.index', [
            'page' => $page,
            'per_page' => $perPage
        ])->with('success', 'Danh mục đã được xóa thành công.');
    }
}
