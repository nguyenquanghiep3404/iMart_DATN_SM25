@extends('admin.layouts.app')

@section('title', 'Chi tiết thẻ bài viết')

@push('styles')
    <style>
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .detail-item {
            margin-bottom: 1rem;
        }

        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.875rem;
            color: #374151;
        }

        table th,
        table td {
            padding: 0.75rem;
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')

        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Chi tiết thẻ bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="mx-2">/</li>
                        <li><a href="{{ route('admin.post-tags.index') }}" class="text-indigo-600 hover:text-indigo-800">Thẻ bài viết</a></li>
                        <li class="mx-2">/</li>
                        <li class="text-gray-700 font-medium">Chi tiết</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="p-5">
                    <table class="min-w-full text-sm text-left border border-gray-200">
            <tbody>
                <tr class="border-b">
                    <th class="w-1/3 p-3 font-medium text-gray-600 bg-gray-50">ID thẻ</th>
                    <td class="p-3 text-gray-800">{{ $postTag->id }}</td>
                </tr>
                <tr class="border-b">
                    <th class="p-3 font-medium text-gray-600 bg-gray-50">Tên thẻ</th>
                    <td class="p-3 text-gray-800">{{ $postTag->name }}</td>
                </tr>
                <tr class="border-b">
                    <th class="p-3 font-medium text-gray-600 bg-gray-50">Slug</th>
                    <td class="p-3 text-gray-800">{{ $postTag->slug }}</td>
                </tr>
                <tr class="border-b">
                    <th class="p-3 font-medium text-gray-600 bg-gray-50">Ngày tạo</th>
                    <td class="p-3 text-gray-800">{{ $postTag->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th class="p-3 font-medium text-gray-600 bg-gray-50">Ngày cập nhật</th>
                    <td class="p-3 text-gray-800">{{ $postTag->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            </tbody>
        </table>
                </div>
            </div>

            {{-- Danh sách bài viết --}}
            <div class="card bg-white mt-8">
                <div class="p-5">
                    <div class="detail-item">
                        <label class="detail-label text-lg font-semibold">Bài viết có gắn thẻ này</label>
                        @if ($posts->count())
                            <div class="overflow-x-auto border rounded mt-2">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tiêu đề</th>
                                            <th>Người viết</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($posts as $post)
                                            <tr class="border-t">
                                                <td>{{ $post->id }}</td>
                                                <td>{{ $post->title }}</td>
                                                <td>{{ $post->user->name ?? 'Không rõ' }}</td>
                                                <td>{{ $post->created_at->format('d/m/Y') }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('admin.posts.show', $post) }}"
                                                            class="btn btn-secondary p-2 text-xs" title="Xem chi tiết">
                                                            <i class="fas fa-eye text-base"></i>
                                                        </a>
                                                        <a href="{{ route('admin.posts.edit', $post) }}"
                                                            class="btn btn-primary p-2 text-xs"
                                                            title="Chỉnh sửa bài viết"><i
                                                                class="fas fa-edit text-base"></i></a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 mt-2">Không có bài viết nào gắn thẻ này.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
