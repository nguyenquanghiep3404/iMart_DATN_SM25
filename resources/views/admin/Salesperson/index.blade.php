@extends('admin.layouts.app')

@section('title', 'Quản Lý Nhân Viên Bán Hàng - POS')

@push('styles')
<style>
/* Custom pagination styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.pagination > * {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    background-color: white;
    color: #374151;
    text-decoration: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.pagination > *:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
}

.pagination .active {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pagination .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination .disabled:hover {
    background-color: white;
    border-color: #d1d5db;
}
</style>
@endpush

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Quản Lý Nhân Viên Bán Hàng - POS</h1>
            <p class="text-gray-500">Quản lý nhân viên và lịch làm việc tại các cửa hàng</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sales-staff.work-shifts.index') }}" class="flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline></svg>
                Quản Lý Ca Làm Việc
            </a>
        </div>
    </div>

    <!-- Combined Filter and Table Block -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 flex-wrap">
                <select id="province-filter" class="w-full sm:w-56 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tất cả Tỉnh/Thành</option>
                    @foreach($provinces ?? [] as $province)
                        <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                    @endforeach
                </select>
                <select id="district-filter" class="w-full sm:w-56 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" disabled>
                    <option value="">Tất cả Quận/Huyện</option>
                </select>
                <div class="relative flex-grow">
                    <input id="store-search-input" type="text" placeholder="Tìm theo tên cửa hàng..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <label for="per-page-select" class="text-sm font-medium text-gray-700">Hiển thị:</label>
                    <select id="per-page-select" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                {{-- <button id="view-trash-btn" class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 font-semibold px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <span>Thùng rác</span>
                </button> --}}
                <button id="add-staff-btn-global" class="flex items-center gap-2 bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Thêm Nhân Viên
                </button>
            </div>
        </div>
        
        <!-- Store Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Tên Cửa Hàng</th>
                        <th scope="col" class="px-6 py-3">Quận/Huyện</th>
                        <th scope="col" class="px-6 py-3">Tỉnh/Thành Phố</th>
                        <th scope="col" class="px-6 py-3">Nhân Viên</th>
                        <th scope="col" class="px-6 py-3 text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody id="store-table-body">
                    @forelse($stores ?? [] as $item)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('admin.sales-staff.stores.employees', $item->id) }}" class="hover:text-indigo-700">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ $item->district->name_with_type ?? '' }}</td>
                            <td class="px-6 py-4">{{ $item->province->name_with_type ?? '' }}</td>
                            <td class="px-6 py-4">{{ $item->assignedUsers->count() }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('admin.sales-staff.stores.employees', $item->id) }}" class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition-colors" title="Xem chi tiết">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V8a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v4"/><path d="M2 20v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/><path d="M12 14v-2"/><path d="M20 14h.01"/><path d="M4 14h.01"/></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy cửa hàng</h3>
                                    <p class="mt-1 text-sm text-gray-500">Vui lòng thay đổi bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($stores->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                    <p class="text-sm text-gray-700 leading-5">
                        Hiển thị từ
                        <span class="font-medium">{{ $stores->firstItem() }}</span>
                        đến
                        <span class="font-medium">{{ $stores->lastItem() }}</span>
                        trên tổng số
                        <span class="font-medium">{{ $stores->total() }}</span>
                        cửa hàng
                    </p>
                </div>
                <div>
                    {!! $stores->appends([
                        'per_page' => request('per_page'),
                    ])->links() !!}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Staff Modal -->
@include('admin.Salesperson.partials.add_staff_modal')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Per page selector functionality
    const perPageSelect = document.getElementById('per-page-select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const perPage = this.value;
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('per_page', perPage);
            currentUrl.searchParams.delete('page'); // Reset to first page
            window.location.href = currentUrl.toString();
        });
    }
    
    // Filter functionality
    const provinceFilter = document.getElementById('province-filter');
    const districtFilter = document.getElementById('district-filter');
    const storeSearchInput = document.getElementById('store-search-input');
    
    // Load districts when province changes
    provinceFilter.addEventListener('change', function() {
        const provinceCode = this.value;
        if (provinceCode) {
            fetch(`/api/locations/old/districts/${provinceCode}`)
                .then(response => response.json())
                .then(data => {
                    districtFilter.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    if (data.success && data.data) {
                        data.data.forEach(district => {
                            districtFilter.innerHTML += `<option value="${district.code}">${district.name_with_type}</option>`;
                        });
                    }
                    districtFilter.disabled = false;
                });
        } else {
            districtFilter.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
            districtFilter.disabled = true;
        }
        filterStores();
    });
    
    districtFilter.addEventListener('change', filterStores);
    storeSearchInput.addEventListener('input', filterStores);
    
    function filterStores() {
        const province = provinceFilter.value;
        const district = districtFilter.value;
        const search = storeSearchInput.value;
        const perPage = perPageSelect ? perPageSelect.value : 10;
        
        // Show loading state
        const tbody = document.getElementById('store-table-body');
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">Đang tải...</td></tr>';
        
        // Fetch filtered data
        fetch(`{{ route('admin.sales-staff.api.stores') }}?province=${province}&district=${district}&search=${search}&per_page=${perPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.stores.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V8a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v4"/><path d="M2 20v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/><path d="M12 14v-2"/><path d="M20 14h.01"/><path d="M4 14h.01"/></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy cửa hàng</h3>
                                    <p class="mt-1 text-sm text-gray-500">Vui lòng thay đổi bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = data.stores.map(store => `
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="/admin/sales-staff/stores/${store.id}/employees" class="hover:text-indigo-700">
                                    ${store.name}
                                </a>
                            </td>
                            <td class="px-6 py-4">${store.district}</td>
                            <td class="px-6 py-4">${store.province}</td>
                            <td class="px-6 py-4">${store.staff_count}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="/admin/sales-staff/stores/${store.id}/employees" class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition-colors" title="Xem chi tiết">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                }
                
                // Update pagination info if available
                if (data.pagination) {
                    updatePaginationInfo(data.pagination);
                }
            });
    }
    
    function updatePaginationInfo(pagination) {
        // This function can be used to update pagination display if needed
        console.log('Pagination info:', pagination);
    }
    
    // Xóa toàn bộ đoạn JS modal cũ
    // Modal functionality đã được chuyển sang partials/add_staff_modal.blade.php
});
</script>
@endpush
