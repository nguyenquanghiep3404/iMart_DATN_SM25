@extends('admin.layouts.app')

@section('title', 'Xem trước bài viết: ' . $post->title)

@push('styles')
    <style>
        .article-container {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .cover-image {
            max-width: 100%;
            width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .cover-image:hover {
            transform: scale(1.02);
        }

        .content-view {
            line-height: 1.9;
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
            margin-bottom: 1.5rem;
        }

        .content-view ul,
        .content-view ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
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

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            background: linear-gradient(to right, #a5b4fc, #6366f1);
            color: white;
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .tag svg {
            width: 14px;
            height: 14px;
            margin-right: 4px;
        }

        @media (max-width: 768px) {
            .article-container {
                padding: 1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-4 md:px-8 py-8 bg-gray-50">
        <div class="container mx-auto max-w-screen-xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Xem trước bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-3 flex items-center text-sm text-gray-600 space-x-2">
                    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 transition">Bảng điều khiển</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('admin.posts.index') }}" class="text-indigo-600 hover:text-indigo-800 transition">Bài viết</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('admin.posts.show', $post->id) }}" class="text-indigo-600 hover:text-indigo-800 transition">Chi tiết</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-700 font-medium">Xem trước</span>
                </nav>
            </div>

            <div class="article-container">
                <!-- Tiêu đề -->
                <h1 class="text-3xl md:text-4xl font-bold text-indigo-700 mb-4">{{ $post->title }}</h1>

                <!-- Ảnh đại diện -->
                @if ($post->coverImage)
                    <img src="{{ Storage::url($post->coverImage->path) }}" alt="{{ $post->title }}" class="cover-image">
                @endif

                <!-- Meta thông tin -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center text-sm text-gray-600 mb-6">
                    <div class="flex flex-wrap items-center">
                        <span class="mr-4"><strong>Tác giả:</strong> {{ $post->user?->name ?? 'Không xác định' }}</span>
                        <span class="mr-4">
                            <strong>Ngày đăng:</strong>
                            <time title="{{ $post->created_at->toDayDateTimeString() }}">
                                {{ $post->created_at->diffForHumans() }}
                            </time>
                        </span>
                        @if ($post->category)
                            <span class="mr-4"><strong>Danh mục:</strong> {{ $post->category->name }}</span>
                        @endif
                        <span><strong>Lượt xem:</strong> {{ $post->views ?? 0 }}</span>
                    </div>
                    <div class="mt-2 md:mt-0">
                        @if ($post->tags->isNotEmpty())
                            <div class="flex flex-wrap">
                                @foreach ($post->tags as $tag)
                                    <span class="tag">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M4 4l16 16" />
                                        </svg>
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tóm tắt -->
                @if ($post->excerpt)
                    <p class="text-lg text-gray-700 mb-6 italic">{{ $post->excerpt }}</p>
                @endif

                <!-- Nội dung -->
                <div class="content-view">
                    {!! $post->content !!}
                </div>

                <!-- Nút quay lại -->
                <div class="mt-8">
                    <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Quay lại chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
