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
        return redirect()->route('categories.index')->with('success', 'Danh mục đã được tạo thành công.');
    }
    public function show() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
