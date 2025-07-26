<div class="my-6">
    <form id="comment-form" action="{{ route('comments.store') }}" method="POST" enctype="multipart/form-data"
        class="mb-6">
        @csrf
        <input type="hidden" name="commentable_type" value="App\Models\Product">
        <input type="hidden" name="commentable_id" value="{{ $product->id }}">

        <div style="display: flex; align-items: center; gap: 8px; mb-5">
            <textarea id="comment-textarea" name="content" maxlength="3000" placeholder="Nhập nội dung bình luận..." required
                style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 6px; resize: none; height: 50px;"></textarea>
            <button type="submit"
                style="background: black; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">
                Gửi bình luận
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;" class="mt-2">
            <label for="comment-image" id="add-image-label"
                style="width: 48px; height: 48px; border: 2px dashed #ccc; border-radius: 8px; 
                          display: flex; justify-content: center; align-items: center; cursor: pointer; 
                          font-size: 28px; font-weight: bold; user-select: none;">
                +
            </label>
            <input type="file" id="comment-image" name="images[]" accept="image/*,video/*" multiple
                style="display: none;" />
            <div id="image-preview" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
            <div id="add-info-text" style="font-size: 14px; color: #666; font-style: italic; margin-left: 8px;">
                Thêm tối đa 5 ảnh và 1 video
            </div>
        </div>


        <div style="text-align: right; font-size: 12px; color: #666; margin-top: 4px;" id="char-counter">0/3000</div>
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
    if (!window.isGuest) {
        localStorage.removeItem('guestComments');
    }

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

        // Xóa file input mặc định nếu có
        formData.delete('images[]');

        // Thêm các file đã chọn vào formData
        selectedFiles.forEach(file => {
            formData.append('images[]', file);
        });

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
                    const errors = Object.values(data.errors).flat();
                    errors.forEach(err => toastr.error(err));
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
                toastr.success('Gửi bình luận thành công! Bình luận của bạn sẽ được duyệt sớm.');
            } else {
                toastr.success('Gửi bình luận thành công!');
            }

            const comment = data.comment;

            if (comment.status !== 'approved' && !comment.is_owner && !comment.is_admin) return;

            renderComment(comment);

            if (data.is_guest) {
                saveGuestCommentToLocalStorage(comment);
            }

            commentForm.reset();
            charCounter.textContent = `0/${maxChars}`;
            selectedFiles = [];
            renderImages();
            document.getElementById('user-info-modal').classList.add('hidden');

        } catch (error) {
            console.error('Lỗi khi gửi bình luận:', error);
            toastr.error('Đã xảy ra lỗi khi gửi bình luận.');
        }
    }

    // Giới hạn tối đa 5 ảnh và 1 video
    const maxImages = 5;
    const maxVideos = 1;

    const imageInput = document.getElementById('comment-image');
    const imagePreview = document.getElementById('image-preview');
    const addInfoText = document.getElementById('add-info-text'); // phần text thông báo
    let selectedFiles = [];

    // Cập nhật dòng chữ "Thêm tối đa X ảnh và Y video"
    function updateAddInfoText() {
        const imageCount = selectedFiles.filter(f => f.type.startsWith('image/')).length;
        const videoCount = selectedFiles.filter(f => f.type.startsWith('video/')).length;

        const remainingImages = maxImages - imageCount;
        const remainingVideos = maxVideos - videoCount;

        addInfoText.textContent = `Thêm tối đa ${remainingImages} ảnh và ${remainingVideos} video`;
        const addImageLabel = document.getElementById('add-image-label');
        if (imageCount >= maxImages) {
            addImageLabel.style.pointerEvents = 'none';
            addImageLabel.style.opacity = '0.5'; // Cho thấy đã bị disable
            addImageLabel.style.cursor = 'default';
        } else {
            addImageLabel.style.pointerEvents = 'auto';
            addImageLabel.style.opacity = '1';
            addImageLabel.style.cursor = 'pointer';
        }
    }

    // Render ảnh và video preview, kèm nút xoá
    function renderImages() {
        imagePreview.innerHTML = '';

        if (selectedFiles.length === 0) {
            addInfoText.textContent = `Thêm tối đa ${maxImages} ảnh và ${maxVideos} video`;
            return;
        }

        selectedFiles.forEach((file, index) => {
            const url = URL.createObjectURL(file);

            const imgContainer = document.createElement('div');
            imgContainer.style.position = 'relative';
            imgContainer.style.width = '48px';
            imgContainer.style.height = '48px';
            imgContainer.style.borderRadius = '8px';
            imgContainer.style.overflow = 'hidden';
            imgContainer.style.flexShrink = '0';

            let mediaElement;
            if (file.type.startsWith('video/')) {
                mediaElement = document.createElement('video');
                mediaElement.src = url;
                mediaElement.style.width = '100%';
                mediaElement.style.height = '100%';
                mediaElement.style.objectFit = 'cover';
                mediaElement.muted = true;
                mediaElement.loop = true;
                mediaElement.autoplay = true;
            } else {
                mediaElement = document.createElement('img');
                mediaElement.src = url;
                mediaElement.style.width = '100%';
                mediaElement.style.height = '100%';
                mediaElement.style.objectFit = 'cover';
            }

            imgContainer.appendChild(mediaElement);

            const btnDelete = document.createElement('button');
            btnDelete.type = 'button';
            btnDelete.textContent = '×';
            btnDelete.style.position = 'absolute';
            btnDelete.style.top = '2px';
            btnDelete.style.right = '2px';
            btnDelete.style.background = 'rgba(0,0,0,0.6)';
            btnDelete.style.color = 'white';
            btnDelete.style.border = 'none';
            btnDelete.style.borderRadius = '50%';
            btnDelete.style.width = '18px';
            btnDelete.style.height = '18px';
            btnDelete.style.cursor = 'pointer';
            btnDelete.style.fontWeight = 'bold';
            btnDelete.style.lineHeight = '18px';
            btnDelete.style.textAlign = 'center';
            btnDelete.style.padding = '0';

            btnDelete.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                renderImages();
            });

            imgContainer.appendChild(btnDelete);
            imagePreview.appendChild(imgContainer);
        });

        updateAddInfoText();
    }

    // Xử lý khi chọn file mới
    imageInput.addEventListener('change', function() {
        const files = Array.from(this.files);

        // Kiểm tra tổng số ảnh + video
        const imageCount = selectedFiles.filter(f => f.type.startsWith('image/')).length;
        const videoCount = selectedFiles.filter(f => f.type.startsWith('video/')).length;

        // Kiểm tra số lượng ảnh/video mới thêm
        const newImageCount = files.filter(f => f.type.startsWith('image/')).length;
        const newVideoCount = files.filter(f => f.type.startsWith('video/')).length;

        if (imageCount + newImageCount > maxImages) {
            toastr.error(`Bạn chỉ được chọn tối đa ${maxImages} ảnh.`);
            this.value = '';
            return;
        }

        if (videoCount + newVideoCount > maxVideos) {
            toastr.error(`Bạn chỉ được chọn tối đa ${maxVideos} video.`);
            this.value = '';
            return;
        }

        selectedFiles = selectedFiles.concat(files);

        renderImages();

        // Reset input để có thể chọn lại file giống cũ nếu cần
        this.value = '';
    });

    // Modal xử lý thông tin khách vãng lai
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
        updateAddInfoText(); // cập nhật text ban đầu
    });
</script>
