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

        .pagination>* {
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

        .pagination>*:hover {
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

        .modal-open {
            overflow: hidden;
        }
    </style>
@endpush
@section('content')
    <div class="w-full">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.sales-staff.index') }}" class="p-2 rounded-md hover:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Nhân viên tại: {{ $store->name }}</h1>
                    <p class="text-gray-500">{{ $store->district->name_with_type ?? '' }},
                        {{ $store->province->name_with_type ?? '' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Per Page -->
                <div class="flex items-center gap-2">
                    <label for="per-page-select" class="text-sm font-medium text-gray-700">Hiển thị:</label>
                    <select id="per-page-select"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <a href="{{ route('admin.sales-staff.stores.schedule', $store->id) }}"
                    class="flex items-center gap-2 bg-teal-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Quản Lý Lịch Làm Việc
                </a>
                <button id="add-staff-btn-in-view" data-store-id="{{ $store->id }}"
                    class="flex items-center gap-2 bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Thêm Nhân Viên
                </button>
                <a href="{{ route('admin.sales-staff.trash') }}"
                    class="flex items-center gap-2 bg-gray-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"></path>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                        </path>
                    </svg>
                    Thùng Rác
                </a>
            </div>
        </div>
        <!-- Bộ lọc và bảng nhân viên kết hợp -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <input id="employee-search-input" type="text" placeholder="Tìm theo tên, email, sđt..."
                        class="pl-10 pr-4 py-2 border rounded-lg w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
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
                                    @if ($employee['status'] == 'active')
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Đang
                                            hoạt động</span>
                                    @elseif($employee['status'] == 'inactive')
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Không
                                            hoạt động</span>
                                    @elseif($employee['status'] == 'banned')
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Đã
                                            khóa</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button
                                            class="edit-btn bg-gray-200 text-gray-800 p-2 rounded-lg hover:bg-gray-300 transition-colors"
                                            title="Chỉnh sửa" data-id="{{ $employee['id'] }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="w-5 h-5">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            class="delete-btn bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition-colors"
                                            title="Xóa" data-id="{{ $employee['id'] }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center py-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400"
                                            width="48" height="48" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <path d="M20 8v6"></path>
                                            <path d="M23 11h-6"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có nhân viên</h3>
                                        <p class="mt-1 text-sm text-gray-500">Hãy thêm nhân viên đầu tiên cho cửa hàng này.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            @if ($employees->hasPages())
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
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-900">Thêm Nhân Viên Mới</h3>
                <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <!-- Form -->
            <form id="add-staff-form" class="p-6">
                @csrf
                <div id="staff-form-errors" class="mb-2"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" placeholder="Nhập họ và tên"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-name" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" placeholder="example@email.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-email" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số Điện Thoại <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="phone" name="phone" placeholder="09xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-phone" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="status" name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                                <option value="banned">Đã khóa</option>
                            </select>
                            <div id="error-status" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật Khẩu <span
                                    class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-password" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác Nhận Mật Khẩu <span
                                    class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-password_confirmation" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        @if (!$store)
                            <div>
                                <label for="modal-province-select"
                                    class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành Phố</label>
                                <select id="modal-province-select" name="province"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Chọn Tỉnh/Thành Phố --</option>
                                    @foreach ($provinces ?? [] as $province)
                                        <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                                    @endforeach
                                </select>
                                <div id="error-province" class="text-red-600 text-sm mt-1"></div>
                            </div>
                            <div>
                                <label for="modal-district-select"
                                    class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                                <select id="modal-district-select" name="district" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                </select>
                                <div id="error-district" class="text-red-600 text-sm mt-1"></div>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành Phố</label>
                                <input type="text" value="{{ $store->province->name_with_type ?? '' }}" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                <input type="hidden" name="province" value="{{ $store->province->code ?? '' }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                                <input type="text" value="{{ $store->district->name_with_type ?? '' }}" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                <input type="hidden" name="district" value="{{ $store->district->code ?? '' }}">
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Phần chọn cửa hàng -->
                <div class="mt-6 border-t pt-6">
                    @if (!$store)
                        <div>
                            <label for="modal-store-select" class="block text-sm font-medium text-gray-700 mb-1">Cửa Hàng <span
                                    class="text-danger">*</span></label>
                            <select id="modal-store-select" name="store_location_id" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn Cửa Hàng --</option>
                            </select>
                            <div id="error-store_location_id" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cửa Hàng</label>
                            <input type="text" value="{{ $store->name }}" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                            <input type="hidden" name="store_location_id" value="{{ $store->id }}">
                        </div>
                    @endif
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="button" id="cancel-modal-btn"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Hủy
                    </button>
                    <button type="submit" id="submit-btn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Thêm Mới
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Chỉnh Sửa Nhân Viên -->
    <div id="edit-staff-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto" id="edit-modal-content">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Chỉnh Sửa Nhân Viên</h3>
                <button type="button" id="close-edit-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <!-- Form Edit -->
            <form id="edit-staff-form" class="p-6">
                @csrf
                <input type="hidden" id="edit-staff-id" name="edit_staff_id" value="">
                <div id="edit-staff-form-errors" class="mb-2"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột trái -->
                    <div class="space-y-4">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" id="edit-name" name="name" placeholder="Nhập họ và tên"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-edit-name" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-danger">*</span></label>
                            <input type="email" id="edit-email" name="email" placeholder="example@email.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-edit-email" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-phone" class="block text-sm font-medium text-gray-700 mb-1">Số Điện Thoại <span class="text-danger">*</span></label>
                            <input type="text" id="edit-phone" name="phone" placeholder="09xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-edit-phone" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="edit-status" name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                                <option value="banned">Đã khóa</option>
                            </select>
                            <div id="error-edit-status" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    </div>
                    <!-- Cột phải -->
                    <div class="space-y-4">
                        <div>
                            <label for="edit-password" class="block text-sm font-medium text-gray-700 mb-1">Mật Khẩu Mới</label>
                            <input type="password" id="edit-password" name="password" placeholder="Để trống nếu không đổi"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-edit-password" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-password-confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác Nhận Mật Khẩu Mới</label>
                            <input type="password" id="edit-password-confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu mới"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div id="error-edit-password-confirmation" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-province-select" class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành Phố <span class="text-danger">*</span></label>
                            <select id="edit-province-select" name="province"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn Tỉnh/Thành Phố --</option>
                                @foreach ($provinces ?? [] as $province)
                                    <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                                @endforeach
                            </select>
                            <div id="error-edit-province" class="text-red-600 text-sm mt-1"></div>
                        </div>
                        <div>
                            <label for="edit-district-select" class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện <span class="text-danger">*</span></label>
                            <select id="edit-district-select" name="district" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                            <div id="error-edit-district" class="text-red-600 text-sm mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <div>
                        <label for="edit-store-select" class="block text-sm font-medium text-gray-700 mb-1">Cửa Hàng <span class="text-danger">*</span></label>
                        <select id="edit-store-select" name="store_location_id" disabled
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Chọn Cửa Hàng --</option>
                        </select>
                        <div id="error-edit-store_location_id" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="button" id="cancel-edit-modal-btn"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Hủy
                    </button>
                    <button type="submit" id="edit-submit-btn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
<div id="deleteEmployeeModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="delete-modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="delete-modal-title">
                            Xóa nhân viên
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Bạn có chắc chắn muốn xóa nhân viên này không? Hành động này không thể được hoàn tác.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmDeleteBtn"
                    class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Xác nhận Xóa
                </button>
                <button type="button" onclick="closeDeleteModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Hủy
                </button>
            </div>
        </div>
    </div>
</div>
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
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        document.addEventListener('DOMContentLoaded', function() {
            //Chức năng chọn theo trang
            const perPageSelect = document.getElementById('per-page-select');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const perPage = this.value;
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('per_page', perPage);
                    currentUrl.searchParams.delete('page');
                    window.location.href = currentUrl.toString();
                });
            }

            // Kiểm tra và hiển thị thông báo từ session storage
            const successMessage = sessionStorage.getItem('staff_success_message');
            const deleteSuccessMessage = sessionStorage.getItem('employee_delete_success_message');
            if (successMessage) {
                toastr.success(successMessage);
                sessionStorage.removeItem('staff_success_message');
            }
            // Kiểm tra và hiển thị thông báo xóa thành công
            if (deleteSuccessMessage) {
                toastr.success(deleteSuccessMessage);
                sessionStorage.removeItem('employee_delete_success_message');
            }
            const employeeSearchInput = document.getElementById('employee-search-input');
            const staffTableBody = document.getElementById('staff-table-body');
            // Tìm kiếm nhân viên với API
            let searchTimeout;
            employeeSearchInput.addEventListener('input', function() {
                const searchTerm = this.value;
                const perPage = perPageSelect ? perPageSelect.value : 10;
                clearTimeout(searchTimeout);
                // Thực hiện tìm kiếm sau 300ms
                searchTimeout = setTimeout(() => {
                    searchEmployees(searchTerm, perPage);
                }, 300);
            });

            function searchEmployees(searchTerm, perPage) {
                staffTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">Đang tải...</td></tr>';
                
                fetch(`{{ route('admin.sales-staff.api.stores.employees', $store->id) }}?search=${searchTerm}&per_page=${perPage}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.employees.length === 0) {
                            staffTableBody.innerHTML = `
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy nhân viên</h3>
                                    <p class="mt-1 text-sm text-gray-500">Vui lòng thay đổi từ khóa tìm kiếm.</p>
                                </div>
                                </td></tr>`;
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
                            <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center py-8">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Lỗi tìm kiếm</h3>
                                <p class="mt-1 text-sm text-gray-500">Vui lòng thử lại sau.</p>
                            </div>
                            </td></tr>`;
                    });
            }
            // Xử lý click events cho buttons (chỉ trong trang employees)
            if (document.getElementById('staff-table-body')) {
                // Delay nhỏ để tránh conflict khi chuyển trang
                setTimeout(() => {
                    document.addEventListener('click', function(e) {
                        // Chỉ xử lý nếu đang ở trang employees và có staff-table-body
                        if (!document.getElementById('staff-table-body')) return;
                        
                        // Delete button
                        if (e.target.closest('.delete-btn')) {
                            e.preventDefault();
                            e.stopPropagation();
                            const employeeId = e.target.closest('.delete-btn').dataset.id;
                            if (employeeId && employeeId !== '') {
                    openDeleteModal(employeeId);
                }
                        }
                        
                        // Edit button
                        if (e.target.closest('.edit-btn')) {
                            e.preventDefault();
                            e.stopPropagation();
                            const employeeId = e.target.closest('.edit-btn').dataset.id;
                            if (employeeId && employeeId !== '') {
                    openEditStaffModal(employeeId);
                            }
                }
            });
                }, 100);
            }
            // Nút mở modal
            const btnInView = document.getElementById('add-staff-btn-in-view');
            if (btnInView) {
                btnInView.addEventListener('click', function(e) {
                    openAddStaffModal();
                });
            }
            // Đóng modal khi click ra ngoài
            const modal = document.getElementById('add-staff-modal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }
            // Đóng modal khi click nút Hủy
            const cancelModalBtn = document.getElementById('cancel-modal-btn');
            if (cancelModalBtn) {
                cancelModalBtn.addEventListener('click', closeModal);
            }
            
            // Đóng modal edit khi click nút Hủy
            const cancelEditModalBtn = document.getElementById('cancel-edit-modal-btn');
            if (cancelEditModalBtn) {
                cancelEditModalBtn.addEventListener('click', closeEditModal);
            }
            // Đóng modal
            const closeModalBtn = document.getElementById('close-modal-btn');
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }
            
            // Đóng modal edit
            const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
            if (closeEditModalBtn) {
                closeEditModalBtn.addEventListener('click', closeEditModal);
            }
            const modalProvinceSelect = document.getElementById('modal-province-select');
            const modalDistrictSelect = document.getElementById('modal-district-select');
            const modalStoreSelect = document.getElementById('modal-store-select');

            // Edit modal selects
            const editProvinceSelect = document.getElementById('edit-province-select');
            const editDistrictSelect = document.getElementById('edit-district-select');
            const editStoreSelect = document.getElementById('edit-store-select');

            // Location handlers cho Add modal
            if (modalProvinceSelect) {
                modalProvinceSelect.addEventListener('change', function() {
                    const provinceCode = this.value;
                    modalDistrictSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                    modalStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                    
                    if (provinceCode) {
                        fetch(`/api/locations/old/districts/${provinceCode}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.data) {
                                    data.data.forEach(district => {
                                        modalDistrictSelect.innerHTML += `<option value="${district.code}">${district.name_with_type}</option>`;
                                    });
                                }
                                modalDistrictSelect.disabled = false;
                            });
                    } else {
                        modalDistrictSelect.disabled = true;
                    modalStoreSelect.disabled = true;
                    }
                });
            }
            
            if (modalDistrictSelect) {
                modalDistrictSelect.addEventListener('change', function() {
                    const districtCode = this.value;
                    modalStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                    
                    if (districtCode) {
                        fetch(`{{ route('admin.sales-staff.api.stores') }}?district=${districtCode}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.stores && data.stores.length > 0) {
                                    data.stores.forEach(store => {
                                        modalStoreSelect.innerHTML += `<option value="${store.id}">${store.name}</option>`;
                                    });
                                }
                                modalStoreSelect.disabled = false;
                            });
                    } else {
                        modalStoreSelect.disabled = true;
                    }
                });
            }
            // Chỉnh sửa địa chỉ trong Edit modal
            if (editProvinceSelect) {
                editProvinceSelect.addEventListener('change', function() {
                    const provinceCode = this.value;
                    editDistrictSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                    editStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                    
                    if (provinceCode) {
                        fetch(`/api/locations/old/districts/${provinceCode}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.data) {
                                    data.data.forEach(district => {
                                        editDistrictSelect.innerHTML += `<option value="${district.code}">${district.name_with_type}</option>`;
                                    });
                                }
                                editDistrictSelect.disabled = false;
                            });
                    } else {
                        editDistrictSelect.disabled = true;
                        editStoreSelect.disabled = true;
                    }
                });
            }
            if (editDistrictSelect) {
                editDistrictSelect.addEventListener('change', function() {
                    const districtCode = this.value;
                    editStoreSelect.innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                    
                    if (districtCode) {
                        fetch(`{{ route('admin.sales-staff.api.stores') }}?district=${districtCode}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.stores && data.stores.length > 0) {
                                    data.stores.forEach(store => {
                                        editStoreSelect.innerHTML += `<option value="${store.id}">${store.name}</option>`;
                                    });
                                }
                                editStoreSelect.disabled = false;
                            });
                    } else {
                        editStoreSelect.disabled = true;
                    }
                });
            }
            // Form submit Add
            const addStaffForm = document.getElementById('add-staff-form');
            if (addStaffForm) {
                addStaffForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    ['name', 'email', 'phone', 'password', 'password_confirmation', 'store_location_id', 'province', 'district'].forEach(field => {
                        const el = document.getElementById('error-' + field);
                        if (el) el.innerHTML = '';
                    });
                    document.getElementById('staff-form-errors').innerHTML = '';
                    // Validate
                    const name = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const phone = document.getElementById('phone').value.trim();
                    const password = document.getElementById('password').value;
                    const passwordConfirmation = document.getElementById('password_confirmation').value;
                    
                    let hasError = false;
                    if (!name) { document.getElementById('error-name').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập họ và tên</div>'; hasError = true; }
                    if (!email) { document.getElementById('error-email').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập email</div>'; hasError = true; }
                    if (!phone) { document.getElementById('error-phone').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập số điện thoại</div>'; hasError = true; }
                    if (!password) { document.getElementById('error-password').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập mật khẩu</div>'; hasError = true; }
                    if (!passwordConfirmation) { document.getElementById('error-password_confirmation').innerHTML = '<div class="text-red-600 text-sm">Vui lòng xác nhận mật khẩu</div>'; hasError = true; }
                    if (password && passwordConfirmation && password !== passwordConfirmation) { 
                        document.getElementById('error-password_confirmation').innerHTML = '<div class="text-red-600 text-sm">Mật khẩu xác nhận không khớp</div>'; 
                        hasError = true; 
                    }
                    
                    if (hasError) return;
                    // Submit
                    fetch('{{ route('admin.sales-staff.api.employees.store') }}', {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            sessionStorage.setItem('staff_success_message', data.message);
                            closeModal();
                            location.reload();
                    } else {
                            if (data.errors) {
                                Object.entries(data.errors).forEach(([field, arr]) => {
                                    const el = document.getElementById('error-' + field);
                                    if (el) el.innerHTML = `<div class="text-red-600 text-sm">${arr[0]}</div>`;
                                });
                            }
                        }
                    })
                    .catch(error => {
                        document.getElementById('staff-form-errors').innerHTML = '<div class="text-red-600 text-sm mb-1">Lỗi kết nối server</div>';
                    });
                });
            }
            
            // Form submit cho Edit
            const editStaffForm = document.getElementById('edit-staff-form');
            if (editStaffForm) {
                editStaffForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    ['edit-name', 'edit-email', 'edit-phone', 'edit-password', 'edit-password-confirmation', 'edit-store_location_id', 'edit-province', 'edit-district'].forEach(field => {
                        const el = document.getElementById('error-' + field);
                        if (el) el.innerHTML = '';
                    });
                    document.getElementById('edit-staff-form-errors').innerHTML = '';
                    // Validate
                    const name = document.getElementById('edit-name').value.trim();
                    const email = document.getElementById('edit-email').value.trim();
                    const phone = document.getElementById('edit-phone').value.trim();
                    const password = document.getElementById('edit-password').value;
                    const passwordConfirmation = document.getElementById('edit-password-confirmation').value;
                    const province = document.getElementById('edit-province-select').value;
                    const district = document.getElementById('edit-district-select').value;
                    const storeLocationId = document.getElementById('edit-store-select').value;
                    
                    let hasError = false;
                    if (!name) { document.getElementById('error-edit-name').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập họ và tên</div>'; hasError = true; }
                    if (!email) { document.getElementById('error-edit-email').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập email</div>'; hasError = true; }
                    if (!phone) { document.getElementById('error-edit-phone').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập số điện thoại</div>'; hasError = true; }
                    if (!province) { document.getElementById('error-edit-province').innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn tỉnh/thành phố</div>'; hasError = true; }
                    if (!district) { document.getElementById('error-edit-district').innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn quận/huyện</div>'; hasError = true; }
                    if (!storeLocationId) { document.getElementById('error-edit-store_location_id').innerHTML = '<div class="text-red-600 text-sm">Vui lòng chọn cửa hàng</div>'; hasError = true; }
                    
                    // Validate password nếu có nhập
                    if (password && !passwordConfirmation) {
                        document.getElementById('error-edit-password-confirmation').innerHTML = '<div class="text-red-600 text-sm">Vui lòng xác nhận mật khẩu mới</div>';
                        hasError = true;
                    }
                    if (passwordConfirmation && !password) {
                        document.getElementById('error-edit-password').innerHTML = '<div class="text-red-600 text-sm">Vui lòng nhập mật khẩu mới</div>';
                        hasError = true;
                    }
                    if (password && passwordConfirmation && password !== passwordConfirmation) {
                        document.getElementById('error-edit-password-confirmation').innerHTML = '<div class="text-red-600 text-sm">Mật khẩu xác nhận không khớp</div>';
                        hasError = true;
                    }
                    
                    if (hasError) return;
                    
                    const formData = new FormData(this);
                    const staffId = document.getElementById('edit-staff-id').value;
                    
                        formData.append('_method', 'PUT');
                    fetch(`/admin/sales-staff/api/employees/${staffId}`, {
                        method: 'POST',
                            body: formData,
                            headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            sessionStorage.setItem('staff_success_message', data.message || 'Cập nhật nhân viên thành công!');
                            closeEditModal();
                            location.reload();
                        } else {
                            if (data.errors) {
                                    Object.entries(data.errors).forEach(([field, arr]) => {
                                    const el = document.getElementById('error-edit-' + field);
                                    if (el) el.innerHTML = `<div class="text-red-600 text-sm">${arr[0]}</div>`;
                                });
                                } else {
                                document.getElementById('edit-staff-form-errors').innerHTML = `<div class="text-red-600 text-sm mb-1">${data.message || 'Có lỗi xảy ra'}</div>`;
                            }
                        }
                    })
                    .catch(error => {
                        document.getElementById('edit-staff-form-errors').innerHTML = '<div class="text-red-600 text-sm mb-1">Lỗi kết nối server</div>';
                        });
                });
            }
            // Functions
            function openAddStaffModal() {
                const form = document.getElementById('add-staff-form');
                const modal = document.getElementById('add-staff-modal');
                const modalContent = document.getElementById('modal-content');
                if (!form || !modal || !modalContent) {
                    return;
                }
                // Reset form
                form.reset();
                // Clear errors
                ['name', 'email', 'phone', 'password', 'password_confirmation', 'store_location_id', 'province', 'district'].forEach(field => {
                    const el = document.getElementById('error-' + field);
                    if (el) el.innerHTML = '';
                });
                const errorDiv = document.getElementById('staff-form-errors');
                if (errorDiv) errorDiv.innerHTML = '';
                // Show modal
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                }, 10);
            }

            function openEditStaffModal(employeeId) {
                // Clear errors
                ['edit-name', 'edit-email', 'edit-phone', 'edit-password', 'edit-password-confirmation', 'edit-store_location_id', 'edit-province', 'edit-district'].forEach(field => {
                    const el = document.getElementById('error-' + field);
                    if (el) el.innerHTML = '';
                });
                document.getElementById('edit-staff-form-errors').innerHTML = '';
                
                // Fetch employee data
                fetch(`{{ route('admin.sales-staff.api.stores.employees.show', ['storeId' => $store->id, 'employeeId' => ':employeeId']) }}`.replace(':employeeId', employeeId))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.employee) {
                            const employee = data.employee;
                            
                            // Set form data
                            document.getElementById('edit-staff-id').value = employeeId;
                            document.getElementById('edit-name').value = employee.name || '';
                            document.getElementById('edit-email').value = employee.email || '';
                            document.getElementById('edit-phone').value = employee.phone || '';
                            document.getElementById('edit-status').value = employee.status || 'active';
                            
                            // Set location data
                            if (employee.store_location && employee.store_location.province) {
                                const store = employee.store_location;
                                
                                // Set province
                                document.getElementById('edit-province-select').value = store.province.code;
                                
                                // Load districts
                                fetch(`/api/locations/old/districts/${store.province.code}`)
                                    .then(response => response.json())
                                    .then(districtData => {
                                        if (districtData.success && districtData.data) {
                                            document.getElementById('edit-district-select').innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                                            districtData.data.forEach(district => {
                                                document.getElementById('edit-district-select').innerHTML += 
                                                    `<option value="${district.code}">${district.name_with_type}</option>`;
                                            });
                                            document.getElementById('edit-district-select').disabled = false;
                                            
                                            // Set district
                                            if (store.district) {
                                                document.getElementById('edit-district-select').value = store.district.code;
                                                
                                                // Load stores
                                                fetch(`{{ route('admin.sales-staff.api.stores') }}?district=${store.district.code}`)
                                                    .then(response => response.json())
                                                    .then(storeData => {
                                                        if (storeData.stores) {
                                                            document.getElementById('edit-store-select').innerHTML = '<option value="">-- Chọn Cửa Hàng --</option>';
                                                            storeData.stores.forEach(s => {
                                                                document.getElementById('edit-store-select').innerHTML += 
                                                                    `<option value="${s.id}">${s.name}</option>`;
                                                            });
                                                            document.getElementById('edit-store-select').disabled = false;
                                                            
                                                            // Set store
                                                            document.getElementById('edit-store-select').value = employee.store_location_id;
                                                        }
                                                    });
                                            }
                                        }
                                    });
                            }
                            
                            // Show modal
                            document.getElementById('edit-staff-modal').classList.remove('hidden');
                        } else {
                            alert('Không thể tải thông tin nhân viên');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Lỗi khi tải thông tin nhân viên');
                    });
            }

            function closeModal() {
                const modalContent = document.getElementById('modal-content');
                const modal = document.getElementById('add-staff-modal');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 200);
            }
            
            function closeEditModal() {
                const modal = document.getElementById('edit-staff-modal');
                modal.classList.add('hidden');
            }
            // Modal xác nhận xóa nhân viên
            const deleteEmployeeModal = document.getElementById('deleteEmployeeModal');
            if (deleteEmployeeModal) {
                deleteEmployeeModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDeleteModal();
                    }
                });
            }

            function openDeleteModal(employeeId) {
                const deleteModal = document.getElementById('deleteEmployeeModal');
                if (deleteModal) {
                    deleteModal.classList.remove('hidden');
                    deleteModal.style.display = 'block';
                    document.getElementById('confirmDeleteBtn').dataset.id = employeeId;
                }
            }
            window.closeDeleteModal = function() {
                const deleteModal = document.getElementById('deleteEmployeeModal');
                if (deleteModal) {
                    deleteModal.classList.add('hidden');
                    deleteModal.style.display = 'none';
                }
            }
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                const employeeId = this.dataset.id;
                
                fetch(`/admin/sales-staff/api/stores/{{ $store->id }}/employees/${employeeId}`, {
                    method: 'DELETE',
                        headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                        sessionStorage.setItem('employee_delete_success_message', data.message);
                            location.reload();
                        }
                    })
                    .catch(error => {
                    toastr.error('Có lỗi xảy ra khi xóa nhân viên');
                    });
                
                closeDeleteModal();
            });
        });
    </script>
@endpush
