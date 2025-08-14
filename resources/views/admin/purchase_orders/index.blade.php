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
        .status-pending, .status-waiting_for_scan { background-color: #FEF3C7; color: #92400E; } /* Yellow */
        .status-completed, .status-received { background-color: #D1FAE5; color: #065F46; } /* Green */
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; } /* Red */
        .status-default { background-color: #E5E7EB; color: #4B5563; } /* Gray */
     </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <header class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Phiếu Nhập Kho</h1>
                <p class="text-gray-600 mt-1">Theo dõi và quản lý tất cả các đơn hàng nhập từ nhà cung cấp.</p>
            </div>
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <a href="{{ route('admin.purchase-orders.receiving.index') }}" class="btn btn-success w-full sm:w-auto">
                    <i class="fas fa-dolly-flatbed mr-2"></i>
                    <span>Tiếp Nhận</span>
                </a>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary w-full sm:w-auto">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Tạo Mới</span>
                </a>
            </div>
        </header>

        <div class="card-custom">
            <div class="p-4">
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

            <!-- DESKTOP TABLE VIEW -->
            <div class="overflow-x-auto border-t border-gray-200 hidden lg:block">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã Phiếu</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhà Cung Cấp</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho Nhận</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày Tạo</th>
                            <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                            <th class="p-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng Tiền</th>
                            <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 whitespace-nowrap font-mono text-indigo-600 font-semibold">{{ $po->po_code }}</td>
                            <td class="p-3 whitespace-nowrap font-medium text-gray-900">{{ $po->supplier->name ?? 'N/A' }}</td>
                            <td class="p-3 whitespace-nowrap">{{ $po->storeLocation->name ?? 'N/A' }}</td>
                            <td class="p-3 whitespace-nowrap">{{ $po->order_date->format('d/m/Y') }}</td>
                            <td class="p-3 whitespace-nowrap text-center">
                                @php
                                    $statusClass = 'status-' . ($po->status ?? 'default');
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statuses[$po->status] ?? ucfirst($po->status) }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right font-medium">
                                {{ number_format($po->total_amount, 0, ',', '.') }} ₫
                            </td>
                            <td class="p-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-x-2">
                                    @if(in_array($po->status, ['pending', 'waiting_for_scan']))
                                        <a href="{{ route('admin.purchase-orders.receiving.index', ['po_id' => $po->id]) }}" class="btn btn-success btn-sm" title="Tiếp nhận hàng hóa">
                                            <i class="fas fa-qrcode mr-1"></i> Nhận Hàng
                                        </a>
                                        <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="btn btn-secondary btn-sm" title="Sửa phiếu">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-secondary btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
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

            <!-- MOBILE CARD VIEW -->
            <div class="grid grid-cols-1 gap-4 p-4 lg:hidden border-t border-gray-200">
                @forelse ($purchaseOrders as $po)
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200 space-y-3">
                        <div class="flex justify-between items-start">
                            <span class="font-mono font-bold text-indigo-600">{{ $po->po_code }}</span>
                            @php
                                $statusClass = 'status-' . ($po->status ?? 'default');
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statuses[$po->status] ?? ucfirst($po->status) }}
                            </span>
                        </div>
                        <div class="text-sm space-y-2">
                            <p><strong class="text-gray-600">Nhà Cung Cấp:</strong> {{ $po->supplier->name ?? 'N/A' }}</p>
                            <p><strong class="text-gray-600">Kho Nhận:</strong> {{ $po->storeLocation->name ?? 'N/A' }}</p>
                            <p><strong class="text-gray-600">Ngày Tạo:</strong> {{ $po->order_date->format('d/m/Y') }}</p>
                            <p><strong class="text-gray-600">Tổng Tiền:</strong> <span class="font-semibold text-gray-800">{{ number_format($po->total_amount, 0, ',', '.') }} ₫</span></p>
                        </div>
                        <div class="flex items-center justify-end gap-x-2 border-t pt-3 mt-3">
                             @if(in_array($po->status, ['pending', 'waiting_for_scan']))
                                <a href="{{ route('admin.purchase-orders.receiving.index', ['po_id' => $po->id]) }}" class="btn btn-success btn-sm flex-grow" title="Tiếp nhận hàng hóa">
                                    <i class="fas fa-qrcode mr-2"></i> Nhận Hàng
                                </a>
                                <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="btn btn-secondary btn-sm" title="Sửa phiếu">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            @else
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-secondary btn-sm flex-grow" title="Xem chi tiết">
                                    <i class="fas fa-eye mr-2"></i> Xem
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-gray-400"></i>
                            <p class="text-lg font-medium">Không tìm thấy phiếu nhập kho nào.</p>
                            <p class="text-sm">Hãy thử điều chỉnh bộ lọc hoặc <a href="{{ route('admin.purchase-orders.create') }}" class="text-indigo-600 hover:underline">tạo một phiếu nhập mới</a>.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($purchaseOrders->hasPages())
            <div class="p-4 border-t border-gray-200">
                {!! $purchaseOrders->appends(request()->query())->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
