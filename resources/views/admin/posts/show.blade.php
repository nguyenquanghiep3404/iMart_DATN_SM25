@extends('admin.layouts.app')

@section('title', 'Thông tin bài viết')

@push('styles')
    <style>
        .card {
            border-radius: 1rem;
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1);
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

        .preview-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .content-view {
            line-height: 1.8;
            font-size: 1rem;
            color: #1f2937;
        }

        .content-view * {
            max-width: 100%;
            box-sizing: border-box;
        }

        .content-view img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1rem auto;
            display: block;
            loading: lazy;
        }

        .content-view h1 {
            font-size: 1.5rem;
            margin: 1.5rem 0 0.75rem;
        }

        .content-view h2 {
            font-size: 1.25rem;
            margin: 1.25rem 0 0.75rem;
        }

        .content-view h3 {
            font-size: 1.1rem;
            margin: 1rem 0 0.5rem;
        }

        .content-view p {
            margin-bottom: 1rem;
        }

        .content-view ul,
        .content-view ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .meta-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
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

        .badge-pending {
            background-color: #f59e0b;
            color: white;
        }

        /* Thêm style cho tag-list và tag-item giống index.blade.php */
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

        @media (max-width: 768px) {
            .btn {
                width: 100%;
                justify-content: center;
            }

            .preview-image {
                width: 100%;
                max-height: 200px;
            }
        }

        .preview-image:hover {
            transform: scale(1.03);
            transition: transform 0.3s ease;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #111827;
                color: #f3f4f6;
            }

            .card {
                background: linear-gradient(to bottom, #1f2937, #111827);
                box-shadow: 0 6px 12px -2px rgba(255, 255, 255, 0.05);
            }

            .meta-item {
                border-bottom: 1px solid #374151;
            }

            .text-gray-700,
            .text-gray-900 {
                color: #f3f4f6;
            }

            .content-view {
                background-color: #1f2937;
                color: #e5e7eb;
                border-color: #374151;
            }

            .btn-primary {
                background-color: #6366f1;
            }

            .btn-primary:hover {
                background-color: #4f46e5;
            }

            .btn-secondary {
                background-color: #374151;
                color: #d1d5db;
                border: 1px solid #4b5563;
            }

            .btn-secondary:hover {
                background-color: #4b5563;
            }

            .btn-danger {
                background-color: #f87171;
            }

            .btn-danger:hover {
                background-color: #dc2626;
            }

            .badge-published {
                background-color: #10b981;
            }

            .badge-draft {
                background-color: #6b7280;
            }

            .badge-pending {
                background-color: #f59e0b;
            }

            .tag-item {
                background-color: #4b5563;
                color: #e5e7eb;
            }
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-4 md:px-8 py-8 bg-gray-50">
        <div class="container mx-auto max-w-screen-xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Thông tin bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-3 flex items-center text-sm text-gray-600">
                    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 transition">Bảng
                        điều khiển</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.posts.index') }}" class="text-indigo-600 hover:text-indigo-800 transition">Bài
                        viết</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-700 font-medium">Chi tiết</span>
                </nav>
            </div>

            <div class="card bg-white p-6 md:p-8">
                @if ($post->coverImage)
                    <div class="mb-6">
                        <img src="{{ Storage::url($post->coverImage->path) }}" alt="Ảnh đại diện" class="preview-image">
                        <p class="text-sm text-gray-600 text-center">Tên file: {{ $post->coverImage->original_name }}</p>
                    </div>
                @endif

                <div class="grid md:grid-cols-4 gap-8">
                    <!-- Cột trái: Meta -->
                    <div class="md:col-span-1 space-y-6">
                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Tiêu đề</label>
                            <p class="text-gray-900 leading-6">{{ $post->title }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Slug</label>
                            <p class="text-gray-900 leading-6">{{ $post->slug }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Danh mục</label>
                            <p class="text-gray-900 leading-6">{{ $post->category?->name ?? 'Không có' }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Thẻ (Tags)</label>
                            <div class="tag-list">
                                @forelse ($post->tags as $tag)
                                    <span class="tag-item">{{ $tag->name }}</span>
                                @empty
                                    <span class="text-gray-900">Không có</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Tác giả</label>
                            <p class="text-gray-900 leading-6">{{ $post->user?->name ?? 'Không xác định' }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Trạng thái</label>
                            <div class="flex items-center space-x-2">
                                <span
                                    class="badge {{ $post->status == 'published' ? 'badge-published' : ($post->status == 'draft' ? 'badge-draft' : 'badge-pending') }}">
                                    {{ $post->status == 'published' ? 'Đã xuất bản' : ($post->status == 'draft' ? 'Nháp' : 'Chờ duyệt') }}
                                </span>
                                @if ($post->status == 'published' && $post->published_at)
                                    <span class="text-gray-900 text-sm">
                                        {{ $post->published_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Lượt xem</label>
                            <p class="text-gray-900 leading-6">{{ $post->view_count ?? 0 }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Ngày tạo</label>
                            <p class="text-gray-900 leading-6">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Ngày cập nhật</label>
                            <p class="text-gray-900 leading-6">{{ $post->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">SEO Meta Title</label>
                            <p class="text-gray-900 leading-6">{{ $post->meta_title ?? 'Không có' }}</p>
                        </div>

                        <div class="meta-item">
                            <label class="font-semibold text-lg text-gray-700 block mb-2">SEO Meta Description</label>
                            <p class="text-gray-900 leading-6">{{ $post->meta_description ?? 'Không có' }}</p>
                        </div>
                    </div>

                    <!-- Cột phải: Nội dung -->
                    <div class="md:col-span-3 space-y-6">
                        <div>
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Tóm tắt</label>
                            <p class="text-gray-900 leading-7">{{ $post->excerpt ?: 'Không có' }}</p>
                        </div>

                        <div>
                            <label class="font-semibold text-lg text-gray-700 block mb-2">Nội dung</label>
                            <div class="content-view border border-gray-200 p-6 rounded-lg bg-white shadow-sm"
                                id="content-view">
                                {!! $post->content !!}

                            </div>
                            <button class="btn btn-secondary mt-2 hidden" id="toggle-content">Mở rộng</button>
                        </div>

                        <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3 pt-4">
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Quay lại
                            </a>
                            <a href="{{ route('admin.posts.preview', $post->id) }}" target="_blank"
                                class="btn btn-primary inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Xem trước
                            </a>

                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Chỉnh sửa
                            </a>
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7v12m6-12v12M10 3h4">
                                        </path>
                                    </svg>
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const contentView = document.getElementById('content-view');
            const toggleButton = document.getElementById('toggle-content');
            if (contentView.scrollHeight > 600) {
                contentView.style.maxHeight = '600px';
                contentView.style.overflowY = 'auto';
                toggleButton.classList.remove('hidden');
                toggleButton.addEventListener('click', () => {
                    if (contentView.style.maxHeight) {
                        contentView.style.maxHeight = null;
                        contentView.style.overflowY = 'visible';
                        toggleButton.textContent = 'Thu gọn';
                    } else {
                        contentView.style.maxHeight = '600px';
                        contentView.style.overflowY = 'auto';
                        toggleButton.textContent = 'Mở rộng';
                    }
                });
            }
        </script>
    @endpush
@endsection