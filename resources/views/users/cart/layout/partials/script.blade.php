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
                    const cartBadge = document.getElementById('cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = totalQty;
                        cartBadge.style.display = totalQty > 0 ? 'flex' : 'none';
                    }
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
                                    toastr.error(err.message || 'Số lượng vượt quá tồn kho.');
                                    updateUI(oldQuantity); // rollback
                                    return;
                                }
                                toastr.error('Lỗi khi gửi yêu cầu');
                                updateUI(oldQuantity); // rollback
                                return;
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return; // lỗi đã xử lý ở trên
                            if (data.success) {
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
                            toastr.error('Chỉ còn ' + stock + ' sản phẩm trong kho.');
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
                                // Xóa sản phẩm khỏi DOM
                                row.remove();

                                // Cập nhật lại tổng tiền và tổng số lượng
                                const cartTotalEl = document.getElementById('cart-total');
                                const cartSubtotalEl = document.getElementById('cart-subtotal');
                                const totalQuantityEl = document.getElementById(
                                    'total-quantity');

                                if (cartTotalEl) cartTotalEl.textContent = data.total ? data
                                    .total : '0₫';
                                if (cartSubtotalEl) cartSubtotalEl.textContent = data.total ?
                                    data.total : '0₫';
                                if (totalQuantityEl) totalQuantityEl.textContent = (data
                                    .totalQuantity !== undefined) ? data.totalQuantity : 0;

                                // ✅ Cập nhật badge giỏ hàng
                                const cartBadge = document.getElementById('cart-badge');
                                if (cartBadge) {
                                    if (data.totalQuantity > 0) {
                                        cartBadge.style.display = 'flex';
                                        cartBadge.textContent = data.totalQuantity;
                                    } else {
                                        cartBadge.style.display = 'none';
                                    }
                                }

                                // Nếu giỏ hàng trống thì reload trang hoặc bạn có thể cập nhật UI theo ý muốn
                                if (data.totalQuantity === 0) {
                                    location.reload();
                                }

                                toastr.success('Xóa sản phẩm thành công.');
                            } else {
                                toastr.error(data.message || 'Xoá sản phẩm thất bại.');
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi xoá sản phẩm:', error);
                            showSlideAlert('error', 'Lỗi khi xoá sản phẩm.');
                        });
                });
            });

        });

        // clear giỏ hàng
        document.addEventListener('DOMContentLoaded', function() {
            const clearBtn = document.getElementById('clear-cart-btn');
            if (!clearBtn) return;

            clearBtn.addEventListener('click', function() {
                if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
                    fetch("{{ route('cart.clear') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({})
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success(data.message);

                                // Xóa các dòng sản phẩm
                                const tbody = document.querySelector('table tbody');
                                if (tbody) tbody.innerHTML = '';

                                // Cập nhật tổng tiền thanh toán
                                const cartTotal = document.getElementById('cart-total');
                                if (cartTotal) cartTotal.textContent = data.total || '0₫';

                                // Cập nhật tổng tiền bên dưới danh sách sản phẩm
                                const cartSubtotal = document.getElementById('cart-subtotal');
                                if (cartSubtotal) cartSubtotal.textContent = data.total || '0₫';

                                // Cập nhật số lượng (ở header, subtotal, badge)
                                const totalQtyElem = document.querySelector('.total-quantity');
                                if (totalQtyElem) totalQtyElem.textContent = data.totalQuantity || '0';

                                const totalQtyHeader = document.getElementById('total-quantity');
                                if (totalQtyHeader) totalQtyHeader.textContent = data.totalQuantity ||
                                    '0';

                                const cartBadge = document.getElementById('cart-badge');
                                if (cartBadge) {
                                    cartBadge.textContent = data.totalQuantity || '0';
                                    cartBadge.style.display = (data.totalQuantity > 0) ? 'flex' :
                                        'none';
                                }

                                // Cập nhật giảm giá về 0₫
                                const cartDiscount = document.getElementById('cart-discount');
                                if (cartDiscount) cartDiscount.textContent = '0₫';

                                // Hiển thị giỏ hàng trống nếu có
                                const emptyMsg = document.getElementById('empty-cart-message');
                                if (emptyMsg) emptyMsg.style.display = 'block';
                            } else {
                                toastr.error('Xảy ra lỗi khi xóa giỏ hàng');
                            }
                        })
                        .catch(error => {
                            toastr.error('Lỗi kết nối server');
                            console.error(error);
                        });
                }
            });
        });
        toastr.options = {
            closeButton: true, // hiển thị nút đóng (dấu x)
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: "3000",
            showDuration: "300",
            hideDuration: "1000",
            showMethod: "slideDown",
            hideMethod: "slideUp"
        };
    </script>
@endpush
