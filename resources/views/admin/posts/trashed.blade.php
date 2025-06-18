@extends('admin.layouts.app')

@section('title', 'Thùng rác bài viết')

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

            .btn-secondary {
                background-color: #374151;
                color: #d1d5db;
                border-color: #4b5563;
            }

            .btn-secondary:hover {
                background-color: #4b5563;
            }

            .btn-danger {
                background-color: #dc2626;
            }

            .badge-published {
                background-color: #10b981;
            }

            .badge-draft {
                background-color: #6b7280;
            }

            .badge-pending_review {
                background-color: #f59e0b;
            }
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')

        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Thùng rác bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="bg-gray-50 p-5 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-3 sm:mb-0">Danh sách bài viết bị xóa ({{ $posts->total() }})</h3>
                    </div>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="table w-full table-auto text-sm">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left">STT</th>
                                    <th class="px-4 py-3 text-left">Tiêu đề</th>
                                    <th class="px-4 py-3 text-left">Danh mục</th>
                                    <th class="px-4 py-3 text-left">Tác giả</th>
                                    <th class="px-4 py-3 text-left">Trạng thái</th>
                                    <th class="px-4 py-3 text-left">Ngày xóa</th>
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
                                            <a href="{{ route('admin.posts.show', $post) }}"
                                                class="text-indigo-600 hover:text-indigo-800 font-semibold">{{ $post->title }}</a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $post->category ? $post->category->name : 'Không có' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $post->user ? $post->user->name ?? 'Không xác định' : 'Không xác định' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="badge {{ $post->status == 'published' ? 'badge-published' : ($post->status == 'draft' ? 'badge-draft' : 'badge-pending_review') }}">
                                                {{ $post->status == 'published' ? 'Đã xuất bản' : ($post->status == 'draft' ? 'Nháp' : 'Chờ duyệt') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $post->deleted_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex space-x-2">
                                                <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Khôi phục bài viết này?');">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-secondary p-2 text-xs">
                                                        <i class="fas fa-undo text-base"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.posts.forceDelete', $post->id) }}"
                                                    method="POST" style="display: inline;"
                                                    onsubmit="return confirm('Xóa vĩnh viễn bài viết này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger p-2 text-xs">
                                                        <i class="fas fa-trash text-base"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center px-4 py-10 text-gray-500">
                                            Không có bài viết nào trong thùng rác.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($posts->total() > 0)
                        <div class="mt-4 text-sm text-gray-600">
                            Tìm thấy <strong>{{ $posts->total() }}</strong> bài viết bị xóa.
                        </div>
                    @endif

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
                                {!! $posts->links() !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection