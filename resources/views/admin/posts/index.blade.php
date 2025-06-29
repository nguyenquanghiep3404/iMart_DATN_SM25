@extends('admin.layouts.app')

@section('title', 'Quản lý bài viết')

@push('styles')
    <style>
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

        .modal-footer> :not(:first-child) {
            margin-left: .5rem;
        }

        .form-input,
        .form-select {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            transition: all 0.2s ease-in-out;
            height: 2.5rem;
            /* Đảm bảo chiều cao đồng đều */
        }

        .form-input:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }

        .icon-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease-in-out;
        }

        .toast.hide {
            opacity: 0;
            transform: translateX(100%);
        }

        .post-image {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 0.25rem;
            loading: lazy;
        }

        .tag-list {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .tag-item {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-published {
            background-color: #10b981;
            color: white;
        }

        .badge-draft {
            background-color: #6b7280;
            color: white;
        }

        .badge-pending_review {
            background-color: #f59e0b;
            color: white;
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

            .text-gray-600,
            .text-gray-700 {
                color: #d1d5db;
            }

            .tag-item {
                background-color: #4b5563;
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
                <h1 class="text-3xl font-bold text-gray-800">Quản lý bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Bài viết</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="bg-gray-50 p-5 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-3 sm:mb-0">Tất cả bài viết ({{ $posts->total() }})
                        </h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.posts.create') }}"
                                class="btn btn-primary inline-flex items-center text-sm">
                                <i class="fas fa-plus-circle mr-2"></i> Thêm bài viết mới
                            </a>
                            <a href="{{ route('admin.posts.trashed') }}"
                                class="btn btn-secondary inline-flex items-center text-sm">
                                <i class="fas fa-trash mr-2"></i> Thùng rác
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div
                        class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-3 md:space-y-0 md:space-x-3">
                        <form action="{{ route('admin.posts.index') }}" method="GET"
                            class="w-full flex flex-col md:flex-row items-center space-y-3 md:space-y-0 md:space-x-3">
                            <div class="flex-grow">
                                <input type="text" name="search" class="form-input w-full py-2.5 text-sm"
                                    placeholder="Tìm kiếm theo tiêu đề, nội dung hoặc tag..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="w-full md:w-1/5">
                                <select name="status" class="form-select w-full py-2.5 text-sm">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã
                                        xuất bản</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp
                                    </option>
                                    <option value="pending_review"
                                        {{ request('status') == 'pending_review' ? 'selected' : '' }}>Chờ duyệt</option>
                                </select>
                            </div>
                            <div class="w-full md:w-1/5">
                                <select name="category_id" class="form-select w-full py-2.5 text-sm">
                                    <option value="">Tất cả danh mục</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/5">
                                <select name="user_id" class="form-select w-full py-2.5 text-sm">
                                    <option value="">Tất cả tác giả</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="btn btn-primary h-full py-2 text-sm">
                                    <i class="fas fa-search mr-2"></i> Tìm kiếm
                                </button>
                                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary h-full py-2 text-sm">
                                    <i class="fas fa-times mr-2"></i> Xóa bộ lọc
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="table w-full table-auto text-sm">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left">STT</th>
                                    <th class="px-4 py-3 text-left">Ảnh</th>
                                    <th class="px-4 py-3 text-left">Tiêu đề</th>
                                    <th class="px-4 py-3 text-left">Danh mục</th>
                                    <th class="px-4 py-3 text-left">Thẻ tag</th>
                                    <th class="px-4 py-3 text-left">Tác giả</th>
                                    <th class="px-4 py-3 text-left">Trạng thái</th>
                                    <th class="px-4 py-3 text-left w-48">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($posts as $index => $post)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($post->coverImage)
                                                <img src="{{ Storage::url($post->coverImage->path) }}"
                                                    alt="{{ $post->title }}" class="post-image">
                                            @else
                                                <span class="text-gray-400 text-sm">Không có ảnh</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.posts.show', $post) }}"
                                                class="text-indigo-600 hover:text-indigo-800 font-semibold">{{ $post->title }}</a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $post->category ? $post->category->name : 'Không có' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="tag-list">
                                                @forelse ($post->tags as $tag)
                                                    <span class="tag-item">{{ $tag->name }}</span>
                                                @empty
                                                    <span class="text-gray-400">Không có thẻ</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $post->user ? $post->user->name ?? 'Không xác định' : 'Không xác định' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="badge {{ $post->status == 'published' ? 'badge-published' : ($post->status == 'draft' ? 'badge-draft' : 'badge-pending_review') }}">
                                                    {{ $post->status == 'published' ? 'Đã xuất bản' : ($post->status == 'draft' ? 'Nháp' : 'Chờ duyệt') }}
                                                </span>
                                                @if ($post->status == 'published' && $post->published_at)
                                                    <span class="text-gray-900 text-sm">
                                                        {{ $post->published_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.posts.show', $post) }}"
                                                    class="btn btn-secondary p-2 text-xs" title="Xem bài viết">
                                                    <i class="fas fa-eye text-base"></i>
                                                </a>
                                                <a href="{{ route('admin.posts.edit', $post) }}"
                                                    class="btn btn-primary p-2 text-xs" title="Chỉnh sửa bài viết">
                                                    <i class="fas fa-edit text-base"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger p-2 text-xs"
                                                    title="Xóa bài viết"
                                                    onclick="openModal('deletePostModal{{ $post->id }}')">
                                                    <i class="fas fa-trash text-base"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <div id="deletePostModal{{ $post->id }}" class="modal" tabindex="-1">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận xóa bài viết</h5>
                                                <button type="button" class="close"
                                                    onclick="closeModal('deletePostModal{{ $post->id }}')"><span
                                                        aria-hidden="true">×</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="flex items-start">
                                                    <div
                                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                                    </div>
                                                    <div class="ml-4 text-left">
                                                        <p class="text-base text-gray-700">Bạn có chắc chắn muốn xóa bài
                                                            viết "<strong>{{ $post->title }}</strong>"?</p>
                                                        <p class="mt-1 text-sm text-gray-500"><strong
                                                                class="font-semibold text-red-600">Cảnh báo:</strong> Hành
                                                            động này không thể hoàn tác.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary py-2 px-4 text-sm"
                                                    onclick="closeModal('deletePostModal{{ $post->id }}')">Hủy</button>
                                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                                    style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger py-2 px-4 text-sm">Xóa
                                                        bài viết</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center px-4 py-10 text-gray-500">
                                            @if (request('search') || request('status') || request('category_id') || request('user_id'))
                                                Không tìm thấy bài viết nào với bộ lọc hiện tại.
                                            @else
                                                Không có bài viết nào.
                                            @endif
                                            <a href="{{ route('admin.posts.create') }}"
                                                class="text-indigo-600 hover:underline ml-2">Thêm bài viết mới?</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($posts->total() > 0)
                        @php
                            $statusLabels = [
                                'published' => 'Đã xuất bản',
                                'draft' => 'Nháp',
                                'pending_review' => 'Chờ duyệt',
                            ];
                        @endphp
                        <div class="mt-4 text-sm text-gray-600">
                            Tìm thấy <strong>{{ $posts->total() }}</strong> bài viết
                            @if (request('search'))
                                với từ khóa "<strong>{{ request('search') }}</strong>"
                            @endif
                            @php
                                $status = request('status');
                            @endphp
                            @if ($status && array_key_exists($status, $statusLabels))
                                trạng thái "<strong>{{ $statusLabels[$status] }}</strong>"
                            @endif
                            @if (request('category_id') && $categories->find(request('category_id')))
                                trong danh mục "<strong>{{ $categories->find(request('category_id'))->name }}</strong>"
                            @endif
                            @if (request('user_id') && $users->find(request('user_id')))
                                bởi tác giả "<strong>{{ $users->find(request('user_id'))->name }}</strong>"
                            @endif
                        </div>
                    @endif
                </div>

                @if ($posts->hasPages())
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                            <p class="text-sm text-gray-700 leading-5">
                                Hiển thị từ
                                <span class="font-medium">{{ $posts->firstItem() }}</span>
                                đến
                                <span class="font-medium">{{ $posts->lastItem() }}</span>
                                trên tổng số
                                <span class="font-medium">{{ $posts->total() }}</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            {!! $posts->appends([
                                    'search' => request('search'),
                                    'status' => request('status'),
                                    'category_id' => request('category_id'),
                                    'user_id' => request('user_id'),
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
