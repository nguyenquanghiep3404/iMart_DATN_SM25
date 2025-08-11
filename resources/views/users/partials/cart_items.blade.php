@if (isset($items) && $items->isNotEmpty())
    <div id="cart-items-wrapper">
        @foreach ($items as $item)
            @php
                    // Khai báo các biến để truy cập dữ liệu lồng nhau một cách gọn gàng
                    // Giả định cấu trúc trả về từ controller là $item->productVariant->product
                    $product = $item->productVariant->product ?? null;
                    $variant = $item->productVariant ?? null;
                    $productName = $product->name ?? ($item->name ?? 'Sản phẩm không xác định');
                    $productSlug = $product->slug ?? ($item->slug ?? '#');
                @endphp
            
            <div class="d-flex align-items-center mb-3 border-bottom pb-2 cart-item" 
                 data-item-id="{{ $item->id }}"
                 data-price="{{ $item->price }}" 
                 data-stock="{{ $item->stock_quantity ?? 0 }}">

                {{-- Hình ảnh --}}
                <a class="flex-shrink-0" href="{{ $productSlug !== '#' ? route('users.products.show', ['slug' => $productSlug]) : '#' }}">
                    <img src="{{ $item->image ?? asset('images/placeholder.jpg') }}" width="80" alt="{{ $productName }}">
                </a>

                {{-- Nội dung bên phải --}}
                <div class="w-100 min-w-0 ps-2 ps-sm-3">
                    <h5 class="d-flex animate-underline mb-2">
                        <a class="d-block fs-sm fw-medium text-truncate animate-target"
                           href="{{ $productSlug !== '#' ? route('users.products.show', ['slug' => $productSlug]) : '#' }}">
                            {{ $productName }}
                        </a>
                    </h5>

                    {{-- Giá --}}
                    <div class="h6 pb-1 mb-2 text-danger">
                        {{ number_format($item->price, 0, ',', '.') }} đ
                    </div>

                    {{-- Tăng giảm & xóa --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="count-input rounded-2">
                            <button type="button" class="btn btn-icon btn-sm" data-decrement
                                    aria-label="Giảm số lượng">
                                <i class="ci-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm" value="{{ $item->quantity }}"
                                   readonly>
                            <button type="button" class="btn btn-icon btn-sm" data-increment
                                    aria-label="Tăng số lượng">
                                <i class="ci-plus"></i>
                            </button>
                        </div>

                        <button type="button" class="btn-close fs-sm" 
                                data-bs-toggle="tooltip" data-bs-title="Xóa" aria-label="Xóa khỏi giỏ hàng">
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tổng tiền & nút thanh toán --}}
    <div class="offcanvas-header flex-column align-items-start" id="cart-summary">
        <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
            <span class="text-light-emphasis">Tổng tiền:</span>
            <span class="h6 mb-0">{{ number_format($total ?? 0, 0, ',', '.') }} đ</span>
        </div>
        <div class="d-flex w-100 gap-3">
            <a class="btn btn-lg btn-secondary w-100" href="/cart">Xem giỏ hàng</a>
            <a class="btn btn-lg btn-primary w-100" href="/checkout">Tiến hành thanh toán</a>
        </div>
    </div>
@else
    <p>Giỏ hàng trống.</p>
@endif
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script>
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: "3000",
        showDuration: "300",
        hideDuration: "1000",
        showMethod: "slideDown",
        hideMethod: "slideUp"
    };

    function formatPrice(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatPriceIntl(num) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(num);
    }

    function updateButtonState($cartItem) {
        const $input = $cartItem.find('input[type=number]');
        const qty = parseInt($input.val()) || 1;
        const stock = parseInt($cartItem.data('stock')) || 0;

        // Disable nút giảm nếu qty = 1
        $cartItem.find('[data-decrement]').prop('disabled', qty <= 1);

        // Disable nút tăng nếu qty >= stock
        $cartItem.find('[data-increment]').prop('disabled', qty >= stock);
    }

    $(function() {
        window.cartTimers = {};

        // Khởi tạo trạng thái nút tăng giảm khi load trang
        $('.cart-item').each(function() {
            updateButtonState($(this));
        });

        // Xử lý tăng giảm số lượng
        $(document).on('click', '.cart-item [data-increment], .cart-item [data-decrement]', function() {
            const $btn = $(this);
            const $cartItem = $btn.closest('.cart-item');
            const $input = $cartItem.find('input[type=number]');
            const itemId = $cartItem.data('item-id');
            const stock = parseInt($cartItem.data('stock')) || 0;

            const oldQty = parseInt($input.val()) || 1;
            $cartItem.data('old-quantity', oldQty);

            let qty = oldQty;

            if ($btn.is('[data-increment]')) {
                if (qty >= stock) {
                    toastr.error('Số lượng đã đạt tối đa tồn kho.');
                    return;
                }
                qty++;
            } else {
                if (qty <= 1) return;
                qty--;
            }

            $input.val(qty);
            updateButtonState($cartItem);

            // Disable toàn bộ nút tăng giảm để tránh thao tác quá nhanh
            $('.cart-item [data-increment], .cart-item [data-decrement]').prop('disabled', true);

            // Debounce gửi request update lên server sau 300ms
            if (cartTimers[itemId]) clearTimeout(cartTimers[itemId]);
            cartTimers[itemId] = setTimeout(() => {
                sendUpdateRequest(itemId, qty);
                $(`.full-cart-input[data-item-id="${itemId}"]`).val(quantity);
            }, 100);
        });

        // function sendUpdateRequest(itemId, quantity, forceUpdate = false) {
        //     $.post("{{ route('cart.updateQuantity') }}", {
        //         item_id: itemId,
        //         quantity: quantity,
        //         force_update: forceUpdate,
        //         _token: '{{ csrf_token() }}'
        //     }).done((res) => {
        //         const $cartItem = $(`.cart-item[data-item-id="${itemId}"]`);
        //         if (res.success) {
        //             cachedCartHtml = null;
        //             updateCartBadge();

        //             if ($('#cart-subtotal').length) $('#cart-subtotal').text(formatPrice(res
        //                 .subtotal_before_dc));
        //             if ($('#cart-discount').length) $('#cart-discount').text(formatPrice(res.discount));
        //             if ($('#cart-total').length) $('#cart-total').text(formatPrice(res.total_after_dc));
        //             $('#cart-summary .h6.mb-0').text(formatPrice(res.total_after_dc));

        //             if (res.voucher_removed) {
        //                 toastr.info('Mã giảm giá đã bị huỷ vì đơn hàng không còn đủ điều kiện.');
        //                 $('#voucher-section').hide();
        //             } else {
        //                 $('#voucher-section').show();
        //             }

        //             $cartItem.removeData('old-quantity');
        //             updateButtonState($cartItem);

        //         } else if (res.need_rollback_quantity) {
        //             Swal.fire({
        //                 title: 'Không đủ điều kiện sử dụng mã giảm giá',
        //                 text: (res.message || '') +
        //                     '\nBạn có muốn tiếp tục và huỷ mã giảm giá không?',
        //                 icon: 'warning',
        //                 showCancelButton: true,
        //                 confirmButtonText: 'Tiếp tục & Huỷ mã',
        //                 cancelButtonText: 'Giữ nguyên số lượng cũ'
        //             }).then(result => {
        //                 if (result.isConfirmed) {
        //                     // Gửi lại request với force_update = true
        //                     sendUpdateRequest(itemId, quantity, true);
        //                 } else {
        //                     revertQuantityInput(itemId);
        //                     $cartItem.removeData('old-quantity');
        //                     updateButtonState($cartItem);
        //                 }
        //             });
        //         } else {
        //             toastr.error(res.message || 'Không thể cập nhật số lượng.');
        //             revertQuantityInput(itemId);
        //             $cartItem.removeData('old-quantity');
        //             updateButtonState($cartItem);
        //         }
        //     }).fail(() => {
        //         toastr.error('Lỗi khi cập nhật số lượng.');
        //         location.reload();
        //     }).always(() => {
        //         $('.cart-item [data-increment], .cart-item [data-decrement]').prop('disabled', false);
        //     });
        // }
        function sendUpdateRequest(itemId, quantity, forceUpdate = false) {
            $.post("{{ route('cart.updateQuantity') }}", {
                item_id: itemId,
                quantity: quantity,
                force_update: forceUpdate,
                _token: '{{ csrf_token() }}'
            }).done((res) => {
                const $cartItem = $(`.cart-item[data-item-id="${itemId}"]`);
                if (res.success) {
                    cachedCartHtml = null;
                    updateCartBadge();

                    if ($('#cart-subtotal').length) $('#cart-subtotal').text(formatPrice(res
                        .subtotal_before_dc));
                    if ($('#cart-discount').length) $('#cart-discount').text(formatPrice(res.discount));
                    if ($('#cart-total').length) $('#cart-total').text(formatPrice(res.total_after_dc));
                    $('#cart-summary .h6.mb-0').text(formatPrice(res.total_after_dc));

                    if (res.voucher_removed) {
                        toastr.info('Mã giảm giá đã bị huỷ vì đơn hàng không còn đủ điều kiện.');
                        $('#voucher-section').hide();
                    } else {
                        $('#voucher-section').show();
                    }

                    // Phát sự kiện đồng bộ số lượng sang các phần khác (nếu có)
                    window.dispatchEvent(new CustomEvent('cartQuantityUpdated', {
                        detail: {
                            itemId: itemId,
                            quantity: quantity
                        }
                    }));

                    $cartItem.removeData('old-quantity');
                    updateButtonState($cartItem);

                } else if (res.need_rollback_quantity) {
                    Swal.fire({
                        title: 'Không đủ điều kiện sử dụng mã giảm giá',
                        text: (res.message || '') +
                            '\nBạn có muốn tiếp tục và huỷ mã giảm giá không?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tiếp tục & Huỷ mã',
                        cancelButtonText: 'Giữ nguyên số lượng cũ'
                    }).then(result => {
                        if (result.isConfirmed) {
                            // Gửi lại request với force_update = true
                            sendUpdateRequest(itemId, quantity, true);
                        } else {
                            revertQuantityInput(itemId);
                            $cartItem.removeData('old-quantity');
                            updateButtonState($cartItem);
                        }
                    });
                } else {
                    toastr.error(res.message || 'Không thể cập nhật số lượng.');
                    revertQuantityInput(itemId);
                    $cartItem.removeData('old-quantity');
                    updateButtonState($cartItem);
                }
            }).fail(() => {
                toastr.error('Lỗi khi cập nhật số lượng.');
                location.reload();
            }).always(() => {
                $('.cart-item [data-increment], .cart-item [data-decrement]').prop('disabled', false);
            });
        }

        function revertQuantityInput(itemId) {
            const $cartItem = $(`.cart-item[data-item-id="${itemId}"]`);
            if ($cartItem.length) {
                const oldQty = $cartItem.data('old-quantity');
                if (oldQty !== undefined) {
                    $cartItem.find('input[type=number]').val(oldQty);
                    updateButtonState($cartItem);

                    // Gửi lại request update với số lượng rollback để lấy lại voucher, tổng tiền
                    $.post("{{ route('cart.updateQuantity') }}", {
                        item_id: itemId,
                        quantity: oldQty,
                        _token: '{{ csrf_token() }}'
                    }).done((res) => {
                        if (res.success) {
                            // Cập nhật tổng tiền, voucher, discount
                            if ($('#cart-subtotal').length) $('#cart-subtotal').text(formatPrice(res
                                .subtotal_before_dc));
                            if ($('#cart-discount').length) $('#cart-discount').text(formatPrice(res
                                .discount));
                            if ($('#cart-total').length) $('#cart-total').text(formatPrice(res
                                .total_after_dc));
                            $('#cart-summary .h6.mb-0').text(formatPrice(res.total_after_dc));

                            if (res.voucher_removed) {
                                toastr.info(
                                    'Mã giảm giá đã bị huỷ vì đơn hàng không còn đủ điều kiện.');
                                $('#voucher-section').hide();
                            } else {
                                $('#voucher-section').show();
                            }
                        } else {
                            toastr.error('Không thể cập nhật lại giỏ hàng sau rollback.');
                        }
                    }).fail(() => {
                        toastr.error('Lỗi khi cập nhật giỏ hàng sau rollback.');
                    });
                }
            }
        }

        function updateCartBadge() {
            let totalQty = 0;
            $('.cart-item').each(function() {
                const qty = parseInt($(this).find('input[type=number]').val()) || 0;
                totalQty += qty;
            });

            const $badge = $('#cart-badge');
            if (totalQty > 0) {
                $badge.text(totalQty).show();
            } else {
                $badge.hide();
            }
        }

        // Xoá sản phẩm khỏi giỏ hàng
        $(document).on('click', '.btn-close[data-item-id]', function() {
            const $btn = $(this);
            const itemId = $btn.data('item-id');

            $.post("{{ route('cart.removeItem') }}", {
                item_id: itemId,
                _token: '{{ csrf_token() }}'
            }).done((res) => {
                if (res.success) {
                    cachedCartHtml = null;

                    // Xóa phần tử giỏ hàng trong DOM
                    $btn.closest('.cart-item').remove();

                    // Cập nhật tổng số lượng trên badge giỏ hàng
                    updateCartBadge();

                    // Cập nhật lại subtotal, discount, tổng tiền trên giao diện
                    if ($('#cart-subtotal').length && res.subtotal_before_dc !== undefined) {
                        $('#cart-subtotal').text(formatPrice(res.subtotal_before_dc));
                    }
                    if ($('#cart-discount').length && res.discount !== undefined) {
                        $('#cart-discount').text(formatPrice(res.discount));
                    }
                    if ($('#cart-total').length && res.total_after_dc !== undefined) {
                        $('#cart-total').text(formatPrice(res.total_after_dc));
                    }
                    // Cập nhật summary bên dưới cùng
                    if ($('#cart-summary .h6.mb-0').length && res.total_after_dc !==
                        undefined) {
                        $('#cart-summary .h6.mb-0').text(formatPrice(res.total_after_dc));
                    }

                    // Xử lý hiển thị voucher section
                    if (res.voucher_removed) {
                        toastr.info(
                            'Mã giảm giá đã bị huỷ vì đơn hàng không còn đủ điều kiện.');
                        $('#voucher-section').hide();
                    } else {
                        $('#voucher-section').show();
                    }

                    // Nếu giỏ hàng rỗng thì hiển thị thông báo và ẩn summary
                    if ($('.cart-item').length === 0) {
                        $('#cart-items-wrapper').html('<p>Giỏ hàng trống.</p>');
                        $('#cart-summary').hide();
                    } else {
                        $('#cart-summary').show();
                    }
                } else {
                    toastr.error(res.message || 'Không thể xoá sản phẩm.');
                }
            }).fail(() => {
                toastr.error('Không thể xoá sản phẩm.');
            });
        });
    });
</script>