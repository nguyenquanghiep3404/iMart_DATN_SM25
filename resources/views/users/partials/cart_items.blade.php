@if (isset($items) && $items->isNotEmpty())
    <div id="cart-items-wrapper">
        @foreach ($items as $item)
            {{-- <pre>@dd($item)</pre> --}}
            <div class="d-flex align-items-center mb-3 border-bottom pb-2 cart-item" data-item-id="{{ $item['id'] }}"
                data-price="{{ $item['price'] }}" data-stock="{{ $item['stock_quantity'] ?? 0 }}">

                {{-- Hình ảnh --}}
                <a class="flex-shrink-0"
                    href="{{ !empty($item['slug']) ? route('users.products.show', ['slug' => $item['slug']]) : '#' }}">
                    <img src="{{ asset($item['image'] ?: 'assets/users/img/shop/electronics/thumbs/08.png') }}"
                        width="80" alt="{{ $item['name'] }}">
                </a>

                {{-- Nội dung bên phải --}}
                <div class="w-100 min-w-0 ps-2 ps-sm-3">
                    <h5 class="d-flex animate-underline mb-2">
                        <a class="d-block fs-sm fw-medium text-truncate animate-target"
                            href="{{ !empty($item['slug']) ? route('users.products.show', ['slug' => $item['slug']]) : '#' }}">
                            {{ $item['name'] }}
                        </a>
                    </h5>

                    {{-- Giá --}}
                    <div class="h6 pb-1 mb-2 text-danger">
                        {{ number_format($item['price'], 0, ',', '.') }} đ
                    </div>

                    {{-- Tăng giảm & xóa --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="count-input rounded-2">
                            <button type="button" class="btn btn-icon btn-sm" data-decrement
                                aria-label="Giảm số lượng">
                                <i class="ci-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm" value="{{ $item['quantity'] }}"
                                readonly>
                            <button type="button" class="btn btn-icon btn-sm" data-increment
                                aria-label="Tăng số lượng">
                                <i class="ci-plus"></i>
                            </button>
                        </div>

                        <button type="button" class="btn-close fs-sm" data-item-id="{{ $item['id'] }}"
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

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
        window.cartTimers = {};

        // Tăng / giảm số lượng
        $(document).on('click', '.cart-item [data-increment], .cart-item [data-decrement]', function() {
            const $btn = $(this);
            const $cartItem = $btn.closest('.cart-item');
            const $input = $cartItem.find('input[type=number]');
            const itemId = $cartItem.data('item-id');
            const stock = parseInt($cartItem.data('stock')) || 0;
            let qty = parseInt($input.val()) || 1;

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

            const subtotal = calculateSubtotal();
            $('#cart-summary .h6.mb-0').text(formatPrice(subtotal));

            if (cartTimers[itemId]) clearTimeout(cartTimers[itemId]);
            cartTimers[itemId] = setTimeout(() => {
                sendUpdateRequest(itemId, qty);
            }, 300);
        });

        function sendUpdateRequest(itemId, quantity) {
            $.post("{{ route('cart.updateQuantity') }}", {
                item_id: itemId,
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            }).fail(() => {
                alert('Lỗi khi cập nhật số lượng.');
                location.reload();
            });
        }

        // Xoá sản phẩm
        $(document).on('click', '.btn-close[data-item-id]', function() {
            const $btn = $(this);
            const itemId = $btn.data('item-id');

            $.post("{{ route('cart.removeItem') }}", {
                item_id: itemId,
                _token: '{{ csrf_token() }}'
            }).done((res) => {
                if (res.success) {
                    cachedCartHtml = null;
                    $btn.closest('.cart-item').remove();

                    const subtotal = calculateSubtotal();
                    $('#cart-summary .h6.mb-0').text(formatPrice(subtotal));

                    if ($('.cart-item').length === 0) {
                        $('#cart-items-wrapper').html('<p>Giỏ hàng trống.</p>');
                        $('#cart-summary').hide();
                    }
                } else {
                    alert(res.message || 'Không thể xoá sản phẩm.');
                }
            }).fail(() => {
                alert('Không thể xoá sản phẩm.');
            });
        });
    });
</script>
