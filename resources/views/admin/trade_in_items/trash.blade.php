@extends('admin.layouts.app')

@section('title', 'Thùng rác - Máy Cũ & Mở Hộp')

@push('styles')
<style>
    /* Sử dụng lại CSS từ trang index */
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
    .btn-secondary { background-color: #e5e7eb; color: #374151; border-color: #d1d5db; }
    .btn-secondary:hover { background-color: #d1d5db; }
    .btn-success { background-color: #10b981; color: white; }
    .btn-success:hover { background-color: #059669; }
    .btn-danger { background-color: #ef4444; color: white; }
    .btn-danger:hover { background-color: #dc2626; }
    .table-custom { width: 100%; min-width: 800px; color: #374151; }
    .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; white-space: nowrap; }
    .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <!-- PAGE HEADER -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thùng rác - Máy Cũ & Mở Hộp</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.trade-in-items.index') }}" class="text-indigo-600 hover:text-indigo-800">Máy Cũ & Mở Hộp</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                </ol>
            </nav>
        </header>

        <div class="card-custom">
            <div class="card-custom-header">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="card-custom-title">Danh sách đã xóa ({{ $items->total() }})</h3>
                    <a href="{{ route('admin.trade-in-items.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
                    </a>
                </div>
            </div>

            <div class="card-custom-body">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Tên Sản Phẩm</th>
                                <th>SKU / IMEI</th>
                                <th class="hidden md:table-cell">Cửa hàng</th>
                                <th>Ngày xóa</th>
                                <th style="width: 150px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        @php
                                            $baseName = optional($item->productVariant->product)->name;
                                            $fullName = $baseName ?: 'Sản phẩm không xác định';
                                        @endphp
                                        <p class="font-semibold text-gray-800">{{ $fullName }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->type === 'used' ? '(Máy đã qua sử dụng)' : '(Hàng mở hộp)' }}</p>
                                    </td>
                                    <td>
                                        <p class="font-medium">{{ $item->sku }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->imei_or_serial }}</p>
                                    </td>
                                    <td class="hidden md:table-cell">{{ $item->storeLocation->name ?? 'N/A' }}</td>
                                    <td>{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="inline-flex space-x-1">
                                            <!-- Form Khôi phục -->
                                            <form action="{{ route('admin.trade-in-items.restore', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" title="Khôi phục">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            </form>
                                            <!-- Form Xóa vĩnh viễn -->
                                            <form action="{{ route('admin.trade-in-items.force-delete', $item->id) }}" method="POST" onsubmit="return confirm('HÀNH ĐỘNG NÀY KHÔNG THỂ HOÀN TÁC! Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Xóa vĩnh viễn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500">
                                        <p>Thùng rác trống.</p>
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
