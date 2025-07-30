@extends('admin.layouts.app')

@section('title', 'Quản lý Phiếu Chuyển Kho')

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
        .status-shipped { background-color: #DBEAFE; color: #1E40AF; } /* Blue */
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
                <h1 class="text-3xl font-bold text-gray-800">Phiếu Chuyển Kho</h1>
                <p class="text-gray-600 mt-1">Theo dõi và quản lý tất cả các phiếu chuyển kho giữa các chi nhánh.</p>
            </div>
            <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                <a href="{{-- {{ route('admin.stock-transfers.create') }} --}}" class="btn btn-primary w-full sm:w-auto">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo Phiếu Chuyển Mới
                </a>
            </div>
        </header>

        <div class="card-custom">
            <div class="card-custom-body p-6">
                <form action="{{ route('admin.stock-transfers.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="lg:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                   placeholder="Tìm theo mã phiếu chuyển..." class="form-input">
                        </div>
                        <div>
                            <label for="from_location_id" class="block text-sm font-medium text-gray-700 mb-1">Kho Gửi</label>
                            <select id="from_location_id" name="from_location_id" class="form-select">
                                <option value="">Tất cả kho</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('from_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="to_location_id" class="block text-sm font-medium text-gray-700 mb-1">Kho Nhận</label>
                            <select id="to_location_id" name="to_location_id" class="form-select">
                                <option value="">Tất cả kho</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('to_location_id') == $location->id ? 'selected' : '' }}>
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
                        <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-secondary">Xóa lọc</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Lọc</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto border-t border-gray-200">
                <table class="table-custom w-full">
                    <thead>
                        <tr>
                            <th>Mã Phiếu</th>
                            <th>Kho Gửi</th>
                            <th>Kho Nhận</th>
                            <th>Người Tạo</th>
                            <th>Ngày Tạo</th>
                            <th class="text-center">Trạng Thái</th>
                            <th class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockTransfers as $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="font-mono text-gray-800">{{ $transfer->transfer_code }}</td>
                            <td class="font-medium text-gray-900">{{ $transfer->fromLocation->name ?? 'N/A' }}</td>
                            <td class="font-medium text-gray-900">{{ $transfer->toLocation->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->createdBy->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @php
                                    $statusClass = 'status-default'; // Mặc định
                                    if ($transfer->status == 'pending') $statusClass = 'status-pending';
                                    elseif ($transfer->status == 'shipped') $statusClass = 'status-shipped';
                                    elseif ($transfer->status == 'received') $statusClass = 'status-received';
                                    elseif ($transfer->status == 'cancelled') $statusClass = 'status-cancelled';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statuses[$transfer->status] ?? ucfirst($transfer->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-x-2">
                                    <a href="{{-- {{ route('admin.stock-transfers.show', $transfer->id) }} --}}" class="btn btn-primary btn-sm" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{-- {{ route('admin.stock-transfers.edit', $transfer->id) }} --}}" class="btn btn-secondary btn-sm" title="Sửa phiếu">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-exchange-alt fa-3x mb-3 text-gray-400"></i>
                                    <p class="text-lg font-medium">Không tìm thấy phiếu chuyển kho nào.</p>
                                    <p class="text-sm">Hãy thử điều chỉnh bộ lọc hoặc <a href="{{-- {{ route('admin.stock-transfers.create') }} --}}" class="text-indigo-600 hover:underline">tạo một phiếu chuyển mới</a>.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($stockTransfers->hasPages())
            <div class="card-custom-footer flex flex-col sm:flex-row items-center justify-between p-4 border-t border-gray-200">
                <span class="text-sm text-gray-700 mb-2 sm:mb-0">
                    Hiển thị <span class="font-semibold text-gray-900">{{ $stockTransfers->firstItem() }}</span> đến <span class="font-semibold text-gray-900">{{ $stockTransfers->lastItem() }}</span> của <span class="font-semibold text-gray-900">{{ $stockTransfers->total() }}</span> Phiếu chuyển
                </span>
                <div>
                    {!! $stockTransfers->appends(request()->query())->links() !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
