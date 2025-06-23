@extends('admin.comments.layouts.main')

@section('content')
<div class="bg-white rounded-md shadow p-6">

    <!-- Search & Filters -->
    <form action="{{ route('admin.comment.index') }}" method="GET">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Bình luận</h1>
            <p class="text-gray-500 mt-1">
                Xem, duyệt và quản lý tất cả các bình luận trên bài viết của bạn.
            </p>
        </header>
    
        <!-- Filter Section -->
        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nội dung, tên người gửi..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>
    
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái bình luận</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
    
                <!-- Date Filter -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày gửi</label>
                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ request('date') }}"
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
            </div>
    
            <div class="mt-4 flex justify-end space-x-3">
                <a
                    href="{{ route('admin.comment.index') }}"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold"
                >
                    Xóa lọc
                </a>
                <button
                    type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2"
                >
                    <i class="fas fa-filter"></i>
                    <span>Áp dụng</span>
                </button>
            </div>
        </div>
    </form>
    
    <!-- Danh sách bình luận -->
    <div class="overflow-x-auto">
        <table class="min-w-[1000px] w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100">
                <tr class="text-xs font-semibold text-gray-600 uppercase">
                    <th class="px-4 py-3"></th>
                    <th class="px-4 py-3">Người bình luận</th>
                    <th class="px-4 py-3">Nội dung</th>
                    <th class="px-4 py-3">Phản hồi cho</th>
                    <th class="px-4 py-3">Ngày gửi</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $comment)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <!-- Avatar -->
                    <td class="px-4 py-4">
                        <div class="flex items-center space-x-3">
                            @if ($comment->parent_id)
                                <span class="text-gray-400 text-lg">↪</span>
                            @endif
                            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500 text-white uppercase font-bold">
                                {{ strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                            </div>
                        </div>
                    </td>

                    <!-- Người dùng -->
                    <td class="px-4 py-4">
                        <div class="font-semibold text-gray-800">{{ $comment->user->name }}</div>
                    </td>

                    <!-- Nội dung -->
                    <td class="px-4 py-4 max-w-xs truncate text-gray-700">
                        {{ $comment->content }}
                    </td>

                    <!-- Đối tượng bình luận -->
                    <td class="px-4 py-4 italic text-gray-600">
                        @php $type = class_basename($comment->commentable_type); @endphp
                        @if ($type === 'Post')
                            <span class="font-medium">Bài viết:</span> {{ $comment->commentable->title }}
                        @elseif ($type === 'Product')
                            <span class="font-medium">Sản phẩm:</span> {{ $comment->commentable->name }}
                        @else
                            {{ $comment->commentable_display ?? 'Không xác định' }}
                        @endif
                    </td>

                    <!-- Ngày gửi -->
                    <td class="px-4 py-4 text-gray-600">
                        {{ $comment->created_at->format('d/m/Y') }}
                    </td>

                    <!-- Trạng thái -->
                    <td class="px-4 py-4">
                        @php
                            $statusColors = [
                                'approved' => 'bg-green-100 text-green-600',
                                'pending' => 'bg-yellow-100 text-yellow-600',
                                'rejected' => 'bg-red-100 text-red-600',
                                'spam' => 'bg-purple-100 text-purple-600',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$comment->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst(__($comment->status)) }}
                        </span>
                    </td>

                    <!-- Hành động -->
                    <td class="px-4 py-4">
                        <div class="flex items-center space-x-2 justify-end">
                            <a href="{{ route('admin.comments.show', $comment->id) }}" class="text-blue-600 hover:text-blue-800" title="Xem">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 576 512">
                                    <path d="M572.52 241.4C518.6 135.5 407.4 64 288 64S57.4 135.5 3.48 241.4a48.07 48.07 0 0 0 0 29.2C57.4 376.5 168.6 448 288 448s230.6-71.5 284.52-177.4a48.07 48.07 0 0 0 0-29.2zM288 400c-70.7 0-128-57.3-128-128s57.3-128 128-128 128 57.3 128 128-57.3 128-128 128zm0-208a80 80 0 1 0 80 80 80 80 0 0 0-80-80z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.comments.edit', $comment->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Sửa">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 512 512">
                                    <path d="M493.2 56.1L455.9 18.8c-25-25-65.5-25-90.5 0L127.2 256.9c-2.7 2.7-4.7 6.1-5.6 9.8L96.1 361.1c-2.4 9.7 6.5 18.6 16.2 16.2l94.4-25.5c3.7-.9 7.1-2.9 9.8-5.6L493.2 146.6c25.1-25 25.1-65.5 0-90.5zM386.6 186.6L325.4 125.4 371.2 79.6l61.2 61.2-45.8 45.8zM154.7 307.3l66.7 66.7-59.1 15.9-23.5-23.5 15.9-59.1z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500 italic">Không có bình luận nào phù hợp.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Hiển thị {{ $parentComments->firstItem() }} đến {{ $parentComments->lastItem() }} trong tổng số {{ $parentComments->total() }} bình luận
        </div>
        <div>
            {{ $parentComments->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
