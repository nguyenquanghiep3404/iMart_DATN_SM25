@extends('admin.layouts.app')

@section('title', 'Quản lý Máy Cũ & Mở Hộp')

@push('styles')
<style>
    /* Các CSS custom cho card, button, table, badge... sẽ được đặt ở đây */
    /* Bạn có thể copy CSS từ file HTML mẫu vào đây hoặc đặt trong file CSS chung */
    .card-custom {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }
    .card-custom-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }
    .card-custom-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    .card-custom-body {
        padding: 1.5rem;
    }
    .card-custom-footer {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.25rem;
        border: 1px solid transparent;
    }
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-danger { background-color: #ef4444; color: white; }
    .btn-danger:hover { background-color: #dc2626; }
    .form-input, .form-select {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        background-color: white;
    }
    .table-custom { width: 100%; min-width: 800px; color: #374151; }
    .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; white-space: nowrap; }
    .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; }
    .badge-custom { display: inline-block; padding: 0.35em 0.65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.375rem; }
    .badge-success { background-color: #10b981; }
    .badge-danger { background-color: #ef4444; }
    .badge-warning { background-color: #f59e0b; color: #1f2937; }
    .badge-info { background-color: #3b82f6; }
    .badge-secondary { background-color: #6b7280; }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <!-- PAGE HEADER -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Máy Cũ & Mở Hộp</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Máy Cũ & Mở Hộp</li>
                </ol>
            </nav>
        </header>

        <div class="card-custom">
            <div class="card-custom-header">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="card-custom-title">Danh sách sản phẩm ({{ $items->total() }})</h3>
                    <a href="{{ route('admin.trade-in-items.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Thêm sản phẩm mới
                    </a>
                </div>
            </div>

            <div class="card-custom-body">
                <!-- FILTERS -->
                <form action="{{ route('admin.trade-in-items.index') }}" method="GET" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="sr-only">Tìm kiếm</label>
                            <input type="text" id="search" name="search" class="form-input" placeholder="Tìm theo tên, SKU, IMEI/Serial..." value="{{ request('search') }}">
                        </div>
                        <div>
                            <label for="filter_status" class="sr-only">Trạng thái</label>
                            <select id="filter_status" name="status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="available" @selected(request('status') == 'available')>Sẵn sàng bán</option>
                                <option value="pending_inspection" @selected(request('status') == 'pending_inspection')>Chờ kiểm tra</option>
                                <option value="sold" @selected(request('status') == 'sold')>Đã bán</option>
                            </select>
                        </div>
                         <div>
                            <label for="filter_condition" class="sr-only">Tình trạng</label>
                            <select id="filter_condition" name="condition_grade" class="form-select">
                                <option value="">Tất cả tình trạng</option>
                                <option value="A" @selected(request('condition_grade') == 'A')>Loại A (Như mới)</option>
                                <option value="B" @selected(request('condition_grade') == 'B')>Loại B (Trầy xước nhẹ)</option>
                                <option value="C" @selected(request('condition_grade') == 'C')>Loại C (Trầy xước nhiều)</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn btn-primary">Áp dụng lọc</button>
                    </div>
                </form>

                <!-- TABLE -->
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>SKU / IMEI</th>
                                <th class="text-center">Tình trạng</th>
                                <th>Giá bán</th>
                                <th>Cửa hàng</th>
                                <th>Trạng thái</th>
                                <th style="width: 120px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <img src="{{ $item->cover_image_url }}" alt="{{ $item->productVariant->name }}" class="w-14 h-14 object-cover rounded-md">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.trade-in-items.edit', $item) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $item->productVariant->name }}</a>
                                        <p class="text-xs text-gray-500">{{ $item->type == 'used' ? 'Máy đã qua sử dụng' : 'Hàng mở hộp' }}</p>
                                    </td>
                                    <td>
                                        <p class="font-medium">{{ $item->sku }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->imei_or_serial }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-custom 
                                            @if($item->condition_grade == 'A') badge-success @endif
                                            @if($item->condition_grade == 'B') badge-warning @endif
                                            @if($item->condition_grade == 'C') badge-danger @endif
                                        ">{{ $item->condition_grade }}</span>
                                    </td>
                                    <td class="font-semibold text-red-600">{{ number_format($item->selling_price, 0, ',', '.') }} ₫</td>
                                    <td>{{ $item->storeLocation->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge-custom 
                                            @if($item->status == 'available') badge-success @endif
                                            @if($item->status == 'pending_inspection') badge-info @endif
                                            @if($item->status == 'sold') badge-secondary @endif
                                        ">{{ \App\Enums\TradeInStatusEnum::getDescription($item->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="inline-flex space-x-1">
                                            <a href="{{ route('admin.trade-in-items.edit', $item) }}" class="btn btn-primary btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('admin.trade-in-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-10 text-gray-500">
                                        <p>Không tìm thấy sản phẩm nào.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if ($items->hasPages())
            <div class="card-custom-footer">
                 <div class="flex justify-between items-center">
                    <p class="text-sm text-gray-700">
                        Hiển thị từ <strong>{{ $items->firstItem() }}</strong> đến <strong>{{ $items->lastItem() }}</strong> trên <strong>{{ $items->total() }}</strong> kết quả
                    </p>
                    {{ $items->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
