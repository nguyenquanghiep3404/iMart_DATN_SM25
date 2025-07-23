<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</script>
