@extends('admin.layouts.app')

@section('title', 'Danh sách sản phẩm')

@push('styles')
    {{-- Custom Styles từ HTML bạn cung cấp --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
        .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; display: flex; justify-content: space-between; align-items: center; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: white; } .btn-danger:hover { background-color: #dc2626; }
        .btn-outline-secondary { color: #4a5568; border: 1px solid #d1d5db; } .btn-outline-secondary:hover { background-color: #e5e7eb; }
        .btn-default { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; border-left: 0; } .btn-default:hover { background-color: #d1d5db; }
        .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
        .form-input:focus, .form-select:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,0.25); }
        .input-group { display: flex; }
        .input-group .form-input { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .table-custom { width: 100%; color: #212529; }
        .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; }
        .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; border-bottom-width: 2px; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.25rem; background-color: #fff; }
        .badge-custom { display: inline-block; padding: 0.35em 0.65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.375rem; }
        .badge-success-custom { background-color: #10b981; } .badge-info-custom { background-color: #3b82f6; }
        .badge-warning-custom { background-color: #f59e0b; color: #1f2937; } .badge-secondary-custom { background-color: #6b7280; }
        .product-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .product-modal.show { display: flex; align-items: center; justify-content: center; }
        .product-modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); }
        .product-modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .product-modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .product-close { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .product-close:hover { opacity: 1; color: #1f2937; }
        .product-modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .product-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .product-modal-footer > :not(:first-child) { margin-left: .5rem; }

        /* === CSS CHO HIỆU ỨNG QUAY ICON === */
        .icon-spin {
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-7xl">

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý sản phẩm</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Sản phẩm</li>
                </ol>
            </nav>
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title">Danh sách sản phẩm ({{ $products->total() }})</h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.products.index') }}" id="refresh-products-button" class="btn btn-outline-secondary btn-sm" title="Làm mới danh sách">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Thêm sản phẩm mới
                    </a>
                </div>
            </div>
            <div class="card-custom-body">
                <form action="{{ route('admin.products.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-6 items-end">
                        <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                            <label for="search_product" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" id="search_product" name="search" class="form-input" placeholder="Tên sản phẩm, SKU..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-default -ml-px"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div>
                            <label for="filter_category" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                            <div class="relative">
                                <select id="filter_category" name="category_id" class="form-select appearance-none pr-10">
                                    <option value="">Tất cả danh mục</option>
                                    @if(isset($categories)) {{-- Kiểm tra nếu $categories tồn tại --}}
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <div class="relative">
                                <select id="filter_status" name="status" class="form-select appearance-none pr-10">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Công khai</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Chờ duyệt</option>
                                    <option value="trashed" {{ request('status') == 'trashed' ? 'selected' : '' }}>Đã xóa mềm</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>
                        <div>
                            <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">Loại sản phẩm</label>
                            <div class="relative">
                                <select id="filter_type" name="type" class="form-select appearance-none pr-10">
                                    <option value="">Tất cả loại</option>
                                    <option value="simple" {{ request('type') == 'simple' ? 'selected' : '' }}>Đơn giản</option>
                                    <option value="variable" {{ request('type') == 'variable' ? 'selected' : '' }}>Có biến thể</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>
                        <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2 xl:col-span-1 flex items-end">
                            <button type="submit" class="btn btn-primary w-full sm:w-auto"><i class="fas fa-filter mr-1"></i> Lọc</button>
                            {{-- Nút Làm mới đã được chuyển lên header --}}
                        </div>
                    </div>
                </form>
                
                <div class="flex justify-end mb-4">
                    <div class="relative" x-data="{ openSort: false }" @click.away="openSort = false">
                        <button @click="openSort = !openSort" type="button" class="btn btn-outline-secondary btn-sm">
                            Sắp xếp theo: {{ request('sort_by_text', 'Mới nhất') }} <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div x-show="openSort" id="sortDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 py-1 border border-gray-200" x-cloak>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_dir' => 'desc', 'sort_by_text' => 'Mới nhất']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mới nhất</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => 'asc', 'sort_by_text' => 'Tên: A-Z']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tên: A-Z</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => 'desc', 'sort_by_text' => 'Tên: Z-A']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tên: Z-A</a>
                            {{-- Add more sort options as needed --}}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-custom table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th style="width: 80px">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>SL Tồn</th>
                                <th>Loại</th>
                                <th>Trạng thái</th>
                                <th style="width: 100px" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    <img src="{{ $product->coverImage ? Storage::url($product->coverImage->path) : asset('assets/admin/img/placeholder-image.png') }}" 
                                         alt="{{ $product->coverImage->alt_text ?? $product->name }}" 
                                         class="img-thumbnail-custom"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/admin/img/placeholder-image.png') }}';">
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $product->name }}</a>
                                    <small class="block text-gray-500">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>
                                    @if($product->variants->isNotEmpty())
                                        {{ number_format($product->variants->first()->price, 0, ',', '.') }} ₫
                                        @if($product->variants->first()->sale_price && $product->variants->first()->sale_price < $product->variants->first()->price)
                                            <small class="block text-red-500 line-through">{{ number_format($product->variants->first()->sale_price, 0, ',', '.') }} ₫</small>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    {{ $product->variants->sum('stock_quantity') }}
                                    @if($product->type == 'variable') <small>(Tổng)</small> @endif
                                </td>
                                <td>
                                    @if($product->type == 'simple') <span class="badge-custom badge-info-custom">Đơn giản</span>
                                    @elseif($product->type == 'variable') <span class="badge-custom badge-warning-custom">Có biến thể</span>
                                    @else <span class="badge-custom badge-secondary-custom">{{ Str::title($product->type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status == 'published') <span class="badge-custom badge-success-custom">Công khai</span>
                                    @elseif($product->status == 'draft') <span class="badge-custom badge-secondary-custom">Bản nháp</span>
                                    @elseif($product->status == 'pending_review') <span class="badge-custom badge-info-custom">Chờ duyệt</span>
                                    @else <span class="badge-custom badge-warning-custom">{{ Str::title(str_replace('_',' ',$product->status)) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex space-x-1">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Xóa" onclick="openProductModal('deleteProductModal{{ $product->id }}')"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <div id="deleteProductModal{{ $product->id }}" class="product-modal" tabindex="-1">
                                <div class="product-modal-content">
                                    <div class="product-modal-header">
                                        <h5 class="product-modal-title">Xác nhận xóa sản phẩm</h5>
                                        <button type="button" class="product-close" onclick="closeProductModal('deleteProductModal{{ $product->id }}')"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="product-modal-body">
                                        <p>Bạn có chắc chắn muốn xóa sản phẩm "<strong>{{ $product->name }}</strong>" không?</p>
                                        <p class="text-red-600 mt-2">Hành động này sẽ xóa sản phẩm và tất cả các biến thể, hình ảnh liên quan. Không thể hoàn tác!</p>
                                    </div>
                                    <div class="product-modal-footer">
                                        <button type="button" class="btn btn-secondary" onclick="closeProductModal('deleteProductModal{{ $product->id }}')">Hủy</button>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Xóa vĩnh viễn</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open fa-3x mb-3 text-gray-400"></i>
                                        <p class="text-lg font-medium">Không tìm thấy sản phẩm nào.</p>
                                        <p class="text-sm">Hãy thử điều chỉnh bộ lọc hoặc <a href="{{ route('admin.products.create') }}" class="text-indigo-600 hover:underline">thêm sản phẩm mới</a>.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-custom-footer">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<script>
    // Script cho Modal (giữ nguyên)
    function openProductModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex'; // Hoặc modal.classList.add('flex'); modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeProductModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none'; // Hoặc modal.classList.remove('flex'); modal.classList.add('hidden');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.product-modal.show');
        modals.forEach(modal => {
            if (event.target == modal) { // Đóng khi click vào vùng nền mờ
                closeProductModal(modal.id);
            }
        });
    });
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.product-modal.show');
            modals.forEach(modal => {
                closeProductModal(modal.id);
            });
        }
    });

    // Script cho nút Làm mới với hiệu ứng xoay
    document.addEventListener('DOMContentLoaded', function() {
        const refreshButton = document.getElementById('refresh-products-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function(event) {
                // Không preventDefault vì đây là thẻ <a> và chúng ta muốn nó điều hướng
                const icon = this.querySelector('i.fa-sync-alt');
                if (icon) {
                    icon.classList.add('icon-spin');
                }
                // Animation sẽ hiển thị trong khi trang đang tải lại
                // Không cần xóa class 'icon-spin' vì trang sẽ tải lại
            });
        }

        // Thêm x-cloak cho dropdown của Alpine để tránh FOUC
        const sortDropdown = document.getElementById('sortDropdown');
        if(sortDropdown) {
            sortDropdown.setAttribute('x-cloak', '');
        }
    });
</script>
@endpush
