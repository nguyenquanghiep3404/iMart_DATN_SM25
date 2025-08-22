@extends('admin.layouts.app')

@section('title', 'Chi tiết Deal Bán Kèm - {{ $bundle->name }}')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f5f7;
        }

        .container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
            overflow: hidden;
        }

        .card-custom-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .card-custom-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .card-custom-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.25rem;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .badge-custom {
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            border-radius: 0.375rem;
        }

        .badge-success-custom {
            background-color: #10b981;
        }

        .badge-secondary-custom {
            background-color: #6b7280;
        }

        .badge-warning-custom {
            background-color: #f59e0b;
        }

        .text-warning {
            color: #f59e0b;
        }

        .grid-cols-auto-fit {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .product-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .product-image {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 0.375rem;
            margin-right: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('content')
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto">
            <!-- Page Header -->
            <header class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Chi tiết Deal: {{ $bundle->name }}</h1>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="flex text-sm text-gray-500">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.bundle-products.index') }}"
                                        class="text-indigo-600 hover:text-indigo-800">Deal bán kèm</a>
                                </li>
                                <li class="text-gray-400 mx-2">/</li>
                                <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Chi tiết
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </header>

            <!-- General Information -->
            <div class="card-custom mb-6">
                <div class="card-custom-header">
                    <h3 class="card-custom-title">Thông tin chung</h3>
                </div>
                <div class="card-custom-body">
                    <dl class="grid-cols-auto-fit">
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Tên nội bộ</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bundle->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Tiêu đề hiển thị</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bundle->display_title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Trạng thái</dt>
                            <dd class="mt-1 text-sm">
                                @if ($bundle->status === 'active' && $bundle->end_date && now()->gt($bundle->end_date))
                                    <span class="badge-custom badge-secondary-custom">Đã hết hạn</span>
                                @elseif ($bundle->status === 'active')
                                    <span class="badge-custom badge-success-custom">Đang kích hoạt</span>
                                @else
                                    <span class="badge-custom badge-secondary-custom">Đã tắt</span>
                                @endif
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-sm font-medium text-gray-700">Mô tả</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bundle->description ?? 'Không có mô tả' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Main Products -->
            <div class="card-custom mb-6">
                <div class="card-custom-header">
                    <h3 class="card-custom-title">Sản phẩm chính ({{ $mainProducts->count() }})</h3>
                </div>
                <div class="card-custom-body">
                    @if ($mainProducts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hình ảnh</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên sản phẩm</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá gốc</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá khuyến mãi
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            SKU</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Biến thể</th>

                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($mainProducts as $product)
                                        @php
                                            $variant = $product->variant;
                                            $baseProduct = $variant->product;
                                            $attributes = $variant->attributeValues;
                                            $nonColor = $attributes
                                                ->filter(fn($v) => $v->attribute->name !== 'Màu sắc')
                                                ->pluck('value')
                                                ->join(' ');
                                            $color = $attributes->firstWhere(
                                                fn($v) => $v->attribute->name === 'Màu sắc',
                                            )?->value;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <img src="{{ $variant->primaryImage?->url ?? $baseProduct->thumbnail_url }}"
                                                    class="product-image" alt="{{ $baseProduct->name }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $baseProduct->name }}
                                                {{ $nonColor ? ' ' . $nonColor : '' }}
                                                {{ $color ? ' ' . $color : '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($variant->price ?? 0) }} VNĐ
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">
                                                {{ $variant->sale_price ? number_format($variant->sale_price) . ' VNĐ' : '—' }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product->sku }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 variant-info">
                                                {{ $nonColor ? $nonColor : '' }}
                                                {{ $color ? ($nonColor ? ', ' : '') . $color : '' }}
                                                {{ !$nonColor && !$color ? 'Không có biến thể' : '' }}
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-gray-500">Chưa có sản phẩm chính
                                                nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Chưa có sản phẩm chính nào.</p>
                    @endif
                </div>
            </div>
            <!-- Suggested Products -->
            <div class="card-custom mb-6">
                <div class="card-custom-header">
                    <h3 class="card-custom-title">Sản phẩm bán kèm ({{ $suggestedProducts->count() }})</h3>
                </div>
                <div class="card-custom-body">
                    @if ($suggestedProducts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hình ảnh</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên sản phẩm</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Biến thể</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá gốc</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá sau giảm</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loại giảm giá</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá trị giảm</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Chọn sẵn</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($suggestedProducts as $product)
                                        @php
                                            $variant = $product->variant;
                                            $baseProduct = $variant->product;
                                            $attributes = $variant->attributeValues;
                                            $nonColor = $attributes
                                                ->filter(fn($v) => $v->attribute->name !== 'Màu sắc')
                                                ->pluck('value')
                                                ->join(' ');
                                            $color = $attributes->firstWhere(
                                                fn($v) => $v->attribute->name === 'Màu sắc',
                                            )?->value;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <img src="{{ $variant->primaryImage?->url ?? $baseProduct->thumbnail_url }}"
                                                    class="product-image" alt="{{ $baseProduct->name }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $baseProduct->name }}
                                                {{ $nonColor ? ' ' . $nonColor : '' }}
                                                {{ $color ? ' ' . $color : '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 variant-info">
                                                {{ $nonColor ? $nonColor : '' }}
                                                {{ $color ? ($nonColor ? ', ' : '') . $color : '' }}
                                                {{ !$nonColor && !$color ? 'Không có biến thể' : '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($variant->price ?? 0) }} VNĐ
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($product->discount_type === 'fixed_price')
                                                    {{ number_format($product->discount_value) }} VNĐ
                                                @else
                                                    {{ number_format($variant->price * (1 - $product->discount_value / 100)) }}
                                                    VNĐ
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product->discount_type === 'fixed_price' ? 'Giá cố định' : 'Giảm theo %' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($product->discount_type === 'fixed_price')
                                                    {{ number_format($product->discount_value) }} VNĐ
                                                @else
                                                    {{ rtrim(rtrim(number_format($product->discount_value, 2), '0'), '.') }}%
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product->is_preselected ? 'Có' : 'Không' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-gray-500">Chưa có sản phẩm gợi ý
                                                nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Chưa có sản phẩm gợi ý nào.</p>
                    @endif
                </div>
            </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="action-buttons">
                                <a href="{{ route('admin.bundle-products.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Quay lại
                                </a>
                                <a href="{{ route('admin.bundle-products.edit', $bundle->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit mr-2"></i>Chỉnh sửa Deal
                                </a>
                                @if ($bundle->status === 'active')
                                    <form action="{{ route('admin.bundle-products.toggle-status', $bundle->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fas fa-toggle-off mr-2"></i>Tắt Deal
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.bundle-products.toggle-status', $bundle->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-toggle-on mr-2"></i>Kích hoạt Deal
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.bundle-products.destroy', $bundle->id) }}" method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa deal này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt mr-2"></i>Xóa Deal
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

        </div>
        @endsection
