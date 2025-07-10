@php
    $userName = $comment->user->name ?? 'A';
    $initial = strtoupper(substr($userName, 0, 1));
    $currentUserId = auth()->id();
@endphp

<div class="ml-{{ $level ?? 0 }} border-b border-gray-200 py-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white select-none"
            style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
            {{ $initial }}
        </div>

        <div>
            <p class="font-semibold text-gray-800">
                {{ $comment->user->name ?? 'Khách' }}
                @if ($comment->user && $comment->user->hasRole('admin'))
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold text-white rounded"
                        style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
                        Quản trị viên
                    </span>
                @endif
            </p>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $comment->content }}</p>

            @if ($comment->image_urls && count($comment->image_urls) > 0)
                <div class="flex gap-2 mt-2 flex-wrap">
                    @foreach ($comment->image_urls as $url)
                        <img src="{{ $url }}" alt="Ảnh bình luận" class="w-20 h-20 rounded-md object-cover">
                    @endforeach
                </div>
            @endif

            <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                <span>{{ $comment->created_at->diffForHumans() }}</span>

                {{-- Nút trả lời (nếu không phải bình luận của mình) --}}
                @if ($comment->user_id !== $currentUserId)
                    <button class="text-blue-600 hover:underline reply-btn" data-comment-id="{{ $comment->id }}">
                        Trả lời
                    </button>
                @endif
            </div>

            {{-- Form trả lời sẽ được chèn động bằng JS nếu cần --}}
            <div class="reply-form-container mt-2"></div>
        </div>
    </div>

    {{-- Đệ quy hiển thị các câu trả lời --}}
    @if ($comment->replies && $comment->replies->count())
        @foreach ($comment->replies as $reply)
            @include('users.partials.show_product.recursive-comment', [
                'comment' => $reply,
                'level' => ($level ?? 0) + 4,
            ])
        @endforeach
    @endif
</div>
<script>
    const csrfToken = '{{ csrf_token() }}';

    document.addEventListener('click', function(e) {
        if (e.target.matches('.reply-btn')) {
            const commentDiv = e.target.closest('div.border-b');
            if (!commentDiv) return;
            const container = commentDiv.querySelector('.reply-form-container');
            if (!container) return;

            if (container.querySelector('form')) {
                container.innerHTML = '';
                return;
            }

            container.innerHTML = `
                <form action="{{ route('comments.store') }}" method="POST" class="mt-3 reply-form">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="parent_id" value="${e.target.dataset.commentId}">
                    <input type="hidden" name="commentable_type" value="App\\Models\\Product">
                    <input type="hidden" name="commentable_id" value="{{ $product->id }}">
                    <textarea name="content" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-400"
                        placeholder="Nhập nội dung phản hồi..." required></textarea>
                    <button type="submit"
                        class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                        Gửi phản hồi
                    </button>
                </form>
            `;
        }
    });
</script>
