@extends('admin.layouts.app')

@section('title', 'Thùng rác - Nhân viên Content')

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
            background: linear-gradient(to bottom, #ffffff, #f9fafb);
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

        .btn-success {
            background-color: #10b981;
            color: white;
            border: none;
        }

        .btn-success:hover {
            background-color: #059669;
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
            background-color: #f9fafb;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }

        .modal-footer > :not(:first-child) {
            margin-left: .5rem;
        }

        @media (prefers-color-scheme: dark) {
            .card {
                background: linear-gradient(to bottom, #1f2937, #111827);
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
    </style>
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')

        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Thùng rác - Nhân viên Content</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.content-staffs.index') }}"
                                class="text-indigo-600 hover:text-indigo-800">Nhân viên Content</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="bg-gray-50 p-5 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-3 sm:mb-0">Nhân viên trong thùng rác
                            ({{ $trashedContentStaffs->total() }})</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.content-staffs.index') }}"
                                class="btn btn-secondary inline-flex items-center text-sm">
                                <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="table w-full table-auto text-sm">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Nhân viên</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Hiệu suất</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Ngày xóa</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-700 w-48">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($trashedContentStaffs as $index => $staff)
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
                                            {{ $staff->deleted_at ? $staff->deleted_at->format('d/m/Y') : '' }}
                                        </td>
                                        <td class="px-6 py-4 align-middle text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <button type="button"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-green-50 hover:bg-green-100 text-green-600 transition"
                                                    title="Khôi phục"
                                                    onclick="openModal('restoreStaffModal{{ $staff->id }}')">
                                                    <i class="fas fa-undo text-lg leading-none"
                                                        style="vertical-align: middle;"></i>
                                                </button>
                                                <button type="button"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-red-50 hover:bg-red-100 text-red-600 transition"
                                                    title="Xóa vĩnh viễn"
                                                    onclick="openModal('forceDeleteStaffModal{{ $staff->id }}')">
                                                    <i class="fas fa-trash-alt text-lg leading-none"
                                                        style="vertical-align: middle;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Modal khôi phục -->
                                    <div id="restoreStaffModal{{ $staff->id }}" class="modal" tabindex="-1">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận khôi phục nhân viên</h5>
                                                <button type="button" class="close"
                                                    onclick="closeModal('restoreStaffModal{{ $staff->id }}')">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="flex items-start">
                                                    <div
                                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <i class="fas fa-undo text-green-600 text-xl"></i>
                                                    </div>
                                                    <div class="ml-4 text-left">
                                                        <p class="text-base text-gray-700">Bạn có chắc chắn muốn khôi phục
                                                            nhân viên "<strong>{{ $staff->name }}</strong>"?</p>
                                                        <p class="mt-1 text-sm text-gray-500">Nhân viên sẽ được khôi phục
                                                            về danh sách nhân viên content.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary py-2 px-4 text-sm"
                                                    onclick="closeModal('restoreStaffModal{{ $staff->id }}')">Hủy</button>
                                                <form action="{{ route('admin.content_staffs.restore', $staff->id) }}"
                                                    method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success py-2 px-4 text-sm">Khôi
                                                        phục</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal xóa vĩnh viễn -->
                                    <div id="forceDeleteStaffModal{{ $staff->id }}" class="modal" tabindex="-1">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận xóa vĩnh viễn nhân viên</h5>
                                                <button type="button" class="close"
                                                    onclick="closeModal('forceDeleteStaffModal{{ $staff->id }}')">
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
                                                        <p class="text-base text-gray-700">Bạn có chắc chắn muốn xóa vĩnh
                                                            viễn nhân viên "<strong>{{ $staff->name }}</strong>"?</p>
                                                        <p class="mt-1 text-sm text-gray-500"><strong
                                                                class="font-semibold text-red-600">Cảnh báo:</strong> Hành
                                                            động này không thể hoàn tác.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary py-2 px-4 text-sm"
                                                    onclick="closeModal('forceDeleteStaffModal{{ $staff->id }}')">Hủy
                                                </button>
                                                <form action="{{ route('admin.content_staffs.force-delete', $staff->id) }}"
                                                    method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger py-2 px-4 text-sm">Xóa
                                                        vĩnh viễn</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center px-6 py-10 text-gray-500">
                                            Không có nhân viên nào trong thùng rác.
                                            <a href="{{ route('admin.content-staffs.index') }}"
                                                class="text-indigo-600 hover:underline ml-2">Quay lại danh sách nhân
                                                viên?</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($trashedContentStaffs->total() > 0)
                        <div class="mt-4 text-sm text-gray-600">
                            Tìm thấy <strong>{{ $trashedContentStaffs->total() }}</strong> nhân viên trong thùng rác
                        </div>
                    @endif
                </div>

                @if ($trashedContentStaffs->hasPages())
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                            <p class="text-sm text-gray-700 leading-5">
                                Hiển thị từ
                                <span class="font-medium">{{ $trashedContentStaffs->firstItem() }}</span>
                                đến
                                <span class="font-medium">{{ $trashedContentStaffs->lastItem() }}</span>
                                trên tổng số
                                <span class="font-medium">{{ $trashedContentStaffs->total() }}</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            {!! $trashedContentStaffs->links() !!}
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