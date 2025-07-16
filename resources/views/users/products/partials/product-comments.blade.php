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
    {{-- Hiển thị bình luận ở đây --}}
</div>

{{-- Modal thông tin khách vãng lai --}}
<div id="user-info-modal"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-transform duration-300 scale-95">
        <div class="flex justify-between items-center p-4 border-b flex-shrink-0">
            <h3 class="text-xl font-bold text-gray-900">Thông tin người gửi</h3>
            <button id="close-user-info-modal-btn"
                class="text-gray-500 hover:text-gray-700 text-3xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-4 flex-grow">
            <div class="flex gap-4">
                <label class="flex items-center"><input type="radio" name="gender" value="Anh"
                        class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" checked> <span
                        class="ml-2">Anh</span></label>
                <label class="flex items-center"><input type="radio" name="gender" value="Chị"
                        class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500"> <span
                        class="ml-2">Chị</span></label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" id="guest-name" placeholder="Nhập họ và tên"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <input type="tel" id="guest-phone" placeholder="Nhập số điện thoại"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <input type="email" id="guest-email" placeholder="Nhập Email (nhận thông báo phản hồi)"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="p-6 bg-white border-t flex-shrink-0">
            <label class="flex items-center text-sm text-gray-600">
                <input id="terms-checkbox" type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2">Tôi đồng ý với điều khoản dịch vụ, chính sách thu thập và xử lý dữ liệu cá nhân của
                    Shop</span>
            </label>
            <button id="qna-complete-btn"
                class="mt-4 w-full bg-gray-300 text-gray-500 font-semibold py-3 rounded-lg cursor-not-allowed"
                disabled>Hoàn tất</button>
        </div>
    </div>
</div>
<script>
    window.isGuest = {{ auth()->guest() ? 'true' : 'false' }};

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

    // Hàm render comment vào DOM
    function renderComment(comment) {
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
        commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);
    }

    // Lưu comment khách vãng lai vào localStorage
    function saveGuestCommentToLocalStorage(comment) {
        let comments = JSON.parse(localStorage.getItem('guestComments') || '[]');
        comments.push(comment);
        localStorage.setItem('guestComments', JSON.stringify(comments));
    }

    // Load comment khách vãng lai từ localStorage
    function loadGuestCommentsFromLocalStorage() {
        let comments = JSON.parse(localStorage.getItem('guestComments') || '[]');
        comments.forEach(comment => {
            renderComment(comment);
        });
    }

    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (window.isGuest) {
            document.getElementById('user-info-modal').classList.remove('hidden');
            document.getElementById('user-info-modal').classList.add('flex');
            return;
        }

        submitCommentForm();
    });

    async function submitCommentForm(extraData = {}) {
        const formData = new FormData(commentForm);
        Object.entries(extraData).forEach(([key, value]) => {
            formData.append(key, value);
        });

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

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    // 👉 Hiển thị từng lỗi bằng toastr
                    const errors = Object.values(data.errors).flat();
                    errors.forEach(err => toastr.error(err));

                    // 👉 Hiện lại modal nếu là khách vãng lai
                    if (window.isGuest) {
                        document.getElementById('user-info-modal').classList.remove('hidden');
                        document.getElementById('user-info-modal').classList.add('flex');
                    }
                } else {
                    toastr.error(data.message || 'Có lỗi khi gửi bình luận!');
                }
                return;
            }
            if (data.is_guest) {
                toastr?.success('Gửi bình luận thành công! Bình luận của bạn sẽ được duyệt sớm.');
            } else {
                toastr?.success('Gửi bình luận thành công!');
            }

            const comment = data.comment;
            // Nếu bình luận chưa duyệt và không phải của chính mình hoặc admin thì không hiện
            if (comment.status !== 'approved' && !comment.is_owner && !comment.is_admin) return;

            // Hiển thị comment ngay
            renderComment(comment);

            // Nếu là khách vãng lai thì lưu comment để load lại khi reload trang
            if (data.is_guest) {
                saveGuestCommentToLocalStorage(comment);
            }

            commentForm.reset();
            charCounter.textContent = `0/${maxChars}`;
            document.getElementById('user-info-modal').classList.add('hidden');

        } catch (error) {
            console.error('Lỗi khi gửi bình luận:', error);
            toastr?.error('Đã xảy ra lỗi khi gửi bình luận.');
        }
    }

    // Giới hạn tối đa 5 ảnh
    document.getElementById('comment-image').addEventListener('change', function() {
        if (this.files.length > 5) {
            toastr.error('Bạn chỉ được chọn tối đa 5 ảnh.');
            this.value = '';
        }
    });

    const completeBtn = document.getElementById('qna-complete-btn');
    const termsCheckbox = document.getElementById('terms-checkbox');

    termsCheckbox.addEventListener('change', function() {
        completeBtn.disabled = !this.checked;
        completeBtn.classList.toggle('bg-gray-800', this.checked);
        completeBtn.classList.toggle('text-white', this.checked);
        completeBtn.classList.toggle('cursor-pointer', this.checked);
        completeBtn.classList.toggle('bg-gray-300', !this.checked);
        completeBtn.classList.toggle('text-gray-500', !this.checked);
        completeBtn.classList.toggle('cursor-not-allowed', !this.checked);
    });

    completeBtn.addEventListener('click', function() {
        const name = document.getElementById('guest-name').value.trim();
        const phone = document.getElementById('guest-phone').value.trim();
        const email = document.getElementById('guest-email').value.trim();
        const gender = document.querySelector('input[name="gender"]:checked')?.value || '';

        if (!name || !phone || !email || !termsCheckbox.checked) {
            toastr.error('Vui lòng điền đầy đủ thông tin và đồng ý điều khoản.');
            return;
        }

        // Gửi bình luận kèm thông tin khách vãng lai
        submitCommentForm({
            guest_name: name,
            guest_phone: phone,
            guest_email: email,
            gender: gender,
        });

        // Đóng modal
        document.getElementById('user-info-modal').classList.add('hidden');
    });

    document.getElementById('close-user-info-modal-btn').addEventListener('click', function() {
        document.getElementById('user-info-modal').classList.add('hidden');
    });

    // Khi load trang, hiển thị comment khách vãng lai đã lưu
    window.addEventListener('DOMContentLoaded', () => {
        loadGuestCommentsFromLocalStorage();
    });
</script>

{{-- <script>
    window.isGuest = {{ auth()->guest() ? 'true' : 'false' }};

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

    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (window.isGuest) {
            document.getElementById('user-info-modal').classList.remove('hidden');
            document.getElementById('user-info-modal').classList.add('flex');
            return;
        }

        submitCommentForm();
    });

    async function submitCommentForm(extraData = {}) {
        const formData = new FormData(commentForm);
        // Thêm dữ liệu khách vãng lai (nếu có)
        Object.entries(extraData).forEach(([key, value]) => {
            formData.append(key, value);
        });

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
            if (data.is_guest) {
                toastr?.success('Gửi bình luận thành công! Bình luận của bạn sẽ được duyệt sớm.');
            } else {
                toastr?.success('Gửi bình luận thành công!');
            }

            const comment = data.comment;
            // Nếu bình luận chưa duyệt và không phải của chính mình hoặc admin thì không hiện
            if (comment.status !== 'approved' && !comment.is_owner && !comment.is_admin) return;

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

            commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);
            commentForm.reset();
            charCounter.textContent = `0/${maxChars}`;
            // Ẩn modal (nếu có)
            document.getElementById('user-info-modal').classList.add('hidden');

        } catch (error) {
            console.error('Lỗi khi gửi bình luận:', error);
            toastr?.error('Đã xảy ra lỗi khi gửi bình luận.');
        }
    }

    // Giới hạn tối đa 5 ảnh
    document.getElementById('comment-image').addEventListener('change', function() {
        if (this.files.length > 5) {
            toastr.error('Bạn chỉ được chọn tối đa 5 ảnh.');
            this.value = '';
        }
    });

    const completeBtn = document.getElementById('qna-complete-btn');
    const termsCheckbox = document.getElementById('terms-checkbox');

    termsCheckbox.addEventListener('change', function() {
        completeBtn.disabled = !this.checked;
        completeBtn.classList.toggle('bg-gray-800', this.checked);
        completeBtn.classList.toggle('text-white', this.checked);
        completeBtn.classList.toggle('cursor-pointer', this.checked);
        completeBtn.classList.toggle('bg-gray-300', !this.checked);
        completeBtn.classList.toggle('text-gray-500', !this.checked);
        completeBtn.classList.toggle('cursor-not-allowed', !this.checked);
    });

    completeBtn.addEventListener('click', function() {
        const name = document.getElementById('guest-name').value.trim();
        const phone = document.getElementById('guest-phone').value.trim();
        const email = document.getElementById('guest-email').value.trim();
        const gender = document.querySelector('input[name="gender"]:checked')?.value || '';

        if (!name || !phone || !email || !termsCheckbox.checked) {
            toastr.error('Vui lòng điền đầy đủ thông tin và đồng ý điều khoản.');
            return;
        }

        // Gửi bình luận kèm thông tin khách vãng lai
        submitCommentForm({
            guest_name: name,
            guest_phone: phone,
            guest_email: email,
            gender: gender,
        });

        // Đóng modal
        document.getElementById('user-info-modal').classList.add('hidden');
    });

    document.getElementById('close-user-info-modal-btn').addEventListener('click', function() {
        document.getElementById('user-info-modal').classList.add('hidden');
    });
</script> --}}
