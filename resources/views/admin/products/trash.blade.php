@extends('admin.layouts.app')

@section('title', 'Thùng rác sản phẩm')

@push('styles')
    {{-- Using the same modern styles from the product list for consistency --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
        .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; line-height: 1rem; }
        .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
        .btn-success { background-color: #10b981; color: white; } .btn-success:hover { background-color: #059669; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: white; } .btn-danger:hover { background-color: #dc2626; }
        .btn-outline-secondary { color: #4a5568; background-color: #fff; border: 1px solid #d1d5db; } .btn-outline-secondary:hover { background-color: #f9fafb; }
        .btn-default { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; border-left: 0; } .btn-default:hover { background-color: #d1d5db; }
        .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
        .input-group { display: flex; }
        .input-group .form-input { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .table-custom { width: 100%; min-width: 800px; color: #374151; }
        .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; white-space: nowrap; }
        .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; border-bottom-width: 2px; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.25rem; background-color: #fff; }
        .badge-custom { display: inline-block; padding: 0.35em 0.65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.375rem; }
        .badge-warning-custom { background-color: #f59e0b; color: #1f2937; } .badge-info-custom { background-color: #3b82f6; }
        .product-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .product-modal.show { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
        .product-modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); }
        .product-modal.show .animated-modal { animation: fadeInScale 0.3s ease-out forwards; }
        .product-modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .product-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; gap: 0.75rem; }
        .product-modal-footer.justify-center { justify-content: center; padding-top: 0; padding-bottom: 1.5rem; border-top: none; background-color: #fff; }
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">
        
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
            <h1 class="text-3xl font-bold text-gray-800">Thùng rác</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:text-indigo-800">Sản phẩm</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                </ol>
            </nav>
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="card-custom-title">Sản phẩm đã xóa ({{ $trashedProducts->total() }})</h3>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-custom-body">
                {{-- FILTERS --}}
                <form action="{{ route('admin.products.trash') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
                        {{-- Search Input --}}
                        <div class="md:col-span-2 xl:col-span-2">
                            <label for="search_product" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" id="search_product" name="search" class="form-input" placeholder="Tên sản phẩm, SKU..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-default -ml-px" aria-label="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        {{-- Category Filter --}}
                        <div>
                            <label for="filter_category" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                            <select id="filter_category" name="category_id" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                {{-- The $categories variable needs to be passed from the controller --}}
                                @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Type Filter --}}
                        <div>
                            <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">Loại sản phẩm</label>
                            <select id="filter_type" name="type" class="form-select">
                                <option value="">Tất cả loại</option>
                                <option value="simple" {{ request('type') == 'simple' ? 'selected' : '' }}>Đơn giản</option>
                                <option value="variable" {{ request('type') == 'variable' ? 'selected' : '' }}>Có biến thể</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Action buttons row --}}
                    <div class="flex justify-end gap-x-3 pt-2 mb-6">
                        <a href="{{ route('admin.products.trash') }}" class="btn btn-secondary">Xóa lọc</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Lọc</button>
                    </div>
                </form>

                {{-- SORT DROPDOWN --}}
                <div class="flex justify-end mb-4">
                    <div class="relative" x-data="{ openSort: false }" @click.away="openSort = false">
                        <button @click="openSort = !openSort" type="button" class="btn btn-outline-secondary btn-sm">
                            Sắp xếp theo: {{ request('sort_by_text', 'Ngày xóa: Mới nhất') }} <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div x-show="openSort" id="sortDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-20 py-1 border border-gray-200" x-cloak>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'deleted_at', 'sort_dir' => 'desc', 'sort_by_text' => 'Ngày xóa: Mới nhất']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ngày xóa: Mới nhất</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'deleted_at', 'sort_dir' => 'asc', 'sort_by_text' => 'Ngày xóa: Cũ nhất']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ngày xóa: Cũ nhất</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => 'asc', 'sort_by_text' => 'Tên: A-Z']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tên: A-Z</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => 'desc', 'sort_by_text' => 'Tên: Z-A']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tên: Z-A</a>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="table-custom table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th style="width: 80px;">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Loại</th>
                                <th>Ngày xóa</th>
                                @can('view_deleted_by_anyone')
                                    <th>Người xóa</th>
                                @endcan
                                <th style="width: 220px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($trashedProducts as $product)
                            <tr>
                                <td>{{ ($trashedProducts->currentPage() - 1) * $trashedProducts->perPage() + $loop->iteration }}</td>
                                <td>
                                    @php
                                        // Logic to get a display image, same as index page
                                        $displayVariant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
                                        $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                                        $imageUrl = $imageToShow ? Storage::url($imageToShow->path) : asset('assets/admin/img/placeholder-image.png');
                                        $altText = $imageToShow?->alt_text ?? $product->name;
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="img-thumbnail-custom" onerror="this.onerror=null;this.src='{{ asset('assets/admin/img/placeholder-image.png') }}';">
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $product->name }}</div>
                                    <small class="text-gray-500">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($product->type == 'simple') <span class="badge-custom badge-info-custom">Đơn giản</span>
                                    @elseif($product->type == 'variable') <span class="badge-custom badge-warning-custom">Biến thể</span>
                                    @endif
                                </td>
                                <td>{{ $product->deleted_at->format('d/m/Y H:i') }}</td>
                                
                                @can('view_deleted_by_anyone')
                                    <td>{{ $product->deletedBy->name ?? 'Không rõ' }}</td>
                                @endcan

                                <td class="text-center">
                                    <div class="inline-flex space-x-2">
                                        {{-- Restore Button --}}
                                        <button type="button" class="btn btn-success btn-sm" onclick="openProductModal('restoreModal{{ $product->id }}')">
                                            <i class="fas fa-undo mr-1"></i> Khôi phục
                                        </button>
                                        {{-- Force Delete Button --}}
                                        @can('forceDelete', $product)
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openProductModal('forceDeleteModal{{ $product->id }}')">
                                                <i class="fas fa-skull-crossbones mr-1"></i> Xóa vĩnh viễn
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- RESTORE CONFIRMATION MODAL --}}
                            <div id="restoreModal{{ $product->id }}" class="product-modal" tabindex="-1">
                                <div class="product-modal-content animated-modal">
                                    <form action="{{ route('admin.products.restore', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="product-modal-body text-center p-6">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                                                <i class="fas fa-undo fa-2x text-green-500"></i>
                                            </div>
                                            <h5 class="text-xl font-semibold text-gray-800">Khôi phục sản phẩm?</h5>
                                            <p class="text-gray-600 mt-2">Bạn có chắc chắn muốn khôi phục sản phẩm<br>"<strong>{{ $product->name }}</strong>"?</p>
                                            <p class="text-gray-500 mt-2 text-sm">Sản phẩm sẽ được chuyển về trạng thái "Bản nháp".</p>
                                        </div>
                                        <div class="product-modal-footer justify-center">
                                            <button type="button" class="btn btn-secondary" onclick="closeProductModal('restoreModal{{ $product->id }}')">Hủy bỏ</button>
                                            <button type="submit" class="btn btn-success">Đồng ý, khôi phục</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- FORCE DELETE CONFIRMATION MODAL --}}
                            <div id="forceDeleteModal{{ $product->id }}" class="product-modal" tabindex="-1">
                                <div class="product-modal-content animated-modal">
                                    <form action="{{ route('admin.products.force-delete', $product->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="product-modal-body text-center p-6">
                                             <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-4">
                                                <i class="fas fa-exclamation-triangle fa-2x text-red-500"></i>
                                            </div>
                                            <h5 class="text-xl font-semibold text-gray-800">Cảnh báo Xóa vĩnh viễn</h5>
                                            <p class="text-gray-600 mt-2">Hành động này <strong class="text-red-600">không thể</strong> hoàn tác. Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm "<strong>{{ $product->name }}</strong>"?</p>
                                        </div>
                                        <div class="product-modal-footer justify-center">
                                            <button type="button" class="btn btn-secondary" onclick="closeProductModal('forceDeleteModal{{ $product->id }}')">Không, giữ lại</button>
                                            <button type="submit" class="btn btn-danger">Tôi hiểu, xóa vĩnh viễn</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open fa-3x mb-3 text-gray-400"></i>
                                        <p class="text-lg font-medium">Không tìm thấy sản phẩm nào trong thùng rác.</p>
                                        <p class="text-sm">Hãy thử điều chỉnh bộ lọc.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($trashedProducts->hasPages())
            <div class="card-custom-footer">
                <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center w-full">
                    <p class="text-sm text-gray-700 leading-5">
                        Hiển thị từ <span class="font-medium">{{ $trashedProducts->firstItem() }}</span> đến <span class="font-medium">{{ $trashedProducts->lastItem() }}</span> trên tổng số <span class="font-medium">{{ $trashedProducts->total() }}</span> kết quả
                    </p>
                    <div>
                        {!! $trashedProducts->appends(request()->query())->links() !!}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- IMPORTANT: AlpineJS is required for the sort dropdown --}}
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<script>
    // ADVANCED MODAL & TOAST SCRIPT (from index page)
    function openProductModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeProductModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal on outside click
        window.addEventListener('click', function(event) {
            const openModal = document.querySelector('.product-modal.show');
            if (openModal && event.target == openModal) {
                closeProductModal(openModal.id);
            }
        });

        // Close modal on Escape key press
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.product-modal.show');
                if (openModal) {
                    closeProductModal(openModal.id);
                }
            }
        });

        // TOAST NOTIFICATION SCRIPT
        const toasts = document.querySelectorAll('.toast');
        const hideToast = (toastElement) => {
            if (toastElement) {
                toastElement.classList.add('hide');
                setTimeout(() => toastElement.remove(), 350);
            }
        };
        toasts.forEach(toast => {
            const autoHideTimeout = setTimeout(() => hideToast(toast), 5000);
            const closeButton = toast.querySelector('[data-dismiss-target]');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    clearTimeout(autoHideTimeout);
                    const targetId = this.getAttribute('data-dismiss-target');
                    hideToast(document.querySelector(targetId));
                });
            }
        });
    });
</script>
@endpush
