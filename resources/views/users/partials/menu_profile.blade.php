<aside class="col-lg-3">
    <div class="offcanvas-lg offcanvas-start pe-lg-0 pe-xl-4" id="accountSidebar">

        <div class="d-flex align-items-center">
            <div class="position-relative">
                <div role="button" data-bs-toggle="modal" data-bs-target="#chooseAvatarModal">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar_url }}" alt="Avatar" class="rounded-circle flex-shrink-0"
                            style="width: 3rem; height: 3rem; object-fit: cover;">
                    @else
                        <div class="h5 d-flex justify-content-center align-items-center flex-shrink-0 text-primary bg-primary-subtle lh-1 rounded-circle mb-0"
                            style="width: 3rem; height: 3rem;">
                            {{ strtoupper(Auth::user()->name[0]) }}
                        </div>
                    @endif
                </div>

                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-0 border"
                    style="transform: translate(30%, 30%); cursor: pointer;" data-bs-toggle="modal"
                    data-bs-target="#chooseAvatarModal">
                    <i class="bi bi-camera-fill text-muted fs-10 "></i>
                </div>
            </div>

            <div class="min-w-0 ps-3">
                <h5 class="h6 mb-1">{{ Auth::user()->name }}</h5>
            </div>
        </div>

        <div class="modal fade" id="chooseAvatarModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    <div class="modal-header">
                        <h5 class="modal-title">Ảnh hồ sơ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Ảnh hồ sơ giúp người khác nhận ra bạn.</p>
                        @if (Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="Avatar"
                                class="rounded-circle flex-shrink-0 d-block mx-auto"
                                style="width: 15rem; height: 15rem; object-fit: cover;">
                        @else
                            <div class="h5 d-flex justify-content-center align-items-center flex-shrink-0 text-primary bg-primary-subtle lh-1 rounded-circle mb-0"
                                style="width: 15rem; height: 15rem;">
                                {{ strtoupper(Auth::user()->name[0]) }}
                            </div>
                        @endif
                        <input type="file" id="chooseAvatarInput" accept="image/*" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button class="btn btn-primary" id="openCropStep" disabled>Tiếp theo</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="offcanvas-body d-block pt-2 pt-lg-4 pb-lg-0">
        <nav class="list-group list-group-borderless">
            <a class="list-group-item list-group-item-action d-flex align-items-center" href="{{ route('orders.index') }}">
                <i class="ci-shopping-bag fs-base opacity-75 me-2"></i>
                Đơn hàng của tôi
                <span class="badge bg-primary rounded-pill ms-auto">1</span>
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center" href="{{ route('wishlist.index') }}">
                <i class="ci-heart fs-base opacity-75 me-2"></i>
                Danh sách yêu thích
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center" href="account-payment.html">
                <i class="ci-credit-card fs-base opacity-75 me-2"></i>
                Phương thức thanh toán
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center"
                href="{{ route('reviews.index') }}">
                <i class="ci-star fs-base opacity-75 me-2"></i>
                Đánh giá của tôi
            </a>
        </nav>
        <h6 class="pt-4 ps-2 ms-1">Manage account</h6>
        <nav class="list-group list-group-borderless">
            <a class="list-group-item list-group-item-action d-flex align-items-center"
                href="{{ route('profile.edit') }}">
                <i class="ci-user fs-base opacity-75 me-2"></i>
                Thông tin cá nhân
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center" href="account-addresses.html">
                <i class="ci-map-pin fs-base opacity-75 me-2"></i>
                Địa chỉ
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center"
                href="account-notifications.html">
                <i class="ci-bell fs-base opacity-75 mt-1 me-2"></i>
                Thông báo
            </a>
        </nav>
        <h6 class="pt-4 ps-2 ms-1">Dịch vụ khách hàng</h6>
        <nav class="list-group list-group-borderless">
            <a class="list-group-item list-group-item-action d-flex align-items-center" href="help-topics-v1.html">
                <i class="ci-help-circle fs-base opacity-75 me-2"></i>
                Trung tâm trợ giúp
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center"
                href="terms-and-conditions.html">
                <i class="ci-info fs-base opacity-75 me-2"></i>
                Điều khoản và điều kiện
            </a>
        </nav>
        <nav class="list-group list-group-borderless pt-3">
            <a href="#" class="list-group-item list-group-item-action"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="ci-log-out fs-base opacity-75 me-2"></i>
                Đăng xuất
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>
</aside>

<div class="modal fade modal-crop-larger" id="cropAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cắt và xoay ảnh đại diện</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body text-center">
                <img id="avatarCropImage2" style="width: 600px; height: 600px; display: block; margin: auto;" />
                <div class="preview-circle mt-4 mx-auto"
                    style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; display: none;">
                    <canvas id="avatarCroppedCanvas2" width="150" height="150"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-outline-primary" id="rotateAvatarBtn">Xoay</button>
                <button class="btn btn-primary" id="saveAvatarCroppedBtn">Tiếp theo</button>
            </div>
        </div>
    </div>
</div>
<script>
    let cropper;

    // Bắt sự kiện khi người dùng chọn ảnh
    document.getElementById('chooseAvatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Bật nút “Tiếp theo”
        document.getElementById('openCropStep').disabled = false;

        // Đọc file và gán vào thẻ img trong modal crop
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('avatarCropImage2').src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Nhấn nút “Tiếp theo” để mở modal crop
    document.getElementById('openCropStep').addEventListener('click', function() {
        // Ẩn modal chọn ảnh
        const chooseModalElement = document.getElementById('chooseAvatarModal');
        const chooseModal = bootstrap.Modal.getInstance(chooseModalElement);
        if (chooseModal) chooseModal.hide();

        // Mở modal crop
        const cropModalElement = document.getElementById('cropAvatarModal');
        const cropModal = new bootstrap.Modal(cropModalElement);
        cropModal.show();

        // Đợi ảnh load xong mới khởi tạo cropper
        const image = document.getElementById('avatarCropImage2');
        image.onload = function() {
            if (cropper) {
                cropper.destroy(); // Hủy bỏ instance cropper cũ nếu có
            }

            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                background: false,
                zoomable: false,
                scalable: false,
                responsive: true,
                minContainerWidth: 800,
                minContainerHeight: 800,
                minCanvasWidth: 600,
                minCanvasHeight: 600,
                minCropBoxWidth: 50,
                minCropBoxHeight: 50,
                ready() {
                    // ✅ ÉP crop box phải to sau khi cropper sẵn sàng
                    const containerData = cropper.getContainerData();
                    cropper.setCropBoxData({
                        width: containerData.width * 0.8,
                        height: containerData.height * 0.8,
                        left: containerData.width * 0.1,
                        top: containerData.height * 0.1,
                    });
                },
                crop() {
                    const croppedCanvas = cropper.getCroppedCanvas({
                        width: 600,
                        height: 600
                    });
                    const preview = document.getElementById('avatarCroppedCanvas2');
                    const ctx = preview.getContext('2d');
                    ctx.clearRect(0, 0, preview.width, preview.height);
                    ctx.drawImage(croppedCanvas, 0, 0, preview.width, preview.height);
                }
            });



        };

        // Nếu ảnh đã load xong trước khi sự kiện onload được thêm (ví dụ: ảnh từ cache)
        if (image.complete && image.naturalWidth !== 0) {
            image.onload();
        }
    });

    // Gửi ảnh về server
    document.getElementById('saveAvatarCroppedBtn').addEventListener('click', function() {
        if (!cropper) {
            alert('Chưa có ảnh để lưu.');
            return;
        }

        // Lấy canvas đã cắt với kích thước 300x300 (là hình vuông)
        const finalCroppedCanvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        const dataUrl = finalCroppedCanvas.toDataURL('image/png'); // Chuyển thành Base64 PNG

        fetch("{{ route('users.avatar.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    avatar_base64: dataUrl
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Đóng modal cắt ảnh
                    const cropModalElement = document.getElementById('cropAvatarModal');
                    const cropModal = bootstrap.Modal.getInstance(cropModalElement);
                    if (cropModal) cropModal.hide();

                    alert('Cập nhật ảnh đại diện thành công!');
                    window.location.reload(); // Tải lại trang sau khi cập nhật thành công
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật.'));
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi gửi ảnh.');
            });
    });

    // Xoay ảnh
    document.getElementById('rotateAvatarBtn').addEventListener('click', function() {
        if (cropper) {
            cropper.rotate(90); // Xoay 90 độ theo chiều kim đồng hồ
        }
    });
</script>
<style>
    .modal-crop-larger .modal-dialog {
        width: 900px;
        max-width: unset;
        margin: 2rem auto;
    }

    .modal-crop-larger .modal-content {
        width: 100%;
        max-height: unset;
    }

    .modal-crop-larger .modal-body {
        height: 700px;
        padding: 2rem;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        max-height: unset;
    }

    #avatarCropImage2 {
        width: 600px !important;
        height: 600px !important;
        max-width: none !important;
        max-height: none !important;
        object-fit: contain;
        display: block;
        margin: auto;
    }


    .modal-crop-larger .preview-circle {
        width: 300px;
        height: 300px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto;
        display: block;
    }

    .modal-crop-larger .modal-footer {
        padding: 15px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .cropper-view-box,
    .cropper-face {
        cursor: move;
        border-radius: 50% !important;
    }

    .cropper-crop-box::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 0;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        pointer-events: none;
        border: 2px dashed white;
    }

    :root {
        --bs-modal-header-height: 60px;
        --bs-modal-footer-height: 70px;
    }
</style>
