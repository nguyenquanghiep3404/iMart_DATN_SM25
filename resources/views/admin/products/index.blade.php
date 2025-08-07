@extends('admin.layouts.app')

@section('title', 'Danh sách sản phẩm')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Quản lý sản phẩm</h1>
        <nav aria-label="breadcrumb" class="mt-2">
            <ol class="flex text-sm text-slate-500">
                <li><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                <li class="text-slate-400 mx-2">/</li>
                <li class="font-medium" aria-current="page">Sản phẩm</li>
            </ol>
        </nav>
    </div>

    {{-- Main Card --}}
    <div class="bg-white rounded-xl shadow-lg">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 rounded-t-xl">
            {{-- Card Header --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                <h2 class="text-xl font-semibold text-slate-800">
                    Danh sách sản phẩm <span class="text-slate-500 font-medium">({{ $products->total() }})</span>
                </h2>
                <div class="flex items-center space-x-2 mt-3 sm:mt-0">
                    <a href="{{ route('admin.products.index') }}" title="Làm mới danh sách"
                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-colors bg-white border border-slate-300 text-slate-600 hover:bg-slate-100">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                    <a href="{{ route('admin.products.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors bg-indigo-600 text-white hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Thêm sản phẩm
                    </a>
                </div>
            </div>
        </div>
        <div class="p-6">
            {{-- Filters --}}
            <form action="{{ route('admin.products.index') }}" method="GET">
                <div class="mb-6">
                    <div class="flex flex-wrap items-end gap-4">
                        {{-- Search Input --}}
                        <div class="flex-1 min-w-[220px]">
                            <label for="search_product" class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
                            <div class="relative">
                                <input type="text" id="search_product" name="search" placeholder="Tên sản phẩm, SKU..." value="{{ request('search') }}"
                                    class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-slate-400"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Other Filters --}}
                        @php
                        $filters = [
                        ['name' => 'category_id', 'label' => 'Danh mục', 'placeholder' => 'Tất cả danh mục', 'options' => $categories ?? [], 'value_key' => 'id', 'text_key' => 'name'],
                        ['name' => 'status', 'label' => 'Trạng thái', 'placeholder' => 'Tất cả trạng thái', 'options' => [['value' => 'published', 'text' => 'Công khai'], ['value' => 'draft', 'text' => 'Bản nháp'], ['value' => 'pending_review', 'text' => 'Chờ duyệt']]],
                        ['name' => 'type', 'label' => 'Loại sản phẩm', 'placeholder' => 'Tất cả loại', 'options' => [['value' => 'simple', 'text' => 'Đơn giản'], ['value' => 'variable', 'text' => 'Có biến thể']]]
                        ];
                        @endphp

                        @foreach ($filters as $filter)
                        <div class="flex-1 min-w-[200px]">
                            <label for="filter_{{ $filter['name'] }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $filter['label'] }}</label>
                            <select id="filter_{{ $filter['name'] }}" name="{{ $filter['name'] }}" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500">
                                <option value="">{{ $filter['placeholder'] }}</option>
                                @foreach ($filter['options'] as $option)
                                <option value="{{ $option['value'] ?? $option->{$filter['value_key']} }}"
                                    {{ request($filter['name']) == ($option['value'] ?? $option->{$filter['value_key']}) ? 'selected' : '' }}>
                                    {{ $option['text'] ?? $option->{$filter['text_key']} }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-x-2">
                            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors bg-white border border-slate-300 text-slate-600 hover:bg-slate-100">Xóa lọc</a>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors bg-indigo-600 text-white hover:bg-indigo-700">
                                <i class="fas fa-filter mr-2"></i>Lọc
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table Wrapper --}}
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-12">STT</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-20">Ảnh</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider min-w-[250px]">Tên sản phẩm</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Danh mục</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Giá</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tồn kho</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Loại</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse ($products as $product)
                        <tr class="hover:bg-slate-50" x-data="{ deleteModalOpen: false }">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $loop->iteration + $products->firstItem() - 1 }}</td>
                            <td class="px-4 py-3">
                                @php
                                // Simplified image logic
                                $image = $product->coverImage ?? $product->variants->first()?->primaryImage;
                                $imageUrl = $image ? Storage::url($image->path) : asset('assets/admin/img/placeholder-image.png');
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-14 h-14 object-cover rounded-md border border-slate-200 p-1 bg-white">
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $product->name }}</a>
                                <p class="text-slate-500 text-xs mt-1">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $product->category->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                                {{-- Simplified Price Logic --}}
                                @php
                                $variant = $product->variants->sortBy('price')->first();
                                @endphp
                                <span class="font-semibold">{{ number_format($variant->sale_price ?? $variant->price, 0, ',', '.') }} ₫</span>
                                @if($variant->sale_price && $variant->sale_price < $variant->price)
                                    <span class="block text-xs text-slate-500 line-through">{{ number_format($variant->price, 0, ',', '.') }} ₫</span>
                                    @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 font-medium">{{ $product->variants->sum('stock') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if ($product->type == 'simple')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-700">Đơn giản</span>
                                @elseif($product->type == 'variable')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Biến thể ({{ $product->variants->count() }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if ($product->status == 'published')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Công khai</span>
                                @elseif($product->status == 'draft')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Bản nháp</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Chờ duyệt</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                <div class="inline-flex rounded-lg shadow-sm">
                                    <a href="{{ route('admin.product-variants.adjust-form', $variant->id) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                                        🛠
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" title="Chỉnh sửa" class="px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 rounded-l-md hover:bg-indigo-700 focus:z-10 focus:ring-2 focus:ring-indigo-500">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button @click="deleteModalOpen = true" title="Xóa" type="button" class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-r-md hover:bg-red-700 focus:z-10 focus:ring-2 focus:ring-red-500">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- AlpineJS Delete Modal --}}
                            <div x-show="deleteModalOpen" x-trap.noscroll="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60" style="display: none;">
                                <div @click.away="deleteModalOpen = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto" x-show="deleteModalOpen" x-transition>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="p-6 text-center">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-4">
                                                <i class="fas fa-trash-alt fa-2x text-red-500"></i>
                                            </div>
                                            <h3 class="text-xl font-semibold text-slate-800">Chuyển vào thùng rác?</h3>
                                            <p class="text-slate-600 mt-2">Bạn có chắc muốn chuyển sản phẩm<br>"<strong>{{ $product->name }}</strong>" vào thùng rác không?</p>
                                            <p class="text-slate-500 mt-2 text-sm">Bạn vẫn có thể khôi phục lại sản phẩm này sau.</p>
                                        </div>
                                        <div class="flex justify-center gap-4 bg-slate-50 p-4 rounded-b-lg">
                                            <button type="button" @click="deleteModalOpen = false" class="px-6 py-2 text-sm font-medium rounded-lg transition-colors bg-white border border-slate-300 text-slate-600 hover:bg-slate-100">Hủy bỏ</button>
                                            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg transition-colors bg-red-600 hover:bg-red-700">Đồng ý</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-box-open fa-4x mb-4 text-slate-400"></i>
                                    <p class="text-lg font-semibold">Không tìm thấy sản phẩm nào.</p>
                                    <p class="mt-1">Hãy thử điều chỉnh bộ lọc hoặc <a href="{{ route('admin.products.create') }}" class="text-indigo-600 hover:underline">thêm sản phẩm mới</a>.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($products->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- AlpineJS Toast Notifications --}}
@if (session('success') || session('error'))
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform translate-x-full"
    class="fixed top-8 right-8 z-50 flex items-center w-full max-w-xs p-4 text-slate-500 bg-white rounded-lg shadow-lg" role="alert" style="display: none;">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg {{ session('success') ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }}">
        <i class="fas {{ session('success') ? 'fa-check' : 'fa-exclamation-triangle' }}"></i>
    </div>
    <div class="ml-3 text-sm font-normal">{{ session('success') ?? session('error') }}</div>
    <button type="button" @click="show = false" class="ml-auto -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg p-1.5 hover:bg-slate-100 inline-flex h-8 w-8">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

@endsection

@push('scripts')
{{-- AlpineJS v3 is now recommended --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush