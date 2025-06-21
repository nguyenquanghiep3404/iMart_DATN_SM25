@extends('admin.comments.layouts.main')

@section('content')
<div class="bg-white rounded-md shadow p-6">
    <!-- Search & Filters -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-1/3">
            <input type="text" placeholder="Search by product name"
                   class="input w-full h-[44px] pl-12 pr-4 border rounded-md focus:ring-blue-500 focus:border-blue-500">
            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 17C13.4 17 17 13.4 17 9C17 4.6 13.4 1 9 1C4.6 1 1 4.6 1 9C1 13.4 4.6 17 9 17Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 19L14.7 14.7" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>

        <!-- Filter -->
        <form action="{{ route('admin.comment.index') }}" method="GET" class="flex items-center space-x-2">
            <label for="status-filter" class="text-sm font-medium">Trạng thái:</label>
            <select name="status" id="status-filter"
                    class="border rounded px-4 py-2 text-sm focus:ring focus:border-blue-400"
                    onchange="this.form.submit()">
                <option value="all">Tất cả</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                <option value="spam" {{ request('status') == 'spam' ? 'selected' : '' }}>Spam</option>
                {{-- <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Đã xóa</option> --}}
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-[1000px] w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100">
                <tr class="text-xs font-semibold text-gray-600 uppercase">
                    <th class="px-4 py-3">
                        <input type="checkbox" id="selectAllProduct">
                    </th>
                    <th class="px-4 py-3">Comment</th>
                    <th class="px-4 py-3 text-right">Customer</th>
                    <th class="px-4 py-3 text-right">Type</th>
                    <th class="px-4 py-3 text-right">Target</th>
                    <th class="px-4 py-3 text-right">Parent ID</th>
                    <th class="px-4 py-3 text-right">Status</th>
                    <th class="px-4 py-3 text-right">Created</th>
                    <th class="px-4 py-3 text-right">Updated</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comments as $comment)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="selected[]" value="{{ $comment->id }}">
                    </td>
                    <td class="px-4 py-3 max-w-xs truncate">
                        {{ $comment->content }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->user->name }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->readable_type }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->commentable_display }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->parent_id }}
                    </td>
                    <td class="px-4 py-3 text-right capitalize">
                        {{ $comment->status }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $comment->updated_at->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2 whitespace-nowrap">
                            <a href="{{ route('admin.comments.show', $comment->id) }}"
                               class="inline-flex items-center px-3 py-2 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 shadow"
                               title="Xem">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 576 512"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M572.52 241.4C518.48 135.1 407.28 64 288 64S57.52 135.1 3.48 241.4a48.54 48.54 0 0 0 0 29.2C57.52 376.9 168.72 448 288 448s230.48-71.1 284.52-177.4a48.54 48.54 0 0 0 0-29.2zM288 400c-88.22 0-160-71.78-160-160s71.78-160 160-160 160 71.78 160 160-71.78 160-160 160zm0-256a96 96 0 1 0 96 96 96.11 96.11 0 0 0-96-96z"/>
                                </svg>
                                Xem
                            </a>

                            <a href="{{ route('admin.comments.edit', $comment->id) }}"
                               class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 shadow"
                               title="Sửa">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 512 512"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M362.7 19.31c25-25 65.5-25 90.5 0l39.5 39.5c25 25 25 65.5 0 90.5l-39.6 39.6L323.1 58.91l39.6-39.6zM299.8 82.22L59.6 322.4C52.8 329.2 48 337.7 45.3 346.9L1.5 483.6c-2.1 6.8-.1 14.1 5.4 19.6s12.8 7.5 19.6 5.4l136.7-43.8c9.2-2.6 17.7-7.5 24.4-14.2l240.2-240.2-128.1-128.1z"/>
                                </svg>
                                Sửa
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Hiển thị {{ $comments->firstItem() }} đến {{ $comments->lastItem() }} trong tổng số {{ $comments->total() }} bình luận
        </div>
        <div>
            {{ $comments->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
