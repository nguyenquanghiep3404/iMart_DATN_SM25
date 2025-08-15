@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                const err = await res.json();
                                toastr.error(err.message || 'Lỗi máy chủ.');
                                updateUI(oldQuantity);
                                return null;
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return;

                            if (data.success) {
                                // Cập nhật UI nếu thành công
                                document.getElementById('cart-subtotal').textContent = data
                                    .subtotal_before_dc;
                                document.getElementById('cart-discount').textContent = data.discount;
                                document.getElementById('cart-total').textContent = data.total_after_dc;
                                return;
                            }

                            // ===== Trường hợp mới: Voucher & Points cùng lúc fail =====
                            if (data.voucher_failed || data.points_failed) {
                                let msgParts = [];
                                if (data.voucher_failed) {
                                    msgParts.push(
                                        `- Mã giảm giá không đủ điều kiện (tối thiểu ${data.voucher_min_amount || '...'}).`
                                    );
                                }
                                if (data.points_failed) {
                                    msgParts.push(
                                        `- Điểm thưởng không được vượt quá tổng tiền đơn hàng.`);
                                }

                                Swal.fire({
                                    title: 'Ưu đãi không đủ điều kiện',
                                    html: msgParts.join('<br>') +
                                        '<br>Bạn có muốn gỡ các ưu đãi này để tiếp tục không?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Gỡ & Tiếp tục',
                                    cancelButtonText: 'Giữ nguyên số lượng cũ'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('{{ route('cart.updateQuantity') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector(
                                                            'meta[name="csrf-token"]')
                                                        .content
                                                },
                                                body: JSON.stringify({
                                                    item_id: itemId,
                                                    quantity: newQuantity,
                                                    force_update: true, // Gỡ cả voucher và điểm
                                                    force_points_removal: true
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // Cập nhật UI
                                                    document.getElementById('cart-subtotal')
                                                        .textContent = data
                                                        .subtotal_before_dc;
                                                    document.getElementById('cart-discount')
                                                        .textContent = data.discount;
                                                    document.getElementById('cart-total')
                                                        .textContent = data.total_after_dc;

                                                    if (data.voucher_removed) {
                                                        $('#appliedCouponBox').hide();
                                                        $('#appliedPointsBox').hide();
                                                        const voucherSection = document
                                                            .getElementById(
                                                                'voucher-section');
                                                        if (voucherSection) voucherSection
                                                            .style.display = 'none';
                                                        Swal.fire({
                                                            icon: 'info',
                                                            title: 'Đã bị huỷ',
                                                            timer: 1000,
                                                            showConfirmButton: false
                                                        });
                                                    }
                                                    if (data.points_removed) {
                                                        $('#appliedCouponBox').hide();
                                                        $('#appliedPointsBox').hide();
                                                        const pointsDiscountRow = document
                                                            .getElementById(
                                                                'points-discount-row');
                                                        const pointsDiscountAmount =
                                                            document.getElementById(
                                                                'points-discount-amount');
                                                        if (pointsDiscountAmount)
                                                            pointsDiscountAmount
                                                            .textContent = '-0₫';
                                                        if (pointsDiscountRow)
                                                            pointsDiscountRow.style
                                                            .display = 'none';
                                                        Swal.fire({
                                                            icon: 'info',
                                                            title: 'Đã bị huỷ',
                                                            timer: 1000,
                                                            showConfirmButton: false
                                                        });
                                                    }
                                                } else {
                                                    showSlideAlert('error', data.message ||
                                                        'Cập nhật thất bại.');
                                                    updateUI(oldQuantity);
                                                }
                                            })
                                            .catch(err => {
                                                console.error(err);
                                                showSlideAlert('error', 'Lỗi máy chủ.');
                                                updateUI(oldQuantity);
                                            });
                                    } else {
                                        updateUI(oldQuantity);
                                    }
                                });
                                return;
                            }

                            // ===== Trường hợp khác (fallback) =====
                            showSlideAlert('error', data.message || 'Cập nhật thất bại.');
                            updateUI(oldQuantity);
                        })
                        .catch(err => {
                            console.error(err);
                            showSlideAlert('error', 'Lỗi máy chủ.');
                            updateUI(oldQuantity);
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
                    Swal.fire({
                        title: 'Bạn có chắc?',
                        text: 'Bạn muốn xoá sản phẩm này khỏi giỏ hàng?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Xoá',
                        cancelButtonText: 'Huỷ'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        function updateCartUI(data) {
                            document.getElementById('cart-subtotal').textContent = data
                                .total_before_discount || '0₫';
                            document.getElementById('cart-discount').textContent = data
                                .discount || '0₫';
                            document.getElementById('cart-total').textContent = data
                                .total_after_discount || '0₫';
                            document.getElementById('total-quantity').textContent = data
                                .totalQuantity ?? 0;

                            const cartBadge = document.getElementById('cart-badge');
                            if (cartBadge) {
                                cartBadge.style.display = data.totalQuantity > 0 ? 'flex' :
                                    'none';
                                cartBadge.textContent = data.totalQuantity;
                            }

                            // Cập nhật giảm từ điểm
                            const pointsRow = document.getElementById(
                                'points-discount-row');
                            const pointsAmount = document.getElementById(
                                'points-discount-amount');
                            if (pointsRow && pointsAmount) {
                                // Lấy giá trị số từ chuỗi, bỏ ký tự không phải số
                                let pointsValue = Number(String(data.points_discount)
                                    .replace(/[^\d]/g, ''));

                                if (pointsValue > 0) {
                                    pointsRow.style.display = '';
                                    pointsAmount.textContent =
                                        `-${pointsValue.toLocaleString('vi-VN')}₫`;
                                } else {
                                    pointsRow.style.display = 'none';
                                    pointsAmount.textContent = '0₫';
                                }
                            }
                        }

                        function sendRemoveRequest(force = false) {
                            fetch('{{ route('cart.removeItem') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        item_id: itemId,
                                        force_remove: force
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        row.remove();
                                        updateCartUI(data);

                                        if (data.voucher_removed) {
                                            $('#appliedCouponBox').hide();
                                            $('#appliedPointsBox').hide();
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Mã giảm giá đã bị huỷ',
                                                text: 'Đơn hàng không còn đủ điều kiện áp dụng mã giảm giá.'
                                            });
                                        }

                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Đã xoá',
                                            text: 'Sản phẩm đã được xoá khỏi giỏ hàng.',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });

                                    } else if (data.voucher_failed || data
                                        .points_failed) {
                                        let reason = '';
                                        if (data.voucher_failed && data
                                            .voucher_min_amount) {
                                            reason +=
                                                `Mã giảm giá yêu cầu đơn tối thiểu ${data.voucher_min_amount}.<br>`;
                                        }
                                        if (data.points_failed) {
                                            reason +=
                                                `Điểm thưởng đã áp dụng vượt quá giá trị giỏ hàng mới.<br>`;
                                        }

                                        Swal.fire({
                                            title: 'Không thể xoá sản phẩm',
                                            html: `
                                <p>${data.message || 'Một số ưu đãi sẽ không còn hợp lệ nếu bạn xoá sản phẩm này.'}</p>
                                <p>${reason}</p>
                                <p>Nếu bạn tiếp tục xoá, các ưu đãi này sẽ bị huỷ.</p>
                            `,
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Xoá và huỷ ưu đãi',
                                            cancelButtonText: 'Giữ lại'
                                        }).then((choice) => {
                                            if (choice.isConfirmed) {
                                                sendRemoveRequest(true);
                                            }
                                        });

                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Lỗi',
                                            text: data.message ||
                                                'Đã xảy ra lỗi khi xoá sản phẩm.'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Lỗi khi xoá sản phẩm:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: 'Lỗi hệ thống khi xoá sản phẩm. Vui lòng thử lại.'
                                    });
                                });
                        }

                        sendRemoveRequest();
                    });
                });

            });
        });

        // clear giỏ hàng
        document.addEventListener('DOMContentLoaded', function() {
            const clearBtn = document.getElementById('clear-cart-btn');
            if (!clearBtn) return;

            clearBtn.addEventListener('click', function() {
                const cartRows = document.querySelectorAll('table tbody tr');
                if (cartRows.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Giỏ hàng đang trống',
                        text: 'Không có sản phẩm nào để xóa.',
                        showCloseButton: true, // Hiện nút dấu x góc trên bên phải
                        confirmButtonText: 'Tiếp tục mua sắm',
                    }).then((result) => {
                        // Nếu nhấn nút confirm (Tiếp tục mua sắm) thì mới điều hướng
                        if (result.isConfirmed) {
                            window.location.href = '/';
                        }
                        // Nếu nhấn dấu x hoặc click ngoài popup thì không làm gì (chỉ đóng popup)
                    });
                    return;
                }
                Swal.fire({
                    title: 'Bạn có chắc?',
                    text: 'Hành động này sẽ xóa toàn bộ giỏ hàng của bạn!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa hết!',
                    cancelButtonText: 'Huỷ',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
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
                                    $('#appliedCouponBox').hide();
                                    $('#appliedPointsBox').hide();
                                    const pointsRow = document.getElementById(
                                        'points-discount-row');
                                    if (pointsRow) pointsRow.style.display = 'none';

                                    const pointsAmount = document.getElementById(
                                        'points-discount-amount');
                                    if (pointsAmount) pointsAmount.textContent = '-0₫';
                                    // Xóa các dòng sản phẩm
                                    const tbody = document.querySelector('table tbody');
                                    if (tbody) tbody.innerHTML = '';

                                    // Cập nhật tổng tiền thanh toán
                                    const cartTotal = document.getElementById('cart-total');
                                    if (cartTotal) cartTotal.textContent = data.total || '0₫';

                                    const cartSubtotal = document.getElementById(
                                        'cart-subtotal');
                                    if (cartSubtotal) cartSubtotal.textContent = data.total ||
                                        '0₫';

                                    const totalQtyElem = document.querySelector(
                                        '.total-quantity');
                                    if (totalQtyElem) totalQtyElem.textContent = data
                                        .totalQuantity || '0';

                                    const totalQtyHeader = document.getElementById(
                                        'total-quantity');
                                    if (totalQtyHeader) totalQtyHeader.textContent = data
                                        .totalQuantity || '0';

                                    const cartBadge = document.getElementById('cart-badge');
                                    if (cartBadge) {
                                        cartBadge.textContent = data.totalQuantity || '0';
                                        cartBadge.style.display = (data.totalQuantity > 0) ?
                                            'flex' : 'none';
                                    }

                                    const cartDiscount = document.getElementById(
                                        'cart-discount');
                                    if (cartDiscount) cartDiscount.textContent = '0₫';

                                    const emptyMsg = document.getElementById(
                                        'empty-cart-message');
                                    if (emptyMsg) emptyMsg.style.display = 'block';

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Đã xóa!',
                                        text: data.message ||
                                            'Giỏ hàng của bạn đã được làm trống.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
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
        window.addEventListener('cartQuantityUpdated', function(e) {
            const {
                itemId,
                quantity
            } = e.detail;

            // Ở giỏ hàng chính (có <tr> với item-subtotal)
            const $cartRow = $(`tr[data-item-id="${itemId}"]`);
            if ($cartRow.length) {
                const price = parseFloat($cartRow.find('.item-subtotal').data('price')) || 0;
                const newSubtotal = price * quantity;
                $cartRow.find('.item-subtotal').text(newSubtotal.toLocaleString('vi-VN') + 'đ');
            }

            // Ở modal hoặc sidebar (ví dụ có input .full-cart-input)
            const $input = $(`.full-cart-input[data-item-id="${itemId}"]`);
            if ($input.length) {
                $input.val(quantity);

                // Cập nhật subtotal nếu có phần tử tương ứng trong modal
                const $modalSubtotal = $input.closest('.cart-item-modal').find('.modal-item-subtotal');
                if ($modalSubtotal.length) {
                    const price = parseFloat($modalSubtotal.data('price')) || 0;
                    const newSubtotal = price * quantity;
                    $modalSubtotal.text(newSubtotal.toLocaleString('vi-VN') + 'đ');
                }
            }
            // Cập nhật nút, tổng tiền nếu có
            updateButtonState($input.closest('tr, .cart-item-modal'));
            if (typeof recalculateCartSummary === 'function') {
                recalculateCartSummary();
            }
        });
        setTimeout(() => {
            document.getElementById("loading-overlay").style.display = "none";
        }, 1000);
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('toast_error'))
                toastr.error(@json(session('toast_error')));
            @endif
        });
    </script>
@endpush
