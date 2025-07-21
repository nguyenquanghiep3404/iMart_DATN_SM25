@php
    $userName = $comment->user->name ?? 'A';
    $initial = strtoupper(substr($userName, 0, 1));
    $currentUserId = auth()->id();
@endphp

<div class="ml-{{ $level ?? 0 }} border-b border-gray-200 py-4 w-full">
    <div class="flex items-start gap-3 w-full">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white select-none"
            style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
            {{ $initial }}
        </div>

        <div class="w-full">
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

                @if (auth()->check() &&
                        $comment->user_id !== $currentUserId &&
                        !in_array($comment->status, ['rejected', 'spam', 'pending']))
                    <button class="text-blue-600 hover:underline reply-btn" data-comment-id="{{ $comment->id }}">
                        Trả lời
                    </button>
                @endif
            </div>

            <div class="reply-form-container mt-2 w-full"></div>
        </div>
    </div>

    @php
        $filteredReplies = $comment->replies->filter(function ($reply) use ($isAdmin, $currentUserId) {
            if ($isAdmin) {
                return true;
            }
            return $reply->status === 'approved' || $reply->user_id === $currentUserId;
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
                <form action="{{ route('comments.store') }}" method="POST" enctype="multipart/form-data"
                    class="reply-form ajax-reply-form bg-gray-100 p-4 rounded-lg shadow-sm space-y-3 w-full">

                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="parent_id" value="${e.target.dataset.commentId}">
                    <input type="hidden" name="commentable_type" value="App\\Models\\Product">
                    <input type="hidden" name="commentable_id" value="{{ $product->id }}">

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700 font-medium">
                            <span class="inline-flex items-center gap-1">
                                <span class="text-lg">↩️</span> Đang trả lời:
                                <strong class="ml-1 text-black">
                                    ${e.target.closest('.border-b')?.querySelector('.font-semibold')?.innerText || 'Người dùng'}
                                </strong>
                            </span>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-black text-xl font-bold close-reply-form">&times;</button>
                    </div>

                    <div class="w-full flex items-start gap-3">
                        <div class="w-10 h-10 bg-gray-400 text-white rounded-full flex items-center justify-center font-bold select-none">
                            {{ strtoupper(substr(auth()->user()->name ?? 'KH', 0, 2)) }}
                        </div>

                        <div class="w-full flex flex-col gap-3 min-w-0">
                            <div class="flex flex-col gap-1 w-full">
                                <textarea name="content" required maxlength="3000" rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 resize-y min-h-[5rem] min-w-0"
                                    placeholder="Nhập nội dung bình luận..."></textarea>
                                <div class="text-right text-xs text-gray-500 comment-counter">0/3000</div>
                            </div>

                            <label for="images-upload" class="hidden cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 select-none transition duration-200 w-max">
                                Chọn ảnh
                            </label>
                            <input id="images-upload" type="file" name="images[]" accept="image/*" multiple class="hidden">

                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 rounded-md transition duration-200 h-10 w-max">
                                Gửi
                            </button>
                        </div>
                    </div>
                </form>
            `;

            const form = container.querySelector('form');
            const textarea = form.querySelector('textarea[name="content"]');
            const counter = form.querySelector('.comment-counter');

            textarea.addEventListener('input', () => {
                const length = textarea.value.length;
                counter.textContent = `${length}/3000`;
            });

        } else if (e.target.matches('.close-reply-form')) {
            const container = e.target.closest('.reply-form-container');
            if (container) container.innerHTML = '';
        }
    });

    document.addEventListener('submit', async function(e) {
        if (!e.target.matches('.ajax-reply-form')) return;
        e.preventDefault();

        const form = e.target;
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
            </div>` : '';

            if (result.success) {
                const newCommentHTML = `
                    <div class="ml-4 border-b border-gray-200 py-4 w-full">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white select-none"
                                style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
                                ${result.comment.initial}
                            </div>
                            <div class="w-full">
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
                form.remove();
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
