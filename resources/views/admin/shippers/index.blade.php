@extends('admin.layouts.app')

@section('title', 'Quản lý Nhân viên Giao hàng')

@push('styles')
<style>
    .status-badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-active { background-color: #dcfce7; color: #16a34a; }
    .status-inactive { background-color: #f3f4f6; color: #4b5563; }
    .status-banned { background-color: #fee2e2; color: #dc2626; }
</style>
@endpush

@section('content')
<div class="max-w-screen-2xl mx-auto p-4 md:p-8">
    <header class="mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhân viên Giao hàng</h1>
            <p class="text-gray-500 mt-1">Tổng quan, tìm kiếm kho và quản lý các tài xế.</p>
        </div>
    </header>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4 flex-wrap">
                    <select id="province-filter" class="w-full sm:w-56 py-2.5 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-sm">
                        <option value="">Tất cả Tỉnh/Thành</option>
                        @foreach($provinces ?? [] as $province)
                            <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                        @endforeach
                    </select>
                    <div class="relative flex-grow">
                        <input id="warehouse-search-input" type="text" placeholder="Tìm theo tên kho..." class="w-full sm:w-64 pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-normal text-sm">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="p-6 font-semibold tracking-wide">TÊN KHO</th>
                        <th class="p-6 font-semibold tracking-wide">TỈNH/THÀNH PHỐ</th>
                        <th class="p-6 font-semibold tracking-wide">SỐ LƯỢNG NHÂN VIÊN</th>
                        <th class="p-6 text-center font-semibold tracking-wide">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                    <tr class="bg-white border-b last:border-b-0 hover:bg-gray-50">
                        <td class="p-6">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-warehouse text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-base">{{ $warehouse->name }}</div>
                                    <div class="text-gray-500 text-sm">{{ $warehouse->address }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="font-medium text-gray-800 text-sm">{{ $warehouse->province->name_with_type ?? '' }}</div>
                            <div class="text-gray-500 text-sm">{{ $warehouse->district->name_with_type ?? '' }}</div>
                        </td>
                        <td class="p-6">
                            <div class="font-bold text-indigo-600 text-xl">{{ $warehouse->shipper_count }}</div>
                        </td>
                        <td class="p-6 text-center">
                            <a href="{{ route('admin.shippers.warehouse.show', $warehouse) }}" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 font-medium text-sm">
                                <i class="fas fa-eye mr-2"></i>
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center p-12 text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-warehouse text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Không tìm thấy kho nào</h3>
                                <p class="text-gray-500">Vui lòng kiểm tra lại bộ lọc hoặc thêm kho mới.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $warehouses->appends(request()->query())->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSearchInput = document.getElementById('warehouse-search-input');
    if (warehouseSearchInput) {
        warehouseSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const warehouseName = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
                if (warehouseName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    const provinceFilter = document.getElementById('province-filter');
    if (provinceFilter) {
        provinceFilter.addEventListener('change', function() {
            const selectedProvince = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const provinceName = row.querySelector('td:nth-child(2) .font-medium').textContent.toLowerCase();
                if (!selectedProvince || provinceName.includes(selectedProvince)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endpush
