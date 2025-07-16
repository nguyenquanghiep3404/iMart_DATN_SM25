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
            @php
                $isAdmin = auth()->user() && auth()->user()->hasRole('admin');
            @endphp

            @if ($comment->user_id === $currentUserId || $isAdmin)
                @if ($comment->status === 'pending')
                    <div class="text-sm text-yellow-600 mt-1">
                        {{ $isAdmin ? 'Bình luận đang chờ phê duyệt' : 'Bình luận của bạn đang chờ duyệt' }}
                    </div>
                @elseif ($comment->status === 'rejected')
                    <div class="text-sm text-red-600 mt-1">
                        {{ $isAdmin ? 'Bị từ chối' : 'Bình luận của bạn bị từ chối' }}
                    </div>
                @elseif ($comment->status === 'spam' && ($comment->user_id === $currentUserId || $isAdmin))
                    <div class="text-sm text-red-600 mt-1">
                        {{ $isAdmin ? 'Bình luận này đã bị đánh dấu là spam' : 'Bình luận của bạn đã bị đánh dấu là spam' }}
                    </div>
                @endif
            @endif

            <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                <span>{{ $comment->created_at->diffForHumans() }}</span>

                {{-- Nút trả lời (nếu không phải bình luận của mình) --}}
                @if (auth()->check() &&
                        $comment->user_id !== $currentUserId &&
                        !in_array($comment->status, ['rejected', 'spam', 'pending']))
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
    @php
        $currentUserId = auth()->id();
        $isAdmin = auth()->user() && auth()->user()->hasRole('admin');

        $filteredReplies = $comment->replies->filter(function ($reply) use ($isAdmin, $currentUserId) {
            if ($isAdmin) {
                return true; // Admin xem tất cả
            }
            // Chủ comment hoặc chủ reply mới xem được reply chưa duyệt
            if ($reply->status !== 'approved' && $reply->user_id !== $currentUserId) {
                return false;
            }
            return true; // Các reply đã approved hoặc của chính user
        });
    @endphp

    @if ($filteredReplies->count())
        @foreach ($filteredReplies as $reply)
            @include('users.products.partials.recursive-comment', [
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
                <form action="{{ route('comments.store') }}" method="POST" enctype="multipart/form-data" class="mt-3 reply-form ajax-reply-form space-y-3">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="parent_id" value="${e.target.dataset.commentId}">
                    <input type="hidden" name="commentable_type" value="App\\Models\\Product">
                    <input type="hidden" name="commentable_id" value="{{ $product->id }}">
                    
                    <textarea name="content" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
                    placeholder="Nhập nội dung phản hồi..." required></textarea>
                    
                    <div class="flex items-center gap-4">
                    <label for="images-upload" class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 select-none transition duration-200">
                        Chọn ảnh
                    </label>
                    <input id="images-upload" type="file" name="images[]" accept="image/*" multiple class="hidden">
                    
                    <button type="submit" 
                        class="ml-auto bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                        Gửi phản hồi
                    </button>
                    </div>
                </form>
                `;
        }
    });
    document.addEventListener('submit', async function(e) {
        if (!e.target.matches('.ajax-reply-form')) return;
        e.preventDefault();

        const form = e.target;

        // Ngăn spam hoặc gửi lại nhiều lần
        if (form.dataset.submitted === 'true') return;

        form.dataset.submitted = 'true';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });

            const result = await response.json();

            const imagesHTML = (result.comment.images && result.comment.images.length > 0) ?
                `<div class="flex gap-2 mt-2 flex-wrap">
                ${result.comment.images.map(url => `<img src="${url}" alt="Ảnh bình luận" class="w-20 h-20 rounded-md object-cover">`).join('')}
            </div>` :
                '';
            if (result.success) {
                const newCommentHTML = `
                    <div class="ml-4 border-b border-gray-200 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white select-none"
                                style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
                                ${result.comment.initial}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    ${result.comment.name}
                                    ${result.comment.is_admin ? `<span class="ml-2 px-2 py-0.5 text-xs font-bold text-white rounded"
                                    style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">Quản trị viên</span>` : ''}
                                </p>
                                <p class="text-sm text-gray-600 whitespace-pre-wrap">${result.comment.content}</p>
                                ${imagesHTML}

                                ${result.comment.status === 'pending' ? `
                                    <div class="text-sm text-yellow-600 mt-1">
                                        ${result.comment.is_admin ? 'Bình luận đang chờ phê duyệt' : 'Bình luận của bạn đang chờ duyệt'}
                                    </div>
                                ` : ''}

                                <div class="text-xs text-gray-500 mt-2">
                                    <span>${result.comment.time}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                form.closest('.reply-form-container').insertAdjacentHTML('beforebegin', newCommentHTML);

                // Xóa form trả lời
                form.remove();

                // 🔒 Ngăn reload gửi lại bằng cách reset URL (xóa POST khỏi lịch sử trình duyệt)
                window.history.replaceState(null, document.title, window.location.pathname);
            } else {
                alert(result.message || 'Đã xảy ra lỗi.');
                form.dataset.submitted = 'false';
            }

        } catch (error) {
            console.error(error);
            alert('Có lỗi xảy ra khi gửi phản hồi.');
            form.dataset.submitted = 'false';
        }
    });
</script>
