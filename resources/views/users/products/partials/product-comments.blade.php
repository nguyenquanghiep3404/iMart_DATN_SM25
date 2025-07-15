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

        <div class="flex items-center gap-2 relative">
            <textarea id="comment-textarea" name="content" maxlength="3000" required placeholder="Nhập nội dung bình luận..."
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
    @foreach ($allComments as $comment)
        {{-- @include('users.products.partials.recursive-comment', ['comment' => $comment]) --}}
    @endforeach
</div>


@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('commentSubmitted') === 'true') {
                sessionStorage.removeItem('commentSubmitted');
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        const commentForm = document.getElementById('comment-form');
        const commentsList = document.getElementById('comments-list');
        const charCounter = document.getElementById('char-counter');
        const textarea = document.getElementById('comment-textarea');
        const maxChars = 3000;

        // Cập nhật số ký tự đã nhập
        textarea.addEventListener('input', function() {
            charCounter.textContent = `${this.value.length}/${maxChars}`;
        });

        // Hàm escape HTML để chống XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Xử lý gửi form bằng Ajax
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
                if (comment.status !== 'approved' && !comment.is_owner && !comment.is_admin) {
                    return;
                }
                const initial = comment.initial?.toUpperCase() || 'A';

                const adminBadge = comment.is_admin ?
                    `<span class="ml-2 px-2 py-0.5 text-xs font-bold text-white rounded" style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">Quản trị viên</span>` :
                    '';

                const imagesHtml = comment.images?.length ?
                    `<div class="flex gap-2 mt-2 flex-wrap">${comment.images.map(url => `<img src="${url}" alt="Ảnh bình luận" class="w-20 h-20 rounded-md object-cover">`).join('')}</div>` :
                    '';

                const newCommentHTML = `
                    <div class="border-b border-gray-200 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white select-none"
                                style="background: linear-gradient(45deg, #7b2ff7, #f107a3);">
                                ${initial}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    ${escapeHtml(comment.name)}
                                    ${adminBadge}
                                </p>
                                <p class="text-sm text-gray-600 whitespace-pre-wrap">${escapeHtml(comment.content)}</p>
                                ${comment.status === 'pending' && comment.is_owner ? `<div class="text-sm text-yellow-600 mt-1">Bình luận của bạn đang chờ duyệt</div>` : ''}
                                ${imagesHtml}
                                <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                                    <span>${comment.time}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;


                // Thêm comment mới vào đầu danh sách
                commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);

                // Cập nhật số lượng bình luận
                const countElem = document.getElementById('comments-count');
                if (countElem) {
                    let currentCount = parseInt(countElem.textContent) || 0;
                    currentCount++;
                    countElem.textContent = `${currentCount} Bình luận`;
                }

                commentForm.reset();
                charCounter.textContent = `0/${maxChars}`;
                commentForm.dataset.submitted = 'true'; // Đánh dấu đã gửi
                sessionStorage.setItem('commentSubmitted', 'true');

                // Xóa query string hoặc trạng thái gửi để tránh gửi lại khi reload trang
                window.history.replaceState({}, document.title, window.location.pathname);
            } catch (error) {
                console.error('Lỗi khi gửi bình luận:', error);
                toastr?.error('Đã xảy ra lỗi khi gửi bình luận.');
            }
        });

        // Giới hạn upload tối đa 5 ảnh
        const commentImageInput = document.getElementById('comment-image');
        commentImageInput.addEventListener('change', function() {
            if (this.files.length > 5) {
                toastr.error('Bạn chỉ được chọn tối đa 5 ảnh.');
                this.value = '';
            }
        });

        // Ngăn reload gửi lại bình luận khi refresh trang
        window.onbeforeunload = function() {
            if (commentForm.dataset.submitted === 'true') {
                return null;
            }
        };
    </script>
@endpush
