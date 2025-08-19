<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Thêm toastr CSS & JS (nếu chưa có) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(function() {
        $('#add-to-cart-btn').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const variantId = $btn.data('variant-id');
            const quantity = parseInt($('#quantity_input').val()) || 1;
            const token = '{{ csrf_token() }}'; // Laravel CSRF token

            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('cart.add') }}",
                method: 'POST',
                data: {
                    _token: token,
                    product_variant_id: variantId,
                    quantity: quantity,
                },
                dataType: 'json',
                success: function(res) {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        escapeHtml: false,
                        timeOut: 3000,
                        positionClass: 'toast-bottom-right'
                    };
                    toastr.success(
                        `${res.success || 'Đã thêm vào giỏ hàng!'}<br><a href="{{ route('cart.index') }}" class="btn btn-sm btn-primary mt-2 inline-block">Xem giỏ hàng</a>`
                    );

                    const $cartBadge = $('#cart-badge');
                    if ($cartBadge.length && res.cartItemCount !== undefined) {
                        if (res.cartItemCount > 0) {
                            $cartBadge.text(res.cartItemCount).show();
                        } else {
                            $cartBadge.hide();
                        }
                    }
                },

                error: function(xhr) {
                    let errMsg = 'Có lỗi xảy ra, vui lòng thử lại.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errMsg = xhr.responseJSON.error;
                    }
                    toastr.error(errMsg);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
