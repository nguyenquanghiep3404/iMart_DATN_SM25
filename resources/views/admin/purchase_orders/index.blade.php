@extends('admin.layouts.app')

@section('title', 'Quản lý Phiếu Nhập Kho')

@push('styles')
    {{-- CSS cho các huy hiệu trạng thái, có thể đưa vào file CSS chung nếu muốn --}}
     <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Changed to match my style */
        }

        /* My consistent card, button, form styles */
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
        .form-input, .form-select {
            width: 100%;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            background-color: white;
        }
        .form-input:focus, .form-select:focus {
            border-color: #4f46e5;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(79,70,229,.25);
        }
        .table-custom { width: 100%; min-width: 800px; color: #374151; }
        .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; white-space: nowrap; }
        .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; }
        
        /* Original status badge styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-completed { background-color: #D1FAE5; color: #065F46; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <!-- Page Header -->
        <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Phiếu Nhập Kho</h1>
                <p class="text-gray-600 mt-1">Theo dõi và quản lý tất cả các đơn hàng nhập từ nhà cung cấp.</p>
            </div>
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary mt-4 sm:mt-0 w-full sm:w-auto">
                <i class="fas fa-plus mr-2"></i>
                Tạo Phiếu Nhập Mới
            </a>
        </header>

        <!-- Main Content Card -->
        <div class="card-custom">
            <div class="card-custom-body">
                <!-- Filter and Search Bar -->
                <form action="{{ route('admin.purchase-orders.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                placeholder="Tìm theo mã phiếu, nhà cung cấp..." class="form-input">
                        </div>
                        <div>
                            <label for="filter_location" class="block text-sm font-medium text-gray-700 mb-1">Kho nhận</label>
                            <select id="filter_location" name="location_id" class="form-select">
                                <option value="">Tất cả kho</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="filter_status" name="status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                @foreach ($statuses as $key => $value)
                                     <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-x-3 pt-4">
                        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Xóa lọc</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Lọc</button>
                    </div>
                </form>
            </div>

            <!-- Purchase Orders Table -->
            <div class="overflow-x-auto border-t border-gray-200">
                <table class="table-custom w-full">
                    <thead>
                        <tr>
                            <th>Mã Phiếu</th>
                            <th>Nhà Cung Cấp</th>
                            <th>Kho Nhận</th>
                            <th>Ngày Tạo</th>
                            <th class="text-center">Trạng Thái</th>
                            <th class="text-right">Tổng Tiền</th>
                            <th class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50">
                            <td class="font-mono text-gray-800">{{ $po->po_code }}</td>
                            <td class="font-medium text-gray-900">{{ $po->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $po->storeLocation->name ?? 'N/A' }}</td>
                            <td>{{ $po->order_date->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @php
                                    $statusClass = 'status-default'; // Mặc định
                                    if ($po->status == 'pending') $statusClass = 'status-pending';
                                    elseif ($po->status == 'completed') $statusClass = 'status-completed';
                                    elseif ($po->status == 'cancelled') $statusClass = 'status-cancelled';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statuses[$po->status] ?? ucfirst($po->status) }}
                                </span>
                            </td>
                            <td class="text-right font-medium">
                                {{ number_format($po->total_amount, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="btn btn-primary btn-sm" title="Xem & Nhập kho">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- Bạn có thể thêm các nút khác như Xóa ở đây nếu cần --}}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-gray-400"></i>
                                    <p class="text-lg font-medium">Không tìm thấy phiếu nhập kho nào.</p>
                                    <p class="text-sm">Hãy thử điều chỉnh bộ lọc hoặc <a href="{{ route('admin.purchase-orders.create') }}" class="text-indigo-600 hover:underline">tạo một phiếu nhập mới</a>.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($purchaseOrders->hasPages())
            <div class="card-custom-footer flex flex-col sm:flex-row items-center justify-between">
                <span class="text-sm text-gray-700 mb-2 sm:mb-0">
                    Hiển thị <span class="font-semibold text-gray-900">{{ $purchaseOrders->firstItem() }}</span> đến <span class="font-semibold text-gray-900">{{ $purchaseOrders->lastItem() }}</span> của <span class="font-semibold text-gray-900">{{ $purchaseOrders->total() }}</span> Phiếu nhập
                </span>
                <div>
                    {{-- Nối các tham số filter vào link phân trang --}}
                    {!! $purchaseOrders->appends(request()->query())->links() !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script tùy chỉnh nếu có, ví dụ: cho modal xác nhận xóa --}}
@endpush
