@extends('admin.layouts.app') {{-- Hoặc layout chính của bạn --}}

@section('title', 'Quản lý Người dùng')

@push('styles')
<style>
    /* ==== CORE STYLES - Giữ lại hoặc điều chỉnh nếu cần ==== */
    body {
        background-color: #f8f9fa; /* Màu nền tổng thể cho trang admin */
    }

    .btn {
        border-radius: 0.375rem; /* rounded-md */
        transition: all 0.2s ease-in-out;
        font-weight: 500; /* font-medium */
        padding: 0.625rem 1.25rem; /* py-2.5 px-5 */
        font-size: 0.875rem; /* text-sm */
        line-height: 1.25rem;
    }

    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #6c757d; color: white; border: 1px solid #6c757d; } /* Màu secondary chuẩn hơn */
    .btn-secondary:hover { background-color: #5a6268; border-color: #545b62;}
    .btn-danger { background-color: #dc3545; color: white; }
    .btn-danger:hover { background-color: #c82333; }
    .btn-info { background-color: #17a2b8; color: white; }
    .btn-info:hover { background-color: #138496; }
    .btn-icon {
        padding: 0.5rem; /* p-2 */
        font-size: 0.875rem; /* text-sm */
        line-height: 1; /* Đảm bảo icon căn giữa */
    }


    /* ==== NEW STYLES & OVERWRITES FOR REDESIGN ==== */
    .admin-main-card {
        background-color: white;
        border-radius: 0.75rem; /* rounded-xl */
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); /* box-shadow nhẹ hơn */
        overflow: hidden; /* Để bo góc table bên trong */
    }

    .card-header-section {
        padding: 1.25rem 1.5rem; /* p-5 md:p-6 */
        border-bottom: 1px solid #e5e7eb; /* border-gray-200 */
    }

    .table th {
        background-color: #f9fafb; /* bg-gray-50 */
        color: #374151; /* text-gray-700 */
        font-weight: 600; /* font-semibold */
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.75rem 1.5rem; /* py-3 px-6 */
        text-align: left;
        font-size: 0.75rem; /* text-xs */
    }

    .table td {
        padding: 1rem 1.5rem; /* py-4 px-6 */
        vertical-align: middle;
        color: #4b5563; /* text-gray-600 */
        border-bottom: 1px solid #e5e7eb; /* border-gray-200 */
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .badge-status {
        font-weight: 500;
        padding: 0.25em 0.75em; /* py-1 px-3 */
        display: inline-block;
        border-radius: 9999px; /* rounded-full */
        font-size: 0.75rem; /* text-xs */
        text-transform: capitalize;
    }
    .badge-status-active { background-color: #d1fae5; color: #065f46; }
    .badge-status-inactive { background-color: #fef3c7; color: #92400e; }
    .badge-status-banned { background-color: #fee2e2; color: #991b1b; }

    .search-input {
        border-radius: 0.375rem; /* rounded-md */
        border: 1px solid #d1d5db; /* border-gray-300 */
        padding: 0.625rem 1rem; /* py-2.5 px-4 */
        font-size: 0.875rem; /* text-sm */
        transition: all 0.2s ease-in-out;
    }
    .search-input:focus {
        border-color: #4f46e5; /* focus:border-indigo-500 */
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); /* focus:ring focus:ring-indigo-200 focus:ring-opacity-50 */
        outline: none;
    }

    /* Modal & Toast - giữ lại hoặc điều chỉnh các style cũ của bạn nếu chúng hoạt động tốt */
    .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
    .modal.show { display: flex; align-items: center; justify-content: center; }
    .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
    .modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.125rem; font-weight: 600; color: #1f2937; }
    .close-button { font-size: 1.5rem; font-weight: 600; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
    .close-button:hover { opacity: 1; color: #1f2937; }
    .modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
    .modal-footer { display: flex; align-items: center; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
    .modal-footer > :not(:first-child) { margin-left: .5rem; }

    .toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; display: flex; align-items: center; width: 100%; max-width: 22rem; padding: 1rem; color: #374151; background-color: white; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
    .toast.hide { opacity: 0; transform: translateX(100%); }
    .toast-icon-container { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 2rem; height: 2rem; border-radius: 0.5rem; }
    .toast-icon-success { color: #10b981; background-color: #d1fae5; }
    .toast-icon-error { color: #ef4444; background-color: #fee2e2; }
    .toast-message { margin-left: 0.75rem; font-size: 0.875rem; }
    .toast-close-button { margin-left: auto; margin-right: -0.375rem; margin-top: -0.375rem; background-color: transparent; color: #9ca3af; border-radius: 0.375rem; padding: 0.375rem; display: inline-flex; height: 1.75rem; width: 1.75rem; border: none; cursor: pointer; }
    .toast-close-button:hover { color: #1f2937; background-color: #f3f4f6; }

    .icon-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Pagination styling (nếu bạn dùng Tailwind, có thể custom component pagination) */
    .pagination { display: flex; list-style: none; padding: 0; justify-content: center; /* Hoặc flex-end */ }
    .pagination li { margin: 0 0.25rem; }
    .pagination li a, .pagination li span {
        display: block; padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; color: #4f46e5;
        text-decoration: none; border-radius: 0.25rem; transition: background-color 0.2s;
    }
    .pagination li a:hover { background-color: #e9ecef; }
    .pagination li.active span { background-color: #4f46e5; color: white; border-color: #4f46e5; }
    .pagination li.disabled span { color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6; }

    /* Thêm vào trong thẻ <style> của file users.index.blade.php */
    .badge-role {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
        margin: 0.1rem;
        color: #fff;
        background-color: #6c757d; /* Màu mặc định */
    }

    /* Các màu cụ thể cho từng vai trò nếu muốn */
    .badge-role-admin { background-color: #dc3545; } /* Màu đỏ */
    .badge-role-content_manager { background-color: #ffc107; color: #212529; } /* Màu vàng */
    .badge-role-order_manager { background-color: #17a2b8; } /* Màu xanh dương */
    .badge-role-shipper { background-color: #28a745; } /* Màu xanh lá */
    .badge-role-customer { background-color: #6c757d; } /* Màu xám */

</style>
@endpush

@section('content')
<div class="body-content px-4 md:px-6 py-8">
    {{-- TOAST CONTAINER --}}
    <div id="toast-container" class="toast-container">
        @if (session('success'))
            <div id="toast-success" class="toast" role="alert">
                <div class="toast-icon-container toast-icon-success"><i class="fas fa-check"></i></div>
                <div class="toast-message">{{ session('success') }}</div>
                <button type="button" class="toast-close-button" data-dismiss-target="#toast-success" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
        @endif
        @if (session('error'))
            <div id="toast-error" class="toast" role="alert">
                <div class="toast-icon-container toast-icon-error"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="toast-message">{{ session('error') }}</div>
                <button type="button" class="toast-close-button" data-dismiss-target="#toast-error" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
        @endif
    </div>

    <div class="container mx-auto max-w-full"> {{-- max-w-7xl đã được bỏ để rộng hơn, hoặc giữ lại nếu muốn --}}
        {{-- PAGE HEADER --}}
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">Quản lý Người dùng</h1>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="flex text-xs text-gray-500">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a>
                    </li>
                    <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700" aria-current="page">Người dùng</li>
                </ol>
            </nav>
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="admin-main-card">
            {{-- CARD HEADER & ACTIONS --}}
            <div class="card-header-section">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    {{-- SEARCH FORM --}}
                    <form action="{{ route('admin.users.index') }}" method="GET" class="w-full sm:w-auto sm:max-w-xs flex-grow">
                        <div class="relative">
                            <input type="text" name="search" class="search-input w-full pl-10" placeholder="Tìm kiếm người dùng..." value="{{ request('search') }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </form>
                    {{-- ACTION BUTTONS --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.users.trash') }}" class="px-4 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold flex items-center justify-center space-x-2">
                            <i class="fas fa-trash"></i>Thùng rác</a>
                        <a href="{{ route('admin.users.index') }}" id="refresh-button" class="btn btn-secondary inline-flex items-center" title="Làm mới danh sách">
                            <i class="fas fa-sync-alt mr-0 sm:mr-2"></i> <span class="hidden sm:inline">Làm mới</span>
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary inline-flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Thêm mới
                        </a>

                    </div>
                </div>
            </div>

            {{-- USERS TABLE --}}
            <div class="overflow-x-auto">
                <table class="table w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Thông tin người dùng</th>
                            <th>Vai trò</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="w-32 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover mr-3" src="{{ $user->avatar_url ?? asset('adminlte/dist/img/avatar_placeholder.png') }}" alt="{{ $user->name }}">
                                        <div>
                                            <a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-indigo-600 hover:text-indigo-800 block hover:underline">{{ $user->name }}</a>
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            @if($user->phone_number)
                                                <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone-alt fa-xs mr-1"></i>{{ $user->phone_number }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        @foreach($user->roles as $role)
                                            {{-- Dùng class CSS đã tạo ở trên để hiển thị badge --}}
                                            <span class="badge-role badge-role-{{ str_replace(' ', '_', $role->name) }}">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-500">Chưa có vai trò</span>
                                    @endif
                                </td>
                                <td class="">
                                    @php
                                        $statusClass = '';
                                        switch ($user->status) {
                                            case 'active': $statusClass = 'badge-status-active'; break;
                                            case 'inactive': $statusClass = 'badge-status-inactive'; break;
                                            case 'banned': $statusClass = 'badge-status-banned'; break;
                                            default: $statusClass = 'bg-gray-200 text-gray-800'; break;
                                        }
                                    @endphp
                                    <span class="badge-status {{ $statusClass }}">{{ $user->status }}</span>
                                </td>
                                <td class="text-xs">
                                    {{ $user->created_at->format('d/m/Y') }}
                                    <div class="text-gray-400">{{ $user->created_at->format('H:i') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-icon btn-info hover:bg-blue-600" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-icon btn-primary hover:bg-indigo-700" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-icon btn-danger hover:bg-red-700" title="Xóa người dùng" onclick="openModal('deleteUserModal{{ $user->id }}')"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            {{-- DELETE CONFIRMATION MODAL (Đã cập nhật cho Xóa mềm) --}}
<div id="deleteUserModal{{ $user->id }}" class="modal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            {{-- Sửa tiêu đề --}}
            <h5 class="modal-title">Xác nhận Đưa vào thùng rác</h5>
            <button type="button" class="close-button" onclick="closeModal('deleteUserModal{{ $user->id }}')"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="flex items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                    {{-- Đổi icon sang cảnh báo nhẹ nhàng hơn --}}
                    <i class="fas fa-trash-alt text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4 text-left">
                    {{-- Sửa nội dung câu hỏi --}}
                    <p class="text-base text-gray-700">Bạn có chắc chắn muốn đưa người dùng "<strong>{{ $user->name }}</strong>" vào thùng rác?</p>

                    {{-- SỬA NỘI DUNG CẢNH BÁO - QUAN TRỌNG NHẤT --}}
                    <p class="mt-1 text-sm text-gray-500">Người dùng sẽ bị vô hiệu hóa và chuyển vào thùng rác. Bạn có thể khôi phục họ sau này.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal{{ $user->id }}')">Hủy</button>

            {{-- Form vẫn giữ nguyên, chỉ sửa chữ trên nút bấm --}}
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                {{-- Sửa chữ trên nút bấm --}}
                <button type="submit" class="btn btn-danger">Đưa vào thùng rác</button>
            </form>
        </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">
                                    @if (request('search'))
                                        Không tìm thấy người dùng nào với từ khóa "<strong>{{ request('search') }}</strong>".
                                    @else
                                        Không có người dùng nào trong hệ thống.
                                    @endif
                                    <a href="{{ route('admin.users.create') }}" class="text-indigo-600 hover:underline ml-2">Thêm người dùng mới?</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($users->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if ($users->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white cursor-default">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                    @endif

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                    @else
                        <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white cursor-default">Next</span>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Hiển thị từ
                            <span class="font-medium">{{ $users->firstItem() }}</span>
                            đến
                            <span class="font-medium">{{ $users->lastItem() }}</span>
                            trên tổng số
                            <span class="font-medium">{{ $users->total() }}</span>
                            kết quả
                        </p>
                    </div>
                    <div>
                         {{-- Sử dụng trình tạo link mặc định của Laravel hoặc custom pagination view --}}
                        {!! $users->appends(['search' => request('search')])->links() !!}
                        {{-- Nếu bạn muốn custom pagination với style ở trên, bạn cần tạo custom pagination view
                        ví dụ: {!! $users->appends(['search' => request('search')])->links('vendor.pagination.custom-tailwind') !!}
                        Và CSS cho .pagination đã được thêm vào trong <style>
                        --}}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Giữ nguyên phần script của bạn vì nó xử lý logic, không phải giao diện trực tiếp --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === SCRIPT CHO TOAST NOTIFICATION ===
    const toasts = document.querySelectorAll('.toast');
    const hideToast = (toastElement) => {
        if (toastElement) {
            toastElement.classList.add('hide');
            setTimeout(() => {
                if (toastElement.parentNode) { // Kiểm tra trước khi remove
                    toastElement.remove();
                }
            }, 350);
        }
    };
    toasts.forEach(toast => {
        const autoHideTimeout = setTimeout(() => { hideToast(toast); }, 5000);
        const closeButton = toast.querySelector('[data-dismiss-target]');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                clearTimeout(autoHideTimeout);
                const targetId = this.getAttribute('data-dismiss-target');
                const toastToHide = document.querySelector(targetId);
                hideToast(toastToHide);
            });
        }
    });

    // === SCRIPT CHO MODAL ===
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    window.addEventListener('click', function(event) {
        document.querySelectorAll('.modal.show').forEach(modal => {
            if (event.target.closest('.modal-content') === null && event.target.classList.contains('modal')) {
                closeModal(modal.id);
            }
        });
    });
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
        }
    });

    // === SCRIPT CHO NÚT LÀM MỚI ===
    const refreshButton = document.getElementById('refresh-button');
    if (refreshButton) {
        refreshButton.addEventListener('click', function(e) {
            const icon = this.querySelector('i.fa-sync-alt');
            if (icon) {
                icon.classList.add('icon-spin');
            }
            // Trình duyệt sẽ tự load lại trang do href. Icon sẽ reset.
        });
    }
});
</script>
@endpush
