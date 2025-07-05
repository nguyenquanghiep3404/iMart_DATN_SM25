@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý từng dòng sản phẩm trong giỏ
            document.querySelectorAll('tr[data-item-id]').forEach(function(row) {
                const itemId = row.dataset.itemId;
                const stock = parseInt(row.dataset.stock);
                const btnIncrement = row.querySelector('.btn-increment');
                const btnDecrement = row.querySelector('.btn-decrement');
                const input = row.querySelector('.quantity-input');
                const subtotalCell = row.querySelector('.item-subtotal');
                const price = parseInt(subtotalCell.dataset.price);
                let isUpdating = false;

                function updateUI(newQuantity) {
                    input.value = newQuantity;
                    subtotalCell.textContent = (price * newQuantity).toLocaleString('vi-VN') + '₫';

                    // Cập nhật tổng số lượng & tổng tiền
                    const totalQty = Array.from(document.querySelectorAll('.quantity-input')).reduce((sum,
                        input) => {
                        const qty = parseInt(input.value);
                        return sum + (isNaN(qty) ? 0 : qty);
                    }, 0);
                    const totalQtySpan = document.getElementById('total-quantity');
                    if (totalQtySpan) totalQtySpan.textContent = totalQty;
                }

                function updateQuantity(newQuantity, oldQuantity) {
                    if (isUpdating) return;
                    isUpdating = true;

                    fetch('{{ route('cart.updateQuantity') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                quantity: newQuantity
                            })
                        })
                        .then(async res => {
                            if (!res.ok) {
                                if (res.status === 422) {
                                    const err = await res.json();
                                    showSlideAlert('error', err.message ||
                                        'Số lượng vượt quá tồn kho.');
                                    updateUI(oldQuantity); // rollback
                                    return;
                                }
                                throw new Error('Lỗi khi gửi yêu cầu');
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return; // lỗi đã xử lý ở trên
                            if (data.success) {
                                showSlideAlert('success', data.message ||
                                    'Cập nhật số lượng thành công!');
                                // Cập nhật tổng tiền
                                const subtotalEl = document.getElementById('cart-subtotal');
                                if (subtotalEl) subtotalEl.textContent = data.total;
                                const totalHeader = document.querySelector('.h5.mb-0');
                                if (totalHeader) totalHeader.textContent = data.total;
                            } else {
                                showSlideAlert('error', data.message || 'Cập nhật số lượng thất bại.');
                                updateUI(oldQuantity); // rollback
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi cập nhật:', error);
                            updateUI(oldQuantity); // rollback nếu lỗi
                            showSlideAlert('error', 'Đã xảy ra lỗi khi cập nhật giỏ hàng.');
                        })
                        .finally(() => {
                            isUpdating = false;
                        });
                }

                btnIncrement?.addEventListener('click', function() {
                    let quantity = parseInt(input.value);
                    if (!isNaN(quantity)) {
                        if (quantity >= stock) {
                            showSlideAlert('error', 'Chỉ còn ' + stock + ' sản phẩm trong kho.');
                            return;
                        }
                        const newQuantity = quantity + 1;
                        updateUI(newQuantity);
                        updateQuantity(newQuantity, quantity);
                    }
                });

                btnDecrement?.addEventListener('click', function() {
                    let quantity = parseInt(input.value);
                    if (!isNaN(quantity) && quantity > 1) {
                        const newQuantity = quantity - 1;
                        updateUI(newQuantity);
                        updateQuantity(newQuantity, quantity);
                    }
                });

                row.querySelector('.btn-close')?.addEventListener('click', function() {
                    if (!confirm('Bạn có chắc muốn xoá sản phẩm này khỏi giỏ hàng?')) return;

                    fetch('{{ route('cart.removeItem') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                item_id: itemId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                row.remove();
                                const subtotalEl = document.getElementById('cart-subtotal');
                                if (subtotalEl) subtotalEl.textContent = data.total;
                                const totalHeader = document.querySelector('.h5.mb-0');
                                if (totalHeader) totalHeader.textContent = data.total;
                                const totalQtySpan = document.getElementById('total-quantity');
                                if (totalQtySpan) totalQtySpan.textContent = data.totalQuantity;
                                if (data.totalQuantity === 0) location.reload();
                                showSlideAlert('success', 'Xóa sản phẩm thành công.');
                            } else {
                                showSlideAlert('error', data.message ||
                                    'Xoá sản phẩm thất bại.');
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi xoá sản phẩm:', error);
                            showSlideAlert('error', 'Lỗi khi xoá sản phẩm.');
                        });
                });
            });

            // Hàm hiển thị thông báo dạng trượt (có phân biệt màu theo type)
            function showSlideAlert(type = 'info', message = '', duration = 4000) {
                const alertBox = document.getElementById('slide-alert');
                const messageEl = document.getElementById('slide-alert-message');
                const closeBtn = document.getElementById('slide-alert-close');

                if (!alertBox || !messageEl) return;

                // Reset class màu
                alertBox.classList.remove('bg-red-500', 'bg-green-500', 'bg-blue-500');

                // Gán màu theo loại thông báo
                switch (type) {
                    case 'error':
                        alertBox.classList.add('bg-red-500');
                        break;
                    case 'success':
                        alertBox.classList.add('bg-green-500');
                        break;
                    default:
                        alertBox.classList.add('bg-blue-500');
                }

                messageEl.textContent = message;
                alertBox.classList.remove('hidden', 'translate-x-full');
                alertBox.classList.add('show');

                // Xóa timeout nếu có
                clearTimeout(window.__alertTimeout);
                window.__alertTimeout = setTimeout(() => {
                    alertBox.classList.remove('show');
                    setTimeout(() => alertBox.classList.add('hidden', 'translate-x-full'), 300);
                }, duration);

                // Nút đóng
                closeBtn.onclick = () => {
                    clearTimeout(window.__alertTimeout);
                    alertBox.classList.remove('show');
                    setTimeout(() => alertBox.classList.add('hidden', 'translate-x-full'), 300);
                };
            }

            // Xử lý form voucher
            const voucherForm = document.querySelector('#voucher-form');
            if (voucherForm) {
                voucherForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(voucherForm);

                    fetch("{{ route('cart.applyVoucherAjax') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(async res => {
                            const data = await res.json();

                            if (!res.ok) {
                                showSlideAlert('error', data.message ||
                                    'Mã giảm giá không hợp lệ!');
                                return;
                            }

                            if (data.success) {
                                showSlideAlert('success', data.message ||
                                    'Áp dụng mã giảm giá thành công!');
                                // cập nhật giao diện
                            } else {
                                showSlideAlert('error', data.message ||
                                    'Không thể áp dụng mã giảm giá.');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            showSlideAlert('error', 'Đã có lỗi xảy ra khi áp dụng mã giảm giá.');
                        });
                });
            }
        });
    </script>
@endpush
