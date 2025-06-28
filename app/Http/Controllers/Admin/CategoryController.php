<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    // Phân quyền
     public function __construct()
    {
        // Tự động phân quyền cho tất cả các phương thức CRUD
        $this->authorizeResource(Category::class, 'category');
    }
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

        // Tìm kiếm theo tên
        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Lọc theo trạng thái
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Lọc theo danh mục cha
        if ($request->filled('parent_id')) {
            $parentId = $request->get('parent_id');
            if ($parentId === '0') {
                // Lọc chỉ danh mục gốc (không có parent)
                $query->whereNull('parent_id');
            } else {
                // Lọc theo parent_id cụ thể
                $query->where('parent_id', $parentId);
            }
        }

        // Sắp xếp
        if ($sortField === 'parent') {
            $query->leftJoin('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
                ->orderBy('parent_categories.name', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        if ($sortField !== 'id') {
            $query->orderBy('id', 'desc');
        }
        
        $categories = $query->paginate(15)->withQueryString();
        
        // Lấy danh sách parent categories cho dropdown filter
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.category.index', compact('categories', 'sortField', 'sortDirection', 'parentCategories'));
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
        
        // Soft delete với thông tin người xóa
        $category->update(['deleted_by' => auth()->id()]);
        $category->delete();
        
        $total = Category::count();
        $maxPage = max(ceil($total / $perPage), 1);
        $page = min($page, $maxPage);
        return redirect()->route('admin.categories.index', [
            'page' => $page,
            'per_page' => $perPage
        ])->with('success', 'Danh mục đã được chuyển vào thùng rác.');
    }

    /**
     * Hiển thị thùng rác
     */
    public function trash(Request $request)
    {
        $query = Category::onlyTrashed()->with(['parent' => function($query) {
            $query->withTrashed();
        }, 'deletedBy']);

        // Tìm kiếm theo tên
        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Sắp xếp
        $sortField = in_array($request->sort, ['name', 'deleted_at']) ? $request->sort : 'deleted_at';
        
        if ($sortField === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderBy('deleted_at', 'desc');
        }

        $trashedCategories = $query->paginate(15)->withQueryString();

        return view('admin.category.trash', compact('trashedCategories'));
    }

    /**
     * Khôi phục danh mục từ thùng rác
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        
        // Kiểm tra quyền
        $this->authorize('restore', $category);
        
        $category->restore();
        $category->update(['deleted_by' => null]);

        return redirect()->route('admin.categories.trash')
            ->with('success', 'Danh mục đã được khôi phục thành công.');
    }

    /**
     * Xóa vĩnh viễn danh mục
     */
    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        
        // Kiểm tra quyền
        $this->authorize('forceDelete', $category);
        
        // Kiểm tra ràng buộc trước khi xóa vĩnh viễn
        $children = $category->children()->withTrashed()->pluck('name');
        if ($children->isNotEmpty()) {
            return back()
                ->with('error', 'Không thể xóa vĩnh viễn vì có danh mục con: ' . $children->implode(', '));
        }
        
        $productCount = $category->products()->withTrashed()->count();
        if ($productCount > 0) {
            return back()->with('error', "Không thể xóa vĩnh viễn vì có {$productCount} sản phẩm liên kết.");
        }

        $category->forceDelete();

        return redirect()->route('admin.categories.trash')
            ->with('success', 'Danh mục đã được xóa vĩnh viễn.');
    }
}
