@extends('admin.layouts.app')
@section('title', 'Quản lý Vai trò')
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
    <div class="container mx-auto max-w-full">
        {{-- PAGE HEADER --}}
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">Quản lý Vai trò</h1>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="flex text-xs text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700" aria-current="page">Vai trò</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary inline-flex items-center mt-4 sm:mt-0">
                <i class="fas fa-plus-circle mr-2"></i> Thêm vai trò mới
            </a>
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="admin-main-card">
            <div class="overflow-x-auto">
                <table class="table w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Tên Vai trò</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số người dùng</th>
                            <th class="w-32 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td class="font-semibold">{{ $role->name }}</td>
                                <td>{{ $role->description }}</td>
                                <td class="text-center">{{ $role->users_count }}</td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-icon btn-primary" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vai trò này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">Chưa có vai trò nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
