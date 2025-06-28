@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@push('styles')
<style>
    .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
    .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
    .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
    .card-custom-body { padding: 1.5rem; }
    .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; line-height: 1rem; }
    .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
    .btn-danger { background-color: #ef4444; color: white; } .btn-danger:hover { background-color: #dc2626; }
    .btn-outline-secondary { color: #4a5568; background-color: #fff; border: 1px solid #d1d5db; } .btn-outline-secondary:hover { background-color: #f9fafb; }
    .btn-default { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; border-left: 0; } .btn-default:hover { background-color: #d1d5db; }
    .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
    .form-input:focus, .form-select:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,0.25); }
    .input-group { display: flex; }
    .input-group .form-input { border-top-right-radius: 0; border-bottom-right-radius: 0; }
    .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .table-custom { width: 100%; color: #374151; }
    .table-custom th, .table-custom td { padding: 0.5rem 0.75rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; }
    .table-custom th:last-child, .table-custom td:last-child { white-space: nowrap; }
    
    /* Tree structure styles */
    .category-row[data-level="0"] {
        background-color: #fefefe;
        font-weight: 500;
        border-left: 3px solid #4f46e5;
    }
    .category-row[data-level="1"] {
        background-color: #f8fafc;
        border-left: 3px solid #f59e0b;
    }
    .category-row[data-level="2"] {
        background-color: #f1f5f9;
        border-left: 3px solid #10b981;
    }
    .category-row[data-level="3"] {
        background-color: #e2e8f0;
        border-left: 3px solid #f43f5e;
    }
    .category-row:hover {
        background-color: #e0f2fe !important;
        transform: translateX(2px);
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Tree connection lines */
    .tree-connector {
        color: #cbd5e1;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1;
        font-weight: normal;
    }
    


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-custom th, .table-custom td { padding: 0.5rem 0.5rem; font-size: 0.875rem; }
        .category-row div[style*="padding-left"] {
            padding-left: 8px !important;
        }
    }
    

    .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; border-bottom-width: 2px; }
    .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
    .badge-custom { display: inline-block; padding: 0.35em 0.65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.375rem; }
    .badge-success-custom { background-color: #10b981; } .badge-warning-custom { background-color: #f59e0b; color: #1f2937; }
    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
    .toast.hide { opacity: 0; transform: translateX(100%); }
    .category-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
    .category-modal.show { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
    .category-modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); }
    .category-modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
    .category-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .category-modal.show .animated-modal { animation: fadeInScale 0.3s ease-out forwards; }
    @keyframes fadeInScale { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="w-full">
        
        {{-- TOAST NOTIFICATIONS CONTAINER --}}
        <div id="toast-container" class="toast-container">
            @if (session('success'))
                <div id="toast-success" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg"><i class="fas fa-check"></i></div>
                    <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close"><span class="sr-only">Close</span><i class="fas fa-times"></i></button>
                </div>
            @endif
            @if (session('error'))
                <div id="toast-error" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ml-3 text-sm font-normal">{{ session('error') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-error" aria-label="Close"><span class="sr-only">Close</span><i class="fas fa-times"></i></button>
                </div>
            @endif
        </div>

        {{-- PAGE HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Danh mục</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Danh mục</li>
                </ol>
            </nav>
            @if(!request()->filled('search') && !request()->filled('status') && !request()->filled('parent_id'))
            @elseif(isset($autoPaginatedFlag) && $autoPaginatedFlag)
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                    <span class="text-sm text-amber-700">
                        Tự động chuyển sang chế độ phân trang do có quá nhiều danh mục (>50)
                    </span>
                </div>
            </div>
            @endif
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                {{-- Responsive header layout --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="card-custom-title">
                        @if(isset($isFiltered) && $isFiltered && method_exists($categories, 'total'))
                            Danh sách danh mục ({{ $categories->total() }} kết quả)
                        @elseif(isset($isTreeView) && $isTreeView)
                            Cấu trúc danh mục ({{ $categories->count() }} danh mục)
                        @else
                            Danh sách danh mục ({{ $categories->count() }} danh mục)
                        @endif
                    </h3>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.categories.trash') }}" class="btn btn-outline-secondary btn-sm" title="Thùng rác"><i class="fas fa-trash mr-2"></i>Thùng rác</a>
                        <a href="{{ route('admin.categories.index') }}" id="refresh-categories-button" class="btn btn-outline-secondary btn-sm" title="Làm mới danh sách"><i class="fas fa-sync-alt"></i></a>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>Thêm danh mục</a>
                    </div>
                </div>
            </div>
            <div class="card-custom-body">
                {{-- FILTERS --}}
                <form action="{{ route('admin.categories.index') }}" method="GET">
                    {{-- Filters grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-4">
                        {{-- Search Input --}}
                        <div class="md:col-span-2 lg:col-span-2 xl:col-span-2">
                            <label for="search_category" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" id="search_category" name="search" class="form-input" placeholder="Tên danh mục..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-default -ml-px" aria-label="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div>
                            <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="filter_status" name="status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                        </div>

                        {{-- Parent Filter --}}
                        <div>
                            <label for="filter_parent" class="block text-sm font-medium text-gray-700 mb-1">Danh mục cha</label>
                            <select id="filter_parent" name="parent_id" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <option value="0" {{ request('parent_id') === '0' ? 'selected' : '' }}>Danh mục gốc</option>
                                @foreach($parentCategories as $parentCategory)
                                    <option value="{{ $parentCategory->id }}" {{ request('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                                        {{ $parentCategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sort Filter --}}
                        <div>
                            <label for="filter_sort" class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp</label>
                            <select id="filter_sort" name="sort" class="form-select">
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên</option>
                                <option value="id" {{ request('sort') == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Trạng thái</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Action buttons row --}}
                    <div class="flex justify-end gap-x-3 pt-2 mb-6">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Xóa lọc</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Lọc danh mục</button>
                    </div>
                </form>

                {{-- CATEGORIES TABLE --}}
                <div class="border border-gray-200 rounded-lg shadow-sm bg-white">
                    <table class="table-custom table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 35%;">Tên danh mục</th>
                                <th style="width: 30%;" class="hidden md:table-cell">Mô tả</th>
                                <th style="width: 100px;" class="text-center">Trạng thái</th>
                                <th style="width: 140px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                            <tr class="category-row" data-level="{{ $category->tree_level ?? 0 }}">
                                <td>{{ $category->id }}</td>
                                <td>
                                    <div class="flex items-center" style="padding-left: {{ ($category->tree_level ?? 0) * 24 }}px;">
                                        @if(isset($category->tree_level) && $category->tree_level > 0)
                                            {{-- Child indicator --}}
                                            <div class="flex items-center mr-2 tree-connector">
                                                @for($i = 0; $i < $category->tree_level; $i++)
                                                    @if($i == $category->tree_level - 1)
                                                        <span>├─</span>
                                                    @else
                                                        <span class="mr-2">│</span>
                                                    @endif
                                                @endfor
                                            </div>
                                        @endif
                                        
                                        <div>
                                            <a href="{{ route('admin.categories.show', $category->id) }}" 
                                               class="font-semibold text-indigo-600 hover:text-indigo-800 
                                                      {{ isset($category->tree_level) && $category->tree_level > 0 ? 'text-sm' : '' }}">
                                                {{ $category->name }}
                                            </a>
                                            @if(isset($category->tree_level) && $category->tree_level > 0)
                                                <div class="text-xs text-gray-400 italic">Danh mục con</div>
                                            @endif
                                            <div class="text-xs text-gray-500 md:hidden mt-1">{{ Str::limit($category->description ?? '', 30, '...') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm text-gray-600 hidden md:table-cell" style="word-wrap: break-word; white-space: normal;">
                                    {{ Str::limit($category->description ?? '', 80, '...') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge-custom {{ $category->status === 'active' ? 'badge-success-custom' : 'badge-warning-custom' }}">
                                        {{ $category->status === 'active' ? 'Hoạt động' : 'Tắt' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex justify-center items-center space-x-1" style="min-width: 120px;">
                                        <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-outline-secondary btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Xóa" onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                        <h5 class="text-lg font-medium text-gray-500 mb-2">Chưa có danh mục nào</h5>
                                        <p class="text-sm text-gray-400">Nhấn nút "Thêm danh mục" để bắt đầu</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- PAGINATION - hiển thị cho filtered view --}}
            @if(isset($isFiltered) && $isFiltered && method_exists($categories, 'hasPages') && $categories->hasPages())
            <div class="card-custom-footer">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Hiển thị {{ $categories->firstItem() }} - {{ $categories->lastItem() }} trong tổng số {{ $categories->total() }} kết quả
                    </div>
                    <div>
                        {{ $categories->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            {{-- Footer cho tree view --}}
            @elseif(isset($isTreeView) && $isTreeView)
            <div class="card-custom-footer">
                <div class="flex justify-center items-center">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-sitemap mr-2"></i>Hiển thị tất cả danh mục theo cấu trúc tree
                    </div>
                </div>
            </div>
            {{-- Footer cho auto pagination hoặc filtered view --}}
            @else
            <div class="card-custom-footer">
                <div class="flex justify-center items-center">
                    <div class="text-sm text-gray-500">
                        @if(isset($autoPaginatedFlag) && $autoPaginatedFlag)
                            <i class="fas fa-list mr-2"></i>Tự động phân trang do quá nhiều danh mục
                        @else
                            <i class="fas fa-filter mr-2"></i>Kết quả lọc với phân trang
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div id="deleteModal" class="category-modal">
    <div class="category-modal-content animated-modal">
        <div class="category-modal-body text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Xác nhận xóa</h3>
            <p class="text-sm text-gray-500 mb-4">Bạn có chắc chắn muốn xóa danh mục <strong id="categoryNameToDelete"></strong>?</p>
            <p class="text-xs text-gray-400">Danh mục sẽ được chuyển vào thùng rác và có thể khôi phục sau.</p>
        </div>
        <div class="category-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Hủy</button>
            <button type="button" class="btn btn-danger ml-2" id="confirmDeleteBtn" onclick="executeDelete()">
                <i class="fas fa-trash-alt mr-2"></i>Xóa
            </button>
        </div>
    </div>
</div>

{{-- HIDDEN DELETE FORM --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    @if(isset($isFiltered) && $isFiltered && method_exists($categories, 'currentPage'))
        <input type="hidden" name="page" value="{{ $categories->currentPage() }}">
    @endif
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toasts after 5 seconds
    setTimeout(function() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
            toast.classList.add('hide');
            setTimeout(function() {
                toast.remove();
            }, 300);
        });
    }, 5000);

    // Toast close buttons
    document.querySelectorAll('[data-dismiss-target]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-dismiss-target'));
            if (target) {
                target.classList.add('hide');
                setTimeout(function() {
                    target.remove();
                }, 300);
            }
        });
    });
});

let categoryIdToDelete = null;

function confirmDelete(categoryId, categoryName) {
    categoryIdToDelete = categoryId;
    document.getElementById('categoryNameToDelete').textContent = categoryName;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    categoryIdToDelete = null;
}

function executeDelete() {
    if (categoryIdToDelete) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/categories/${categoryIdToDelete}`;
        form.submit();
    }
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endpush