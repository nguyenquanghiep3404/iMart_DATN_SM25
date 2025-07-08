<div class="my-6">
    <form id="comment-form" action="{{ route('comments.store') }}" method="POST"
        data-commentable-type="App\\Models\\Product" data-commentable-id="{{ $product->id }}"
        enctype="multipart/form-data" class="relative">
        @csrf
        <input type="hidden" name="commentable_type" value="App\Models\Product">
        <input type="hidden" name="commentable_id" value="{{ $product->id }}">

        <input type="file" name="images[]" id="comment-image" accept="image/*" multiple
            class="mt-1 mb-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
            file:rounded-lg file:border-0 file:text-sm file:font-semibold
            file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />

        <div class="flex items-center gap-2">
            <textarea id="comment-textarea" name="content" maxlength="3000" placeholder="Nhập nội dung bình luận..."
                class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>

            <span id="char-counter" class="absolute right-32 bottom-3 text-sm text-gray-400">0/3000</span>

            <button type="submit" id="comment-submit-btn"
                class="absolute right-2 bottom-1.5 bg-gray-800 text-white font-semibold py-2 px-5 rounded-lg hover:bg-gray-900 transition-colors">
                Gửi bình luận
            </button>
        </div>
    </form>
</div>

<div id="comments-list">
    @foreach ($comments as $comment)
        <div class="border-b border-gray-200 py-4">
            <div class="flex items-start gap-3">
                <img src="{{ $comment->user->avatar ?? 'https://placehold.co/40x40/7e22ce/ffffff?text=' . strtoupper(substr($comment->user->name ?? 'N', 0, 1)) }}"
                    alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                <div>
                    <p class="font-semibold text-gray-800">
                        {{ $comment->user->name ?? 'Khách' }}
                        @if (!empty($comment->user->is_admin))
                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold text-white bg-red-600 rounded">Quản trị
                                viên</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-600">{{ $comment->content }}</p>

                    @if ($comment->image_urls)
                        <div class="flex gap-2 mt-2">
                            @foreach ($comment->image_urls as $url)
                                <img src="{{ $url }}" alt="Review Image"
                                    class="w-20 h-20 rounded-md object-cover">
                            @endforeach
                        </div>
                    @endif

                    <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
    <script>
        const commentForm = document.getElementById('comment-form');
        const commentsList = document.getElementById('comments-list');
        const charCounter = document.getElementById('char-counter');
        const textarea = document.getElementById('comment-textarea');
        const maxChars = 3000;

        textarea.addEventListener('input', function() {
            charCounter.textContent = `${this.value.length}/${maxChars}`;
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(commentForm);

            try {
                const response = await fetch(commentForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    toastr?.error(data.message || 'Có lỗi khi gửi bình luận!');
                    return;
                }

                const comment = data.comment;
                const avatar = comment.avatar ||
                    `https://placehold.co/40x40/7e22ce/ffffff?text=${comment.initial || 'K'}`;

                const newCommentHTML = `
                <div class="border-b border-gray-200 py-4">
                    <div class="flex items-start gap-3">
                        <img src="${avatar}" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <p class="font-semibold text-gray-800">
                                ${escapeHtml(comment.name)}
                                ${comment.is_admin ? '<span class="ml-2 px-2 py-0.5 text-xs font-semibold text-white bg-red-600 rounded">Quản trị viên</span>' : ''}
                            </p>
                            <p class="text-sm text-gray-600">${escapeHtml(comment.content)}</p>
                            ${comment.images?.length ? `
                                                                                            <div class="flex gap-2 mt-2">
                                                                                                ${comment.images.map(url => `<img src="${url}" alt="Review Image" class="w-20 h-20 rounded-md object-cover">`).join('')}
                                                                                            </div>` : ''}
                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                                <span>${comment.time}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);
                commentForm.reset();
                charCounter.textContent = `0/${maxChars}`;
                commentForm.dataset.submitted = 'true'; // Đánh dấu đã gửi
                // ✅ Cập nhật lại URL (loại bỏ query hoặc trạng thái gửi)
                window.history.replaceState({}, document.title, window.location.pathname);
            } catch (error) {
                console.error('Lỗi khi gửi bình luận:', error);
                toastr?.error('Đã xảy ra lỗi khi gửi bình luận.');
            }
        });

        // Giới hạn 5 ảnh upload
        const commentImageInput = document.getElementById('comment-image');
        commentImageInput.addEventListener('change', function() {
            if (this.files.length > 5) {
                toastr.error('Bạn chỉ được chọn tối đa 5 ảnh.');
                this.value = '';
            }
        });

        // ✅ Ngăn reload gửi lại bình luận
        window.onbeforeunload = function() {
            if (commentForm.dataset.submitted === 'true') {
                return null;
            }
        };
    </script>
@endpush
