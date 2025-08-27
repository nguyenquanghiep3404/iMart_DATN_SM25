<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\SpecificationGroup;

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
        $totalCategories = Category::count();
        // Kiểm tra filters - chỉ cần 1 trong các field có giá trị
        $hasFilters = $request->filled('search') ||
            $request->filled('status') ||
            $request->filled('parent_id');
        // Logic phân luồng: Tree view cho ≤50 items + không filter, ngược lại dùng pagination
        if ($totalCategories <= 50 && !$hasFilters) {
            return $this->renderTreeView($request);
        }
        return $this->renderPaginatedView($request, $totalCategories > 50);
    }
    /**
     * Render tree view cho categories ít và không có filter
     */
    private function renderTreeView(Request $request)
    {
        // Load tất cả categories với default sorting
        $allCategories = Category::with('parent')->orderBy('id', 'desc')->get();
        // Xây dựng tree và flatten trong một bước - tối ưu performance
        $categories = $this->buildTreeAndFlatten($allCategories);
        $isTreeView = true;
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.category.index', compact('categories', 'isTreeView', 'parentCategories'));
    }
    /**
     * Render paginated view cho categories nhiều hoặc có filter
     */
    private function renderPaginatedView(Request $request, $autoPaginated = false)
    {
        $query = Category::with('parent');
        // Apply filters inline
        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        // Default sorting - mới nhất trước
        $query->orderBy('id', 'desc');
        $categories = $query->paginate(15)->withQueryString();
        // Thay vì dynamic properties, dùng variables riêng
        $isFiltered = true;
        $autoPaginatedFlag = $autoPaginated;
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.category.index', compact('categories', 'parentCategories', 'isFiltered', 'autoPaginatedFlag'));
    }
    /**
     * Xây dựng tree và flatten trong một bước - tối ưu performance
     */
    private function buildTreeAndFlatten($categories)
    {
        // GroupBy để tối ưu performance - chỉ duyệt collection 1 lần
        $categoriesByParent = $categories->groupBy('parent_id');
        // Hàm đệ quy xây dựng và flatten tree cùng lúc
        $flattenTree = function ($parentId = null, $level = 0) use (&$flattenTree, $categoriesByParent) {
            $result = collect();
            $children = $categoriesByParent->get($parentId, collect());
            foreach ($children as $category) {
                // Gán thông tin tree cho category
                $category->tree_level = $level;
                $category->has_children = $categoriesByParent->has($category->id);
                // Thêm category hiện tại
                $result->push($category);
                // Đệ quy thêm children ngay sau parent
                if ($category->has_children) {
                    $childrenResult = $flattenTree($category->id, $level + 1);
                    $result = $result->concat($childrenResult);
                }
            }
            return $result;
        };
        return $flattenTree();
    }
    public function create(Request $request)
    {
        $specSearch = $request->input('spec_search');
        $specQuery = SpecificationGroup::orderBy('name');
        if ($specSearch) {
            $specQuery->where('name', 'like', '%' . $specSearch . '%');
        }
        $specificationGroups = $specQuery->get();
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.category.create', compact('parents', 'specificationGroups', 'specSearch'));
    }
    /**
     * Lưu danh mục mới vào cơ sở dữ liệu.
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category = Category::create($data);
        // Gán nhóm thông số cho danh mục vừa tạo
        if ($request->has('specification_groups')) {
            $category->specificationGroups()->sync($request->input('specification_groups'));
        }
        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được tạo thành công.');
    }
    public function show(Category $category)
    {
        $category->load('parent', 'children')->loadCount('products');
        return view('admin.category.show', compact('category'));
    }
    public function edit(Request $request, Category $category)
    {
        $specSearch = $request->input('spec_search');
        $specQuery = SpecificationGroup::orderBy('name');
        if ($specSearch) {
            $specQuery->where('name', 'like', '%' . $specSearch . '%');
        }
        $specificationGroups = $specQuery->get();

        $categorySpecificationGroupIds = $category->specificationGroups()->pluck('specification_groups.id')->toArray();
        $parents = Category::whereNull('parent_id')
            ->whereNot('id', $category->id)
            ->orderBy('name', "asc")
            ->pluck('name', 'id');

        return view('admin.category.edit', compact(
            'category',
            'parents',
            'specificationGroups',
            'categorySpecificationGroupIds',
            'specSearch'
        ));
    }
    public function update(CategoryRequest $request, Category $category)
    {
        // Gán nhóm thông số cho danh mục
        // Sử dụng mảng rỗng làm giá trị mặc định nếu không có nhóm nào được chọn
        $category->specificationGroups()->sync($request->input('specification_groups', []));

        // Validate và cập nhật dữ liệu danh mục
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category->update($data);
        // !!! FIX: Luôn chuyển hướng về trang index sau khi cập nhật thành công
        return redirect()->route('admin.categories.index')
            ->with('success', "Danh mục '{$category->name}' đã được cập nhật thành công.");
    }
    public function destroy(Category $category)
    {
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
        // Kiểm tra nếu có pagination (filtered view) thì redirect với page params
        if (request('page')) {
            $page = request('page', 1);
            $perPage = request('per_page', 15);
            $total = Category::count();
            $maxPage = max(ceil($total / $perPage), 1);
            $page = min($page, $maxPage);
            return redirect()->route('admin.categories.index', [
                'page' => $page,
                'per_page' => $perPage
            ])->with('success', 'Danh mục đã được chuyển vào thùng rác.');
        }
        // Tree view - redirect về index không có pagination
        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được chuyển vào thùng rác.');
    }
    /**
     * Hiển thị thùng rác
     */
    public function trash(Request $request)
    {
        $query = Category::onlyTrashed()->with(['parent' => function ($query) {
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

        dd($id, Category::onlyTrashed()->pluck('id'));

        return redirect()->route('admin.categories.index')
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
    /**
     * Toggle hiển thị danh mục trên trang chủ
     */
    // public function toggleHomepage(Category $category)
    // {
    //     $category->show_on_homepage = !$category->show_on_homepage;
    //     $category->save();

    //     $status = $category->show_on_homepage ? 'hiển thị' : 'ẩn';

    //     return response()->json([
    //         'success' => true,
    //         'message' => "Danh mục '{$category->name}' đã được {$status} trên trang chủ.",
    //         'show_on_homepage' => $category->show_on_homepage
    //     ]);
    // }
}
