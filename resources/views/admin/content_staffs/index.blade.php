@extends('admin.layouts.app')

@section('title', 'Quản lý Nhân viên Content')

@push('styles')
    <style>
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: #ffffff;
            /* Thay gradient bằng màu trắng hoàn toàn */
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            height: 2.5rem;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td,
        .table th {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .table tr:hover {
            background-color: #f9fafb;
        }

        .form-input,
        .form-select {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            transition: all 0.2s ease-in-out;
            height: 2.5rem;
            padding: 0.5rem 1rem;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }

        .stats-card {
            min-height: 100px;
            display: flex;
            align-items: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            border: none;
            width: 90%;
            max-width: 550px;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin-bottom: 0;
            line-height: 1.5;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .close {
            font-size: 1.75rem;
            font-weight: 500;
            color: #6b7280;
            opacity: .75;
            background-color: transparent;
            border: 0;
            cursor: pointer;
        }

        .close:hover {
            opacity: 1;
            color: #1f2937;
        }

        .modal-body {
            padding: 1.5rem;
            color: #374151;
        }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            background-color: #ffffff;
            /* Thay #f9fafb bằng #ffffff */
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }

        .modal-footer> :not(:first-child) {
            margin-left: .5rem;
        }

        @media (prefers-color-scheme: dark) {
            .card {
                background: #1f2937;
                /* Thay gradient bằng màu nền tối cố định */
                box-shadow: 0 6px 12px -2px rgba(255, 255, 255, 0.05);
            }

            .table th {
                background-color: #1f2937;
                color: #d1d5db;
            }

            .table tr {
                background-color: #111827;
                color: #f3f4f6;
            }

            .table tr:hover {
                background-color: #1f2937;
            }

            .modal-content {
                background-color: #1f2937;
                color: #f3f4f6;
            }

            .modal-header,
            .modal-footer {
                border-color: #374151;
            }

            .modal-footer {
                background-color: #1f2937;
            }

            .modal-body {
                color: #e5e7eb;
            }
        }

        .badge-published {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-draft {
            background: #f3f4f6;
            color: #4b5563;
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')

        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhân viên Content</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Nhân viên Content
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="card stats-card p-6 flex items-center space-x-4 border-t border-gray-200">
                    <div class="bg-blue-100 text-blue-600 p-4 rounded-full"><i class="fas fa-users fa-2x"></i></div>
                    <div>
                        <p class="text-gray-500 text-sm">Tổng số nhân viên</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="card stats-card p-6 flex items-center space-x-4 border-t border-gray-200">
                    <div class="bg-green-100 text-green-600 p-4 rounded-full"><i class="fas fa-user-check fa-2x"></i></div>
                    <div>
                        <p class="text-gray-500 text-sm">Đang hoạt động</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['active'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="card stats-card p-6 flex items-center space-x-4 border-t border-gray-200">
                    <div class="bg-yellow-100 text-yellow-600 p-4 rounded-full"><i class="fas fa-file-alt fa-2x"></i></div>
                    <div>
                        <p class="text-gray-500 text-sm">Tổng bài viết</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_posts'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="card stats-card p-6 flex items-center space-x-4 border-t border-gray-200">
                    <div class="bg-purple-100 text-purple-600 p-4 rounded-full"><i class="fas fa-eye fa-2x"></i></div>
                    <div>
                        <p class="text-gray-500 text-sm">Tổng lượt xem</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_views'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="card bg-white">
                <div class="bg-gray-50 p-5 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-3 sm:mb-0">
                            Tất cả nhân viên ({{ $contentStaffs->total() }})
                        </h3>
                        <div class="flex space-x-2">
                            <!-- Nút Thêm nhân viên mới -->
                            <a href="{{ route('admin.content-staffs.create') }}"
                                class="btn btn-primary inline-flex items-center text-sm">
                                <i class="fas fa-plus-circle mr-2"></i> Thêm nhân viên mới
                            </a>

                            <!-- Nút Thùng rác -->
                            <a href="{{ route('admin.content_staffs.trash') }}"
                                class="btn btn-danger inline-flex items-center text-sm">
                                <i class="fas fa-trash-alt mr-2"></i> Thùng rác
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="table w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th colspan="5" class="p-0">
                                        <form action="{{ route('admin.content-staffs.index') }}" method="GET"
                                            class="flex flex-col sm:flex-row items-center p-5 space-y-3 sm:space-y-0 sm:space-x-3">
                                            <div class="flex-grow">
                                                <input type="text" name="search"
                                                    class="form-input w-full py-2.5 text-sm"
                                                    placeholder="Tìm kiếm theo tên, Email..."
                                                    value="{{ request('search') }}">
                                            </div>
                                            <div class="w-full sm:w-1/4">
                                                <select name="status" class="form-select w-full py-2.5 text-sm">
                                                    <option value="">Tất cả trạng thái</option>
                                                    <option value="active"
                                                        {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt
                                                        động
                                                    </option>
                                                    <option value="inactive"
                                                        {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt
                                                        động</option>
                                                </select>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button type="submit" class="btn btn-primary h-full py-2 text-sm">
                                                    <i class="fas fa-search mr-2"></i> Tìm kiếm
                                                </button>
                                                <a href="{{ route('admin.content-staffs.index') }}"
                                                    class="btn btn-secondary h-full py-2 text-sm">
                                                    <i class="fas fa-times mr-2"></i> Xóa bộ lọc
                                                </a>
                                            </div>
                                        </form>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Nhân viên</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Hiệu suất</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Ngày tham gia</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Trạng thái</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-700 w-48">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($contentStaffs as $index => $staff)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex items-center">
                                                <img src="https://placehold.co/40x40/8B5CF6/FFFFFF?text={{ strtoupper(mb_substr($staff->name, 0, 1)) }}"
                                                    class="w-10 h-10 rounded-full mr-4 object-cover border border-gray-200">
                                                <div>
                                                    <div class="font-semibold text-gray-800">{{ $staff->name }}</div>
                                                    <div class="text-gray-500 text-xs">{{ $staff->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex items-center text-sm space-x-2">
                                                <i class="fas fa-file-alt text-blue-500"></i>
                                                <span>Số bài viết:</span>
                                                <strong>{{ number_format($staff->posts_count ?? 0) }}</strong>
                                            </div>
                                            <div class="flex items-center text-sm mt-1 space-x-2">
                                                <i class="fas fa-eye text-green-500"></i>
                                                <span>Lượt xem:</span>
                                                <strong>{{ number_format($staff->views_count ?? 0) }}</strong>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-gray-600">
                                            {{ $staff->created_at ? $staff->created_at->format('d/m/Y') : '' }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            <span
                                                class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $staff->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                                {{ $staff->status == 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <a href="{{ route('admin.content-staffs.show', $staff) }}"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 text-gray-600 transition"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye text-lg leading-none"
                                                        style="vertical-align: middle;"></i>
                                                </a>
                                                <a href="{{ route('admin.content-staffs.edit', $staff) }}"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit text-lg leading-none"
                                                        style="vertical-align: middle;"></i>
                                                </a>
                                                <button type="button"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-red-50 hover:bg-red-100 text-red-600 transition"
                                                    title="Xóa"
                                                    onclick="openModal('deleteStaffModal{{ $staff->id }}')">
                                                    <i class="fas fa-trash text-lg leading-none"
                                                        style="vertical-align: middle;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <div id="deleteStaffModal{{ $staff->id }}" class="modal" tabindex="-1">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận xóa nhân viên</h5>
                                                <button type="button" class="close"
                                                    onclick="closeModal('deleteStaffModal{{ $staff->id }}')">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="flex items-start">
                                                    <div
                                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                                    </div>
                                                    <div class="ml-4 text-left">
                                                        <p class="text-base text-gray-700">Bạn có chắc chắn muốn xóa nhân
                                                            viên "<strong>{{ $staff->name }}</strong>"?</p>
                                                        <p class="mt-1 text-sm text-gray-500"><strong
                                                                class="font-semibold text-red-600">Cảnh báo:</strong> Hành
                                                            động này sẽ chuyển nhân viên vào thùng rác và có thể khôi phục
                                                            sau.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary py-2 px-4 text-sm"
                                                    onclick="closeModal('deleteStaffModal{{ $staff->id }}')">Hủy</button>
                                                <form action="{{ route('admin.content-staffs.destroy', $staff) }}"
                                                    method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger py-2 px-4 text-sm">Xóa
                                                        nhân viên</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center px-6 py-10 text-gray-500">
                                            @if (request('search') || request('status'))
                                                Không tìm thấy nhân viên nào với bộ lọc hiện tại.
                                            @else
                                                Không có nhân viên nào.
                                            @endif
                                            <a href="{{ route('admin.content-staffs.create') }}"
                                                class="text-indigo-600 hover:underline ml-2">Thêm nhân viên mới?</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($contentStaffs->total() > 0)
                        <div class="mt-4 text-sm text-gray-600">
                            Tìm thấy <strong>{{ $contentStaffs->total() }}</strong> nhân viên
                            @if (request('search'))
                                với từ khóa "<strong>{{ request('search') }}</strong>"
                            @endif
                            @if (request('status') && request('status') == 'active')
                                trạng thái "<strong>Đang hoạt động</strong>"
                            @elseif (request('status') && request('status') == 'inactive')
                                trạng thái "<strong>Không hoạt động</strong>"
                            @endif
                        </div>
                    @endif
                </div>

                @if ($contentStaffs->hasPages())
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                            <p class="text-sm text-gray-700 leading-5">
                                Hiển thị từ
                                <span class="font-medium">{{ $contentStaffs->firstItem() }}</span>
                                đến
                                <span class="font-medium">{{ $contentStaffs->lastItem() }}</span>
                                trên tổng số
                                <span class="font-medium">{{ $contentStaffs->total() }}</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            {!! $contentStaffs->appends([
                                    'search' => request('search'),
                                    'status' => request('status'),
                                ])->links() !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');

            const hideToast = (toastElement) => {
                if (toastElement) {
                    toastElement.classList.add('hide');
                    setTimeout(() => {
                        toastElement.remove();
                    }, 350);
                }
            };

            toasts.forEach(toast => {
                const autoHideTimeout = setTimeout(() => {
                    hideToast(toast);
                }, 5000);

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
                    if (event.target.closest('.modal-content') === null && event.target.classList
                        .contains('modal')) {
                        closeModal(modal.id);
                    }
                });
            });

            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
                }
            });
        });
    </script>
@endpush
