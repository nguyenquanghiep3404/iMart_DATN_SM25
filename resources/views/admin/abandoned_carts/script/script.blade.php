<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-send-inapp').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.dataset.id;

                Swal.fire({
                    title: 'Xác nhận',
                    text: 'Bạn có chắc muốn gửi thông báo in-app?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Gửi',
                    cancelButtonText: 'Hủy',
                    didOpen: () => {
                        const confirmBtn = Swal.getConfirmButton();
                        const cancelBtn = Swal.getCancelButton();

                        if (confirmBtn) {
                            confirmBtn.disabled = false;
                            confirmBtn.style.opacity = '1';
                            confirmBtn.style.pointerEvents = 'auto';
                        }
                        if (cancelBtn) {
                            cancelBtn.disabled = false;
                            cancelBtn.style.opacity = '1';
                            cancelBtn.style.pointerEvents = 'auto';
                        }
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch(`/admin/abandoned-carts/send-inapp/${cartId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.success ? 'success' : 'error',
                                title: data.success ? 'Thành công' : 'Lỗi',
                                text: data.message || (data.success ?
                                    'Đã gửi' : 'Gửi thất bại'),
                            }).then(() => {
                                if (data.success) location.reload();
                            });
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire('Lỗi', 'Có lỗi xảy ra!', 'error');
                        });
                });
            });
        });
    });

    // gửi email
    $(document).on('click', '.btn-send-email', function(e) {
        e.preventDefault();

        const btn = $(this);
        const cartId = btn.data('id');

        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn gửi email khôi phục giỏ hàng?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Gửi',
            cancelButtonText: 'Hủy',
        }).then((result) => {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true);

            Swal.fire({
                title: 'Đang gửi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();

                    $.post("{{ route('admin.abandoned_carts.send_email', ['id' => '__id__']) }}"
                        .replace('__id__', cartId), {
                            _token: '{{ csrf_token() }}'
                        }).done(function(res) {
                        // Debug console xem dữ liệu server trả về
                        console.log(res);

                        // Nếu server có trường success (true/false)
                        if ('success' in res) {
                            Swal.fire({
                                icon: res.success ? 'success' : 'error',
                                title: res.success ? 'Thành công' : 'Lỗi',
                                text: res.message || (res.success ?
                                    'Đã gửi email!' : 'Gửi thất bại'),
                            }).then(() => {
                                if (res.success) {
                                    location.reload();
                                } else {
                                    btn.prop('disabled', false);
                                }
                            });
                        } else {
                            // Nếu server chỉ trả message (không có success)
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: res.message ||
                                    'Đã gửi email thành công!',
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }).fail(function(xhr) {
                        console.error(xhr);
                        Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi email.', 'error');
                        btn.prop('disabled', false);
                    });
                }
            });
        });
    });
</script>
