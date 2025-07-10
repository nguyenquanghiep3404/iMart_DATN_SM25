@if (isset($items) && $items->isNotEmpty())
    <div id="cart-items-wrapper">
        @foreach ($items as $item)
            <div class="d-flex align-items-center mb-3 border-bottom pb-2 cart-item" data-item-id="{{ $item['id'] }}"
                data-price="{{ $item['price'] }}" data-stock="{{ $item['stock_quantity'] ?? 0 }}">
                <a class="flex-shrink-0" href="{{ route('users.products.show', ['slug' => $item['slug']]) }}">
                    <img src="{{ asset($item['image'] ?: 'assets/users/img/shop/electronics/thumbs/08.png') }}"
                        width="80" alt="{{ $item['name'] }}">
                </a>
                <div class="w-100 min-w-0 ps-2 ps-sm-3">
                    <h5 class="d-flex animate-underline mb-2">
                        <a class="d-block fs-sm fw-medium text-truncate animate-target"
                            href="{{ route('users.products.show', ['slug' => $item['slug']]) }}">
                            {{ $item['name'] }}
                        </a>
                    </h5>
                    <div class="h6 pb-1 mb-2">{{ number_format($item['price'], 0, ',', '.') }} đ</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="count-input rounded-2">
                            <button type="button" class="btn btn-icon btn-sm" data-decrement aria-label="Giảm số lượng"
                                data-item-id="{{ $item['id'] }}">
                                <i class="ci-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm" value="{{ $item['quantity'] }}"
                                readonly>
                            <button type="button" class="btn btn-icon btn-sm" data-increment aria-label="Tăng số lượng"
                                data-item-id="{{ $item['id'] }}">
                                <i class="ci-plus"></i>
                            </button>
                        </div>
                        <button type="button" id="btn-close-{{ $item['id'] }}" class="btn-close fs-sm"
                            data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" data-bs-title="Xóa"
                            aria-label="Xóa khỏi giỏ hàng" data-item-id="{{ $item['id'] }}"></button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="offcanvas-header flex-column align-items-start" id="cart-summary">
        <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
            <span class="text-light-emphasis">Tổng tiền:</span>
            <span class="h6 mb-0">{{ isset($subtotal) ? number_format($subtotal, 0, ',', '.') . ' đ' : '0 đ' }}</span>
        </div>
        <div class="d-flex w-100 gap-3">
            <a class="btn btn-lg btn-secondary w-100" href="/cart">Xem giỏ hàng</a>
            <a class="btn btn-lg btn-primary w-100" href="/checkout">Tiến hành thanh toán</a>
        </div>
    </div>
@else
    <p>Giỏ hàng trống.</p>
@endif

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS + Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function formatPrice(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' đ';
    }

    function calculateSubtotal() {
        let subtotal = 0;
        $('.cart-item').each(function() {
            const price = parseFloat($(this).data('price')) || 0;
            const quantity = parseInt($(this).find('input[type=number]').val()) || 0;
            subtotal += price * quantity;
        });
        return subtotal;
    }

    $(function() {
        let timers = {};

        function sendUpdateRequest(itemId, quantity) {
            $.ajax({
                url: "{{ route('cart.updateQuantity') }}",
                type: 'POST',
                data: {
                    item_id: itemId,
                    quantity: quantity,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (!response.success) {
                        alert(response.message || 'Không thể cập nhật số lượng.');
                        // Nếu server báo lỗi, rollback số lượng hiển thị về giá trị cũ
                        // Có thể gửi lại giá trị quantity đúng server trả về nếu có, hoặc reload trang
                        // Ở đây đơn giản reload trang:
                        location.reload();
                    }
                },
                error: function() {
                    alert('Lỗi khi cập nhật số lượng.');
                    location.reload();
                }
            });
        }

        // Xử lý tăng giảm số lượng với kiểm tra tồn kho
        $(document).on('click', '[data-increment], [data-decrement]', function() {
            const button = $(this);
            const cartItemDiv = button.closest('.cart-item');
            const itemId = button.data('item-id');
            const input = cartItemDiv.find('input[type=number]');
            let currentVal = parseInt(input.val()) || 1;
            const stock = parseInt(cartItemDiv.data('stock')) || 0;

            if (button.is('[data-increment]')) {
                if (currentVal >= stock) {
                    toastr.error('Số lượng đã đạt tối đa tồn kho.');
                    return;
                }
                currentVal++;
            } else if (button.is('[data-decrement]')) {
                if (currentVal <= 1) return;
                currentVal--;
            }

            input.val(currentVal);

            // Cập nhật subtotal ngay
            const subtotal = calculateSubtotal();
            $('#cart-summary .h6.mb-0').text(formatPrice(subtotal));

            // **Cập nhật badge số lượng tổng lên header**
            const totalQuantity = $('.cart-item input[type=number]').toArray()
                .reduce((acc, el) => acc + (parseInt(el.value) || 0), 0);
            const cartBadge = $('#cart-badge');
            if (totalQuantity > 0) {
                cartBadge.text(totalQuantity);
                cartBadge.show();
            } else {
                cartBadge.hide();
            }

            // Gửi ajax debounce cập nhật server
            if (timers[itemId]) clearTimeout(timers[itemId]);
            timers[itemId] = setTimeout(() => {
                sendUpdateRequest(itemId, currentVal);
            }, 300);
        });


        // Xóa sản phẩm
        $(document).on('click', '[id^=btn-close-]', function() {
            const itemId = $(this).data('item-id');
            const button = $(this);

            $.ajax({
                url: "{{ route('cart.removeItem') }}",
                type: 'POST',
                data: {
                    item_id: itemId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Xóa item khỏi DOM
                        button.closest('.cart-item').remove();

                        // Cập nhật subtotal sau khi xoá
                        const subtotal = calculateSubtotal();
                        $('#cart-summary .h6.mb-0').text(formatPrice(subtotal));

                        // Nếu không còn sản phẩm thì hiển thị thông báo và ẩn phần subtotal + nút thanh toán
                        if ($('.cart-item').length === 0) {
                            $('#cart-items-wrapper').html('<p>Giỏ hàng trống.</p>');
                            $('#cart-summary').hide();
                        }
                    } else {
                        alert(response.message ||
                            'Không thể xóa sản phẩm. Vui lòng thử lại!');
                    }
                },
                error: function(xhr) {
                    alert('Không thể xóa sản phẩm. Vui lòng thử lại!');
                    console.log(xhr.responseText);
                }
            });
        });
    });
</script>
