@extends('admin.layouts.app')

@section('title', 'Nhân Viên - ' . $store->name)

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
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sales-staff.index') }}" class="p-2 rounded-md hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Nhân viên tại: {{ $store->name }}</h1>
                <p class="text-gray-500">{{ $store->district->name_with_type ?? '' }}, {{ $store->province->name_with_type ?? '' }}</p>
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
            <a href="{{ route('admin.sales-staff.stores.schedule', $store->id) }}" class="flex items-center gap-2 bg-teal-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Quản Lý Lịch Làm Việc
            </a>
            <button id="add-staff-btn-in-view" data-store-id="{{ $store->id }}" class="flex items-center gap-2 bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                Thêm Nhân Viên
            </button>
        </div>
    </div>
    
    <!-- Combined Employee Filter and Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Employee Filter Bar -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <input id="employee-search-input" type="text" placeholder="Tìm theo tên, email, sđt..." class="pl-10 pr-4 py-2 border rounded-lg w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16 text-center">STT</th>
                        <th scope="col" class="px-6 py-3">Họ và Tên</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Số Điện Thoại</th>
                        <th scope="col" class="px-6 py-3">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody id="staff-table-body">
                    @forelse($employees ?? [] as $index => $employee)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900 text-center">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $employee['name'] }}</td>
                            <td class="px-6 py-4">{{ $employee['email'] }}</td>
                            <td class="px-6 py-4">{{ $employee['phone'] }}</td>
                            <td class="px-6 py-4">
                                @if($employee['status'] == 'active')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Đang hoạt động</span>
                                @elseif($employee['status'] == 'inactive')
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Không hoạt động</span>
                                @elseif($employee['status'] == 'banned')
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Đã khóa</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button class="edit-btn bg-gray-200 text-gray-800 p-2 rounded-lg hover:bg-gray-300 transition-colors" title="Chỉnh sửa" data-id="{{ $employee['id'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </button>
                                    <button class="delete-btn bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition-colors" title="Xóa" data-id="{{ $employee['id'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có nhân viên</h3>
                                    <p class="mt-1 text-sm text-gray-500">Hãy thêm nhân viên đầu tiên cho cửa hàng này.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($employees->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                    <p class="text-sm text-gray-700 leading-5">
                        Hiển thị từ
                        <span class="font-medium">{{ $employees->firstItem() }}</span>
                        đến
                        <span class="font-medium">{{ $employees->lastItem() }}</span>
                        trên tổng số
                        <span class="font-medium">{{ $employees->total() }}</span>
                        nhân viên
                    </p>
                </div>
                <div>
                    {!! $employees->appends([
                        'per_page' => request('per_page'),
                    ])->links() !!}
                </div>
            </div>
        @endif
    </div>
</div>
<!-- Modal Thêm Nhân Viên -->
<div id="add-staff-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto" id="modal-content">
        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-900">Thêm Nhân Viên Mới</h3>
            <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Form -->
        <form id="add-staff-form" class="p-6">
            @csrf
            <input type="hidden" id="editing-staff-id" name="editing_staff_id" value="">
            <div id="staff-form-errors" class="mb-2"></div>
            
            <!-- 2 cột layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột trái -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Nhập họ và tên" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div id="error-name" class="text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div id="error-email" class="text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    @if(!$store)

                        <div>
                            <label for="modal-province-select" class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành Phố</label>
                            <select id="modal-province-select" name="province" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn Tỉnh/Thành Phố --</option>
                                @foreach($provinces ?? [] as $province)
                                    <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                                @endforeach
                            </select>
                            <div id="error-province" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành Phố</label>
                            <input type="text" value="{{ $store->province->name_with_type ?? '' }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                            <input type="hidden" name="province" value="{{ $store->province->code ?? '' }}">
                        </div>
                    @endif
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                            <option value="banned">Đã khóa</option>
                        </select>
                        <div id="error-status" class="text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số Điện Thoại <span class="text-danger">*</span></label>
                        <input type="text" id="phone" name="phone" placeholder="09xxxxxxxx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div id="error-phone" class="text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    @if(!$store)
                        <div>
                            <label for="modal-district-select" class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                            <select id="modal-district-select" name="district" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                            <div id="error-district" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                            <input type="text" value="{{ $store->district->name_with_type ?? '' }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                            <input type="hidden" name="district" value="{{ $store->district->code ?? '' }}">
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Field cửa hàng full width -->
            <div class="mt-6">
                @if(!$store)
                    <div>
                        <label for="modal-store-select" class="block text-sm font-medium text-gray-700 mb-1">Cửa Hàng *</label>
                        <select id="modal-store-select" name="store_location_id" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Chọn Cửa Hàng --</option>
                        </select>
                        <div id="error-store_location_id" class="text-red-600 text-sm mt-1"></div>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cửa Hàng</label>
                        <input type="text" value="{{ $store->name }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                        <input type="hidden" name="store_location_id" value="{{ $store->id }}">
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <button type="button" id="cancel-modal-btn" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Hủy
                </button>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Thêm Mới
                </button>
            </div>
        </form>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Cấu hình toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000", // Tự động ẩn sau 5 giây
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

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
    
    // Flag để kiểm soát validate
    let isEditingMode = false;
    
    // console.log('DEBUG: DOMContentLoaded event triggered');
    
    // Kiểm tra và hiển thị thông báo từ session storage
    const successMessage = sessionStorage.getItem('staff_success_message');
    const deleteSuccessMessage = sessionStorage.getItem('employee_delete_success_message');
    
    // console.log('DEBUG: Checking session storage for success message:', successMessage);
    if (successMessage) {
        // console.log('DEBUG: Found success message in session storage:', successMessage);
        // console.log('DEBUG: Displaying success message with toastr');
        toastr.success(successMessage);
        sessionStorage.removeItem('staff_success_message'); // Xóa thông báo sau khi hiển thị
        // console.log('DEBUG: Success message removed from session storage');
    } else {
        // console.log('DEBUG: No success message found in session storage');
    }
    
    // Kiểm tra và hiển thị thông báo xóa thành công
    if (deleteSuccessMessage) {
        toastr.success(deleteSuccessMessage);
        sessionStorage.removeItem('employee_delete_success_message'); // Xóa thông báo sau khi hiển thị
    }
    
    const employeeSearchInput = document.getElementById('employee-search-input');
    const staffTableBody = document.getElementById('staff-table-body');
    
    // Tìm kiếm nhân viên với API
    let searchTimeout;
    employeeSearchInput.addEventListener('input', function() {
        const searchTerm = this.value;
        const perPage = perPageSelect ? perPageSelect.value : 10;
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout for search (debounce)
        searchTimeout = setTimeout(() => {
            searchEmployees(searchTerm, perPage);
        }, 300);
    });
    
    function searchEmployees(searchTerm, perPage) {
        // Show loading state
        staffTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">Đang tải...</td></tr>';
        
        // Fetch filtered data
        fetch(`{{ route('admin.sales-staff.api.stores.employees', $store->id) }}?search=${searchTerm}&per_page=${perPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.employees.length === 0) {
                    staffTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy nhân viên</h3>
                                    <p class="mt-1 text-sm text-gray-500">Vui lòng thay đổi từ khóa tìm kiếm.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    staffTableBody.innerHTML = data.employees.map((employee, index) => `
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900 text-center">${data.pagination ? data.pagination.from + index : index + 1}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">${employee.name}</td>
                            <td class="px-6 py-4">${employee.email}</td>
                            <td class="px-6 py-4">${employee.phone}</td>
                            <td class="px-6 py-4">
                                ${employee.status === 'active' ? 
                                    '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Đang hoạt động</span>' :
                                    employee.status === 'inactive' ?
                                    '<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Không hoạt động</span>' :
                                    '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Đã khóa</span>'
                                }
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button class="edit-btn bg-gray-200 text-gray-800 p-2 rounded-lg hover:bg-gray-300 transition-colors" title="Chỉnh sửa" data-id="${employee.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </button>
                                    <button class="delete-btn bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition-colors" title="Xóa" data-id="${employee.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                }
            })
            .catch(error => {
                console.error('Error searching employees:', error);
                staffTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center py-8">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Lỗi tìm kiếm</h3>
                                <p class="mt-1 text-sm text-gray-500">Vui lòng thử lại sau.</p>
                            </div>
                        </td>
                    </tr>
                `;
            });
    }
    
    // Xóa nhân viên
    staffTableBody.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            const employeeId = deleteBtn.dataset.id;
            if (confirm('Bạn có chắc muốn xóa nhân viên này?')) {
                fetch(`/admin/sales-staff/api/stores/{{ $store->id }}/employees/${employeeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        // Lưu thông báo vào session storage để hiển thị sau khi reload
                        sessionStorage.setItem('employee_delete_success_message', data.message);
                        // Reload ngay lập tức
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error deleting employee:', error);
                    toastr.error('Có lỗi xảy ra khi xóa nhân viên. Vui lòng thử lại.');
                });
            }
        }
        
        // Sửa nhân viên
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            const employeeId = editBtn.dataset.id;
            openEditStaffModal(employeeId);
        }
    });
    
    // ===== MODAL FUNCTIONALITY =====
    // console.log('DEBUG: Bắt đầu tìm buttons...');
    
    // Nút mở modal
    const btnInView = document.getElementById('add-staff-btn-in-view');
    // console.log('DEBUG: btnInView =', btnInView);
    if (btnInView) {
        // console.log('DEBUG: Đã tìm thấy btnInView, đang gán event listener...');
        btnInView.addEventListener('click', function(e) {
            // console.log('DEBUG: Nút Thêm Nhân Viên đã được click!');
            openAddStaffModal();
        });
        // console.log('DEBUG: Đã gán event listener cho btnInView');
    } else {
        // console.log('DEBUG: KHÔNG TÌM THẤY btnInView!');
    }

    // Đóng modal khi click ra ngoài
    const modal = document.getElementById('add-staff-modal');
    // console.log('DEBUG: Modal element for outside click:', modal);
    if (modal) {
        // console.log('DEBUG: Adding outside click event listener to modal');
        modal.addEventListener('click', function(e) {
            // console.log('DEBUG: Modal clicked, target:', e.target, 'this:', this);
            if (e.target === this) {
                // console.log('DEBUG: Clicked outside modal, calling closeModal()');
                closeModal();
            }
        });
        // console.log('DEBUG: Outside click event listener added to modal');
    } else {
        // console.log('DEBUG: Modal element not found for outside click');
    }

    // Đóng modal khi click nút Hủy
    const cancelModalBtn = document.getElementById('cancel-modal-btn');
    // console.log('DEBUG: cancelModalBtn =', cancelModalBtn);
    if (cancelModalBtn) {
        // console.log('DEBUG: Đã tìm thấy cancelModalBtn, đang gán event listener...');
        cancelModalBtn.addEventListener('click', closeModal);
        // console.log('DEBUG: Đã gán event listener cho cancelModalBtn');
    } else {
        // console.log('DEBUG: KHÔNG TÌM THẤY cancelModalBtn!');
    }
    
    // Đóng modal khi click nút X
    const closeModalBtn = document.getElementById('close-modal-btn');
    // console.log('DEBUG: closeModalBtn =', closeModalBtn);
    if (closeModalBtn) {
        // console.log('DEBUG: Đã tìm thấy closeModalBtn, đang gán event listener...');
        closeModalBtn.addEventListener('click', closeModal);
        // console.log('DEBUG: Đã gán event listener cho closeModalBtn');
    } else {
        // console.log('DEBUG: KHÔNG TÌM THẤY closeModalBtn!');
    }
    
    // ===== DROPDOWN FUNCTIONALITY =====
    const modalProvinceSelect = document.getElementById('modal-province-select');
    const modalDistrictSelect = document.getElementById('modal-district-select');
    const modalStoreSelect = document.getElementById('modal-store-select');
    
    // Load districts when province changes
    if (modalProvinceSelect) {
        modalProvinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
            if (provinceCode) {
                fetch(`/api/locations/old/districts/${provinceCode}`)
                    .then(response => response.json())
                    .then(data => {
                        modalDistrictSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                        if (data.success && data.data) {
                            data.data.forEach(district => {
                                modalDistrictSelect.innerHTML += `<option value="${district.code}">${district.name_with_type}</option>`;
                            });
                        }
                        modalDistrictSelect.disabled = false;
                    });
            } else {
                modalDistrictSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                modalDistrictSelect.disabled = true;
            }
            // Reset store selection
            modalStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
            modalStoreSelect.disabled = true;
        });
    }
    
    // Load stores when district changes
    if (modalDistrictSelect) {
        modalDistrictSelect.addEventListener('change', function() {
            const districtCode = this.value;
            if (districtCode) {
                fetch(`{{ route('admin.sales-staff.api.stores') }}?district=${districtCode}`)
                    .then(response => response.json())
                    .then(data => {
                        modalStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                        if (data.stores && data.stores.length > 0) {
                            data.stores.forEach(store => {
                                modalStoreSelect.innerHTML += `<option value="${store.id}">${store.name}</option>`;
                            });
                        }
                        modalStoreSelect.disabled = false;
                    });
            } else {
                modalStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                modalStoreSelect.disabled = true;
            }
        });
    }
    
    // Form submit
    const addStaffForm = document.getElementById('add-staff-form');
    // console.log('DEBUG: addStaffForm =', addStaffForm);
    // console.log('DEBUG: addStaffForm type =', typeof addStaffForm);
    // console.log('DEBUG: addStaffForm tagName =', addStaffForm?.tagName);
    // console.log('DEBUG: addStaffForm className =', addStaffForm?.className);
    // console.log('DEBUG: addStaffForm action =', addStaffForm?.action);
    // console.log('DEBUG: addStaffForm method =', addStaffForm?.method);
    // console.log('DEBUG: addStaffForm id =', addStaffForm?.id);
    // console.log('DEBUG: addStaffForm onsubmit =', addStaffForm?.onsubmit);
    if (addStaffForm) {
        // console.log('DEBUG: Đã tìm thấy addStaffForm, đang gán event listener...');
        addStaffForm.addEventListener('submit', function(e) {
            // console.log('DEBUG: Form submit event triggered');
            // console.log('DEBUG: Form element:', this);
            // console.log('DEBUG: Event:', e);
            // console.log('DEBUG: Event type:', e.type);
            // console.log('DEBUG: Event target:', e.target);
            // console.log('DEBUG: Event currentTarget:', e.currentTarget);
            e.preventDefault();
            
            // Clear previous errors
            const errorFields = ['name','email','phone','store_location_id','province','district'];
            errorFields.forEach(field => {
                const el = document.getElementById('error-' + field);
                if (el) el.innerHTML = '';
            });
            // console.log('DEBUG: Cleared previous errors');
            
            // Validate required fields
            let hasError = false;
            const nameField = document.getElementById('name');
            const emailField = document.getElementById('email');
            const phoneField = document.getElementById('phone');
            
            const name = nameField?.value?.trim() || '';
            const email = emailField?.value?.trim() || '';
            const phone = phoneField?.value?.trim() || '';
            
            // Validate dropdown fields (chỉ khi không có store)
            let province = '', district = '', storeLocationId = '';
            const provinceInput = document.querySelector('input[name="province"]');
            const provinceSelect = document.getElementById('modal-province-select');
            const districtSelect = document.getElementById('modal-district-select');
            const storeSelect = document.getElementById('modal-store-select');
            
            if (!provinceInput) {
                province = provinceSelect?.value || '';
                district = districtSelect?.value || '';
                storeLocationId = storeSelect?.value || '';
            } else {
                province = provinceInput.value;
                district = document.querySelector('input[name="district"]').value;
                storeLocationId = document.querySelector('input[name="store_location_id"]').value;
            }
            
            // Chỉ validate khi field thực sự trống hoặc sai định dạng
            if (!name) {
                const errorNameEl = document.getElementById('error-name');
                if (errorNameEl) {
                    errorNameEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập họ và tên</div>';
                }
                hasError = true;
            }
            
            if (!email) {
                const errorEmailEl = document.getElementById('error-email');
                if (errorEmailEl) {
                    errorEmailEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập email</div>';
                }
                hasError = true;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                const errorEmailEl = document.getElementById('error-email');
                if (errorEmailEl) {
                    errorEmailEl.innerHTML = '<div class="text-red-600 text-sm">Email không đúng định dạng</div>';
                }
                hasError = true;
            }
            
            if (!phone) {
                const errorPhoneEl = document.getElementById('error-phone');
                if (errorPhoneEl) {
                    errorPhoneEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập số điện thoại</div>';
                }
                hasError = true;
            } else if (!/^[0-9]{10,11}$/.test(phone.replace(/\s/g, ''))) {
                const errorPhoneEl = document.getElementById('error-phone');
                if (errorPhoneEl) {
                    errorPhoneEl.innerHTML = '<div class="text-red-600 text-sm">Số điện thoại không đúng định dạng</div>';
                }
                hasError = true;
            }
            
            // Bỏ validate cho trạng thái vì mặc định đã là active
            
            // Validate dropdown fields (chỉ khi không có store)
            if (!provinceInput) {
                if (!province) {
                    const errorProvinceEl = document.getElementById('error-province');
                    if (errorProvinceEl) {
                        errorProvinceEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn tỉnh/thành phố</div>';
                    }
                    hasError = true;
                }
                
                if (!district) {
                    const errorDistrictEl = document.getElementById('error-district');
                    if (errorDistrictEl) {
                        errorDistrictEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn quận/huyện</div>';
                    }
                    hasError = true;
                }
                
                if (!storeLocationId) {
                    const errorStoreEl = document.getElementById('error-store_location_id');
                    if (errorStoreEl) {
                        errorStoreEl.innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn cửa hàng</div>';
                    }
                    hasError = true;
                }
            }
            
            if (hasError) {
                // console.log('DEBUG: Có lỗi validate, không submit form');
                return;
            }
            
            // console.log('DEBUG: Validate OK, đang submit form...');
            const formData = new FormData(this);
            

            
            // Đảm bảo tất cả các trường cần thiết được thêm vào formData
            // console.log('DEBUG: Adding missing fields to formData');
            if (!formData.has('name')) {
                formData.append('name', name);
                // console.log('DEBUG: Added name to formData');
            }
            if (!formData.has('email')) {
                formData.append('email', email);
                // console.log('DEBUG: Added email to formData');
            }
            if (!formData.has('phone')) {
                formData.append('phone', phone);
                // console.log('DEBUG: Added phone to formData');
            }
            if (!formData.has('status')) {
                formData.append('status', status);
                // console.log('DEBUG: Added status to formData');
            }
            
            // Debug: Log formData contents
            // console.log('DEBUG: FormData contents:');
            let formDataCount = 0;
            for (let [key, value] of formData.entries()) {
                // console.log('DEBUG: ' + key + ': ' + value);
                formDataCount++;
            }
            // console.log('DEBUG: Total formData entries:', formDataCount);
            
            const staffId = document.getElementById('editing-staff-id')?.value || '';
            const url = staffId ? `/admin/sales-staff/api/employees/${staffId}` : '{{ route('admin.sales-staff.api.employees.store') }}';
            const method = staffId ? 'POST' : 'POST'; // Đổi PUT thành POST cho cả hai trường hợp
            
            // Nếu đang sửa, thêm _method: PUT vào formData để Laravel hiểu đây là PUT request
            if (staffId) {
                formData.append('_method', 'PUT');
            }
            
            // console.log('DEBUG: Request details:', {
            //     staffId: staffId,
            //     url: url,
            //     method: method,
            //     hasStore: !!provinceInput,
            //     staffIdLength: staffId.length,
            //     urlLength: url.length
            // });
            
            // Debug: Log form data
            // console.log('DEBUG: Submitting form data:', {
            //     url: url,
            //     method: method,
            //     name: name,
            //     email: email,
            //     phone: phone,
            //     status: status,
            //     store_location_id: storeLocationId
            // });
            

            
            // console.log('DEBUG: Starting fetch request');
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                // console.log('DEBUG: Fetch response received');
                let data = {};
                const errorDiv = document.getElementById('staff-form-errors');
                
                try {
                    const text = await response.text();
                    // console.log('DEBUG: Response text:', text);
                    // console.log('DEBUG: Response text length:', text.length);
                    // console.log('DEBUG: Response text type:', typeof text);
                    data = text ? JSON.parse(text) : {};
                    // console.log('DEBUG: Parsed response data:', data);
                    // console.log('DEBUG: Parsed data type:', typeof data);
                } catch (e) {
                    // console.error('DEBUG: Error parsing response:', e);
                    // console.error('DEBUG: Error name:', e.name);
                    // console.error('DEBUG: Error message:', e.message);
                    data = {};
                }
                
                // Clear previous errors
                // console.log('DEBUG: Clearing previous errors');
                ['name','email','phone','store_location_id','province','district'].forEach(field => {
                    const el = document.getElementById('error-' + field);
                    // console.log('DEBUG: Clearing error field:', field, 'element:', el);
                    if (el) el.innerHTML = '';
                });
                // console.log('DEBUG: Clearing errorDiv:', errorDiv);
                errorDiv.innerHTML = '';
                
                // console.log('DEBUG: Response status:', response.status, response.statusText);
                // console.log('DEBUG: Response ok:', response.ok);
                // console.log('DEBUG: Response headers:', response.headers);
                
                if (!response.ok) {
                    // console.log('DEBUG: Error response - status:', response.status);
                    // console.log('DEBUG: Error response - data:', data);
                    // console.log('DEBUG: Error response - data type:', typeof data);
                    // console.log('DEBUG: Error response - data.errors:', data?.errors);
                    // console.log('DEBUG: Error response - data.message:', data?.message);
                    if (data && data.errors) {
                        
                        Object.entries(data.errors).forEach(([field, arr]) => {
                            // Bỏ qua lỗi status vì mặc định đã là active
                            if (field === 'status') return;
                            
                            const el = document.getElementById('error-' + field);
    
                            if (el) el.innerHTML = `<div class="text-red-600 text-sm">${arr[0]}</div>`;
                        });
                    } else if (data && data.message) {
                        // console.log('DEBUG: Error message:', data.message);
                        // console.log('DEBUG: Setting errorDiv message:', errorDiv);
                        errorDiv.innerHTML = `<div class="text-red-600 text-sm mb-1">${data.message}</div>`;
                    } else {
                        // console.log('DEBUG: Unknown error');
                        // console.log('DEBUG: Setting errorDiv unknown error:', errorDiv);
                        errorDiv.innerHTML = '<div class="text-red-600 text-sm mb-1">Lỗi không xác định. Vui lòng thử lại.</div>';
                    }
                    return;
                }
                
                // Success case
                // console.log('DEBUG: Success case - closing modal and reloading page');
                // console.log('DEBUG: Success data:', data);
                // console.log('DEBUG: Success data type:', typeof data);
                // console.log('DEBUG: Success data.message:', data?.message);
                
                // Lưu thông báo vào session storage để hiển thị sau khi reload
                const successMessage = data && data.message ? data.message : 'Thêm nhân viên thành công!';
                // console.log('DEBUG: Saving success message to session storage:', successMessage);
                sessionStorage.setItem('staff_success_message', successMessage);
                // console.log('DEBUG: Success message saved to session storage');
                
                // Đóng modal trước
                // console.log('DEBUG: Calling closeModal()');
                closeModal();
                // console.log('DEBUG: closeModal() called successfully');
                
                // Reload trang
                // console.log('DEBUG: Calling location.reload()');
                location.reload();
                // console.log('DEBUG: location.reload() called successfully');
            })
            .catch((error) => {
                // console.error('DEBUG: Fetch error:', error);
                // console.error('DEBUG: Error details:', {
                //     name: error.name,
                //     message: error.message,
                //     stack: error.stack
                // });
                // console.error('DEBUG: Error constructor:', error.constructor.name);
                // console.error('DEBUG: Error toString:', error.toString());
                const errorDiv = document.getElementById('staff-form-errors');
                // console.log('DEBUG: Setting catch error message, errorDiv:', errorDiv);
                errorDiv.innerHTML = '<div class="text-red-600 text-sm mb-1">Lỗi kết nối server. Vui lòng kiểm tra kết nối mạng và thử lại.</div>';
            });
        });
    }
    
    // Functions
    function openAddStaffModal() {
        // Reset editing mode
        isEditingMode = false;
        
        // console.log('DEBUG: Hàm openAddStaffModal được gọi');
        const form = document.getElementById('add-staff-form');
        const modal = document.getElementById('add-staff-modal');
        const modalContent = document.getElementById('modal-content');
        
        // console.log('DEBUG: Form element:', form);
        // console.log('DEBUG: Modal element:', modal);
        // console.log('DEBUG: ModalContent element:', modalContent);
        
        if (!form || !modal || !modalContent) {
            // console.error('DEBUG: Không tìm thấy các element cần thiết!');
            return;
        }
        
        // Reset form
        form.reset();
        // console.log('DEBUG: Form đã được reset');
        
        // Clear editing ID
        document.getElementById('editing-staff-id').value = '';
        
        // Update modal title
        document.getElementById('modal-title').textContent = 'Thêm Nhân Viên Mới';
        
        // Update submit button text
        document.getElementById('submit-btn').textContent = 'Thêm Mới';
        
        // Clear errors
        ['name','email','phone','store_location_id','province','district'].forEach(field => {
            const el = document.getElementById('error-' + field);
            if (el) el.innerHTML = '';
        });
        
        const errorDiv = document.getElementById('staff-form-errors');
        if (errorDiv) errorDiv.innerHTML = '';
        
        // Show modal
        // console.log('DEBUG: Modal classes trước khi show:', modal.className);
        modal.classList.remove('hidden');
        // console.log('DEBUG: Modal classes sau khi remove hidden:', modal.className);
        
        setTimeout(() => {
            // console.log('DEBUG: ModalContent classes trước khi remove scale-95, opacity-0:', modalContent.className);
            modalContent.classList.remove('scale-95', 'opacity-0');
            // console.log('DEBUG: ModalContent classes sau khi remove scale-95, opacity-0:', modalContent.className);
            // console.log('DEBUG: Modal đã được mở thành công!');
        }, 10);
    }
    
    function openEditStaffModal(employeeId) {
        // Set editing mode
        isEditingMode = true;
        
        // Clear errors trước khi set value - đảm bảo xóa sạch
        const errorFields = ['name','email','phone','store_location_id','province','district'];
        errorFields.forEach(field => {
            const el = document.getElementById('error-' + field);
            if (el) {
                el.innerHTML = '';
                el.style.display = 'none'; // Ẩn hoàn toàn
            }
        });
        
        const errorDiv = document.getElementById('staff-form-errors');
        if (errorDiv) {
            errorDiv.innerHTML = '';
            errorDiv.style.display = 'none';
        }
        
        // Đảm bảo form không có class error
        const form = document.getElementById('add-staff-form');
        if (form) {
            form.classList.remove('error');
        }

        // Fetch employee data
        const url = `{{ route('admin.sales-staff.api.stores.employees.show', ['storeId' => $store->id, 'employeeId' => ':employeeId']) }}`.replace(':employeeId', employeeId);

        
        fetch(url)
            .then(response => {
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
    
                if (data.success && data.employee) {
                    const employee = data.employee;
    
                    
                    // Set editing ID
                    document.getElementById('editing-staff-id').value = employeeId;
                    
                    // Update modal title
                    document.getElementById('modal-title').textContent = 'Chỉnh Sửa Nhân Viên';
                    
                    // Update submit button text
                    document.getElementById('submit-btn').textContent = 'Cập Nhật';
                    
                    // Fill form fields - tạm thời disable tất cả events
                    const nameField = document.getElementById('name');
                    const emailField = document.getElementById('email');
                    const phoneField = document.getElementById('phone');
                    const statusField = document.getElementById('status');
                    
                    // Tạm thời disable tất cả events
                    const fields = [nameField, emailField, phoneField, statusField];
                    fields.forEach(field => {
                        if (field) {
                            field.disabled = true;
                        }
                    });
                    
                    // Set values
                    nameField.value = employee.name || '';
                    emailField.value = employee.email || '';
                    phoneField.value = employee.phone || '';
                    statusField.value = employee.status || 'active';
                    
                    // Re-enable fields
                    fields.forEach(field => {
                        if (field) {
                            field.disabled = false;
                        }
                    });
                    
                    // Show modal
                    const modal = document.getElementById('add-staff-modal');
                    const modalContent = document.getElementById('modal-content');
                    
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        // Reset editing mode sau khi modal đã hiển thị
                        setTimeout(() => {
                            isEditingMode = false;
                        }, 100);
                    }, 10);
                } else {
                    console.error('DEBUG: Invalid response format:', data);
                    alert('Không thể tải thông tin nhân viên: ' + (data.message || 'Dữ liệu không hợp lệ'));
                }
            })
            .catch(error => {
                console.error('DEBUG: Error fetching employee data:', error);
                console.error('DEBUG: Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                alert('Lỗi khi tải thông tin nhân viên: ' + error.message);
            });
    }
    
    function closeModal() {
        // console.log('DEBUG: Closing modal function called');
        const modalContent = document.getElementById('modal-content');
        const modal = document.getElementById('add-staff-modal');
        // console.log('DEBUG: Modal elements for closing:', { modalContent, modal });
        // console.log('DEBUG: ModalContent classes before closing:', modalContent.className);
        // console.log('DEBUG: Modal classes before closing:', modal.className);
        modalContent.classList.add('scale-95', 'opacity-0');
        // console.log('DEBUG: ModalContent classes after adding scale-95, opacity-0:', modalContent.className);
        setTimeout(() => {
            // console.log('DEBUG: Modal classes before adding hidden:', modal.className);
            modal.classList.add('hidden');
            // console.log('DEBUG: Modal classes after adding hidden:', modal.className);
            // console.log('DEBUG: Modal closed successfully');
        }, 200);
    }
});
</script>
@endpush

