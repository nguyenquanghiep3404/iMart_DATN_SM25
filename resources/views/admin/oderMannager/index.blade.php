@extends('admin.layouts.app')
@section('content')
<div class="p-4 md:p-8 bg-gray-100 min-h-screen">
    <div class="max-w-screen-2xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Quản Lý Nhân viên Đơn hàng</h1>
            <p class="text-gray-500 mt-1">Tổng quan, tìm kiếm kho và quản lý các nhân viên xử lý đơn hàng.</p>
        </div>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div class="flex gap-4 w-full md:w-auto items-center">
                <select id="province-filter" class="px-4 py-2 rounded-lg border border-gray-300 bg-white min-w-[180px] focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tất cả Tỉnh/Thành</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                    @endforeach
                </select>
                <div class="relative w-full md:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input id="warehouse-search-input" type="text" placeholder="Tìm theo tên kho..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 w-full focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <a href="{{ route('admin.order-manager.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Thêm nhân viên</span>
            </a>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-800">
                    <thead class="bg-gray-50">
                        <tr class="text-left font-semibold text-gray-600">
                            <th class="px-6 py-3">TÊN KHO</th>
                            <th class="px-6 py-3">TỈNH/THÀNH PHỐ</th>
                            <th class="px-6 py-3">SỐ LƯỢNG NHÂN VIÊN</th>
                            <th class="px-6 py-3 text-right">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr class="bg-white border-b last:border-b-0 hover:bg-gray-50" data-province-code="{{ $warehouse->province->code ?? '' }}">
                                <td class="px-6 py-4 font-medium">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-600">
                                            <i class="fas fa-warehouse text-indigo-600"></i>
                                        </span>
                                        <div>
                                            <div class="font-semibold text-base text-gray-800">{{ $warehouse->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $warehouse->address }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $warehouse->province->name_with_type ?? '' }}</div>
                                    <div class="text-xs text-gray-500">{{ $warehouse->district->name_with_type ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-indigo-600 text-lg">{{ $warehouse->orderManagers_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.order-manager.warehouse.show', $warehouse->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold">
                                        <i class="fas fa-eye"></i>
                                        <span>Xem chi tiết</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center px-6 py-6 text-gray-500">Không có kho nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const warehouseSearchInput = document.getElementById('warehouse-search-input');
        const provinceFilter = document.getElementById('province-filter');
        // Lưu trữ tất cả warehouses để filter
        const allWarehouses = [];
        document.querySelectorAll('tbody tr').forEach(row => {
            const warehouseName = row.querySelector('td:first-child .font-bold').textContent;
            const provinceName = row.querySelector('td:nth-child(2) .font-semibold').textContent;
            const provinceCode = row.getAttribute('data-province-code');
            allWarehouses.push({
                row: row,
                name: warehouseName,
                province: provinceName,
                provinceCode: provinceCode
            });
        });
        // Filter theo tên kho
        if (warehouseSearchInput) {
            warehouseSearchInput.addEventListener('input', function() {
                filterWarehouses();
            });
        }
        // Filter theo tỉnh/thành
        if (provinceFilter) {
            provinceFilter.addEventListener('change', function() {
                filterWarehouses();
            });
        }
        // Hàm filter tổng hợp
        function filterWarehouses() {
            const searchTerm = warehouseSearchInput ? warehouseSearchInput.value.toLowerCase() : '';
            const selectedProvinceCode = provinceFilter ? provinceFilter.value : '';
            allWarehouses.forEach(warehouse => {
                const nameMatch = !searchTerm || warehouse.name.toLowerCase().includes(searchTerm);
                const provinceMatch = !selectedProvinceCode || warehouse.provinceCode === selectedProvinceCode;
                if (nameMatch && provinceMatch) {
                    warehouse.row.style.display = '';
                } else {
                    warehouse.row.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
@endsection
