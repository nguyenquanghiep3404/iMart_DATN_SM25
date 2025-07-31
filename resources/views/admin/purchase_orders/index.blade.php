@extends('admin.layouts.app')

@section('title', 'Quản lý Phiếu Nhập Kho')

@push('styles')
    {{-- CSS cho các huy hiệu trạng thái --}}
     <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
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
        .btn-success { background-color: #10b981; color: white; } /* Green button */
        .btn-success:hover { background-color: #059669; }
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
        .status-pending { background-color: #FEF3C7; color: #92400E; } /* Yellow */
        .status-received { background-color: #D1FAE5; color: #065F46; } /* Green */
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; } /* Red */
        .status-default { background-color: #E5E7EB; color: #4B5563; } /* Gray */
     </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Phiếu Nhập Kho</h1>
                <p class="text-gray-600 mt-1">Theo dõi và quản lý tất cả các đơn hàng nhập từ nhà cung cấp.</p>
            </div>
            <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                 {{-- Button điều hướng đến trang tiếp nhận hàng --}}
                <a href="{{ route('admin.purchase-orders.receiving.index') }}" class="btn btn-success w-full sm:w-auto">
                    <i class="fas fa-dolly-flatbed mr-2"></i>
                    Đi đến Tiếp Nhận
                </a>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary w-full sm:w-auto">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo Phiếu Nhập Mới
                </a>
            </div>
        </header>

        <div class="card-custom">
            <div class="card-custom-body">
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
                                    elseif ($po->status == 'received') $statusClass = 'status-received';
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
                                <div class="flex items-center justify-center gap-x-2">
                                    {{-- ================== CHANGE START ================== --}}
                                    @if($po->status == 'pending')
                                        {{-- Nếu đang chờ, hiển thị nút "Nhận Hàng" --}}
                                        <a href="{{ route('admin.purchase-orders.receiving.index') }}" class="btn btn-success btn-sm" title="Tiếp nhận hàng hóa">
                                            <i class="fas fa-qrcode mr-1"></i> Nhận Hàng
                                        </a>
                                    @else
                                        {{-- Nếu đã xử lý, hiển thị nút xem/sửa thông thường --}}
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-primary btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="btn btn-secondary btn-sm" title="Sửa phiếu">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endif
                                    {{-- =================== CHANGE END =================== --}}
                                </div>
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

            @if ($purchaseOrders->hasPages())
            <div class="card-custom-footer flex flex-col sm:flex-row items-center justify-between">
                <span class="text-sm text-gray-700 mb-2 sm:mb-0">
                    Hiển thị <span class="font-semibold text-gray-900">{{ $purchaseOrders->firstItem() }}</span> đến <span class="font-semibold text-gray-900">{{ $purchaseOrders->lastItem() }}</span> của <span class="font-semibold text-gray-900">{{ $purchaseOrders->total() }}</span> Phiếu nhập
                </span>
                <div>
                    {!! $purchaseOrders->appends(request()->query())->links() !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection