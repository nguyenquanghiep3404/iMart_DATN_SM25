@php
    $statusColors = [
        'approved' => 'bg-green-100 text-green-600',
        'pending' => 'bg-yellow-100 text-yellow-600',
        'rejected' => 'bg-red-100 text-red-600',
        'spam' => 'bg-purple-100 text-purple-600',
    ];

    $statusLabels = [
        'approved' => 'Đã duyệt',
        'pending' => 'Chờ duyệt',
        'rejected' => 'Từ chối',
        'spam' => 'Spam',
    ];

    // Giới hạn level để tránh thụt vào quá sâu (vd max 3)
    $marginLevel = $level > 3 ? 3 : $level;

    // Tính margin-left (vd: 20px * level)
    $marginLeft = $marginLevel * 20;
@endphp

<tr class="border-b hover:bg-indigo-50" style="margin-left: {{ $marginLeft }}px;">
    <td class="px-4 py-4">
        <div class="flex items-center space-x-3">
            @if ($level > 0)
                <span class="text-gray-400 text-lg">↪</span>
            @endif
            <div
                class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500 text-white uppercase font-bold">
                {{ strtoupper(mb_substr($comment->user->name ?? 'K', 0, 1)) }}
            </div>
        </div>
    </td>

    <td class="px-4 py-4 font-semibold text-gray-800">
        {{ $comment->user->name ?? 'Khách' }}
    </td>

    <td class="px-4 py-4 max-w-xs truncate" style="max-width: 300px;">
        {{ $comment->content }}
    </td>

    <td class="px-4 py-4 italic text-gray-600" style="max-width: 200px;">
        @php $type = class_basename($comment->commentable_type); @endphp
        @if ($type === 'Post')
            <span class="font-medium">Bài viết:</span> {{ $comment->commentable->title ?? 'N/A' }}
        @elseif ($type === 'Product')
            <span class="font-medium">Sản phẩm:</span> {{ $comment->commentable->name ?? 'N/A' }}
        @else
            {{ $comment->commentable_display ?? 'Không xác định' }}
        @endif
    </td>

    <td class="px-4 py-4 text-gray-600">
        {{ $comment->created_at->format('d/m/Y') }}
    </td>

    <td class="px-4 py-4">
        <span
            class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$comment->status] ?? 'bg-gray-100 text-gray-600' }}">
            {{ $statusLabels[$comment->status] ?? ucfirst($comment->status) }}
        </span>
    </td>

    <td class="px-4 py-4 align-middle">
        <div class="flex items-center space-x-2 justify-end">
            <!-- Nút Xem màu xanh dương nền nhạt, icon mắt -->
            <a href="{{ route('admin.comments.show', $comment->id) }}"
                class="flex items-center space-x-1 px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition focus:outline-none focus:ring focus:ring-blue-300"
                title="Xem" aria-label="Xem bình luận">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <span class="text-sm font-medium">Xem</span>
            </a>

            <!-- Nút Sửa màu tím nền nhạt, icon bút chì -->
            <a href="{{ route('admin.comments.edit', $comment->id) }}"
                class="flex items-center space-x-1 px-3 py-1.5 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition focus:outline-none focus:ring focus:ring-purple-300"
                title="Sửa" aria-label="Sửa bình luận">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
                <span class="text-sm font-medium">Sửa</span>
            </a>
        </div>
    </td>

</tr>

@if ($comment->replies)
    @foreach ($comment->replies as $reply)
        @include('admin.comments.comments-row', ['comment' => $reply, 'level' => $level + 1])
    @endforeach
@endif
