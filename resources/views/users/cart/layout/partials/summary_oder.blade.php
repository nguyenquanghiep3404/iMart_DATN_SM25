<aside class="col-lg-4" style="margin-top: -100px">
    <div class="position-sticky top-0" style="padding-top: 100px">
        <div class="bg-body-tertiary rounded-5 p-4 mb-3">
            <div class="p-sm-2 p-lg-0 p-xl-2">
                <h5 class="border-bottom pb-4 mb-4">Tóm tắt đơn hàng</h5>
                <ul class="list-unstyled fs-sm gap-3 mb-0">
                    <li class="d-flex justify-content-between">
                        <span>
                            Tổng (<span id="total-quantity">{{ $items->sum('quantity') }}</span> sản phẩm):
                        </span>
                        <span id="cart-subtotal" class="text-dark-emphasis fw-medium">
                            {{ number_format($subtotal, 0, ',', '.') }}₫
                        </span>
                    </li>

                    <li class="d-flex justify-content-between">
                        Giảm giá:
                        <span id="cart-discount" class="text-danger fw-medium">
                            {{ $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫' }}
                        </span>
                    </li>
                </ul>
                <div class="border-top pt-4 mt-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-sm">Tổng Tiền:</span>
                        <span id="cart-total" class="h5 mb-0">
                            {{ number_format($total, 0, ',', '.') }}₫
                        </span>
                    </div>
                    <a class="btn btn-lg btn-primary w-100" href="{{ route('payments.information') }}">
                        Tiến hành thanh toán
                        <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                    </a>
                    {{-- <div class="nav justify-content-center fs-sm mt-3">
                        <a class="nav-link text-decoration-underline p-0 me-1" href="#authForm"
                            data-bs-toggle="offcanvas" role="button">Create an account</a>
                        and get
                        <span class="text-dark-emphasis fw-medium ms-1">239 bonuses</span>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="accordion bg-body-tertiary rounded-5 p-4">
            <div class="accordion-item border-0">
                <h3 class="accordion-header" id="promoCodeHeading">
                    <button type="button"
                        class="accordion-button animate-underline collapsed py-0 ps-sm-2 ps-lg-0 ps-xl-2"
                        data-bs-toggle="collapse" data-bs-target="#promoCode" aria-expanded="false"
                        aria-controls="promoCode">
                        <i class="ci-percent fs-xl me-2"></i>
                        <span class="animate-target me-2">Nhập mã khuyến mãi(nếu có)</span>
                    </button>
                </h3>
                <div class="accordion-collapse collapse" id="promoCode" aria-labelledby="promoCodeHeading">
                    <div class="accordion-body pt-3 pb-2 ps-sm-2 px-lg-0 px-xl-2">
                        <form id="voucher-form" class="needs-validation d-flex gap-2" novalidate>
                            @csrf
                            <div class="position-relative w-100">
                                <input type="text" name="voucher_code" class="form-control"
                                    placeholder="Enter promo code" required>
                                <div class="invalid-tooltip bg-transparent py-0">Enter a valid promo code!</div>
                            </div>
                            <button type="submit" class="btn btn-dark">Áp dụng</button>
                        </form>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary w-100 mt-3" data-bs-toggle="modal"
                    data-bs-target="#couponModal">
                    <i class="ci-percent me-2"></i> Chọn mã khuyến mãi
                </button>
            </div>
        </div>
    </div>
</aside>
<!-- Modal chọn mã khuyến mãi -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title" id="couponModalLabel">🎁 Chọn mã khuyến mãi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                @php
                    $now = \Carbon\Carbon::now();
                @endphp

                @if ($availableCoupons->count())
                    <div class="list-group">
                        @foreach ($availableCoupons as $coupon)
                            @php
                                $startDate = \Carbon\Carbon::parse($coupon->start_date);
                                $endDate = \Carbon\Carbon::parse($coupon->end_date);
                                $isDisabled = $now->lt($startDate) || $now->gt($endDate);
                                $discount =
                                    $coupon->type === 'percentage'
                                        ? "Giảm {$coupon->value}%"
                                        : 'Giảm ' . number_format($coupon->value, 0, ',', '.') . '₫';
                            @endphp

                            <label
                                class="list-group-item d-flex justify-between align-items-center {{ $isDisabled ? 'opacity-50' : '' }}"
                                style="cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }};">
                                <div>
                                    <strong>{{ $coupon->code }}</strong>
                                    <div class="text-muted small">
                                        {{ $coupon->description ?? $discount }}<br>
                                        Thời gian áp dụng: {{ $startDate->format('d/m/Y') }} -
                                        {{ $endDate->format('d/m/Y') }}<br>
                                        Đơn tối thiểu:
                                        {{ $coupon->min_order_amount ? number_format($coupon->min_order_amount, 0, ',', '.') . '₫' : 'Không' }}
                                    </div>
                                </div>
                                <input type="radio" class="form-check-input mt-0 coupon-radio" name="selected_coupon"
                                    value="{{ $coupon->code }}" data-disabled="{{ $isDisabled ? '1' : '0' }}"
                                    {{ $isDisabled ? 'disabled' : '' }}>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p>Hiện không có mã khuyến mãi nào khả dụng.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="applySelectedCouponBtn">Áp dụng mã</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Cấu hình toastr
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

        // Setup CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Hàm xử lý gọi ajax dùng chung
        function applyVoucher(voucherCode) {
            $.ajax({
                url: '{{ route('cart.applyVoucherAjax') }}',
                method: 'POST',
                data: {
                    voucher_code: voucherCode
                },
                success: function(response) {
                    const formatMoney = (amount) => amount.toLocaleString('vi-VN') + '₫';

                    if (response.success) {
                        toastr.success(response.message);
                        $('#cart-discount').text('-' + formatMoney(response.discount));
                        $('#cart-total').text(formatMoney(response.total_after_discount));
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi AJAX:', error);
                    toastr.error('Đã có lỗi xảy ra. Vui lòng thử lại!');
                }
            });
        }

        // Gửi từ form nhập mã voucher
        $('#voucher-form').on('submit', function(e) {
            e.preventDefault();
            const voucherCode = $(this).find('input[name="voucher_code"]').val();
            applyVoucher(voucherCode);
        });

        // Gửi từ nút chọn mã trong danh sách gợi ý
        $('#applySelectedCouponBtn').on('click', function() {
            const selectedCode = $('input[name="selected_coupon"]:checked').val();

            if (!selectedCode) {
                toastr.warning('Vui lòng chọn một mã khuyến mãi trước khi áp dụng.');
                return;
            }

            applyVoucher(selectedCode);
        });
    });
</script>

{{-- <script>
    $(document).ready(function() {
        // Cấu hình toastr
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $('#voucher-form').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const voucherCode = form.find('input[name="voucher_code"]').val();

            $.ajax({
                url: '{{ route('cart.applyVoucherAjax') }}',
                method: 'POST',
                data: {
                    voucher_code: voucherCode
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);

                        const formatMoney = (amount) => amount.toLocaleString('vi-VN') +
                            '₫';

                        $('#cart-discount').text('-' + formatMoney(response.discount));
                        $('#cart-total').text(formatMoney(response.total_after_discount));
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi AJAX:', error);
                    toastr.error('Đã có lỗi xảy ra. Vui lòng thử lại!');
                }
            });
        });
    });
    $('#applySelectedCouponBtn').on('click', function() {
        const selectedCode = $('input[name="selected_coupon"]:checked').val();

        if (!selectedCode) {
            toastr.warning('Vui lòng chọn một mã khuyến mãi trước khi áp dụng.');
            return;
        }

        $.ajax({
            url: '{{ route('cart.applyVoucherAjax') }}',
            method: 'POST',
            data: {
                voucher_code: selectedCode
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);

                    const formatMoney = (amount) => amount.toLocaleString('vi-VN') + '₫';
                    $('#cart-discount').text('-' + formatMoney(response.discount));
                    $('#cart-total').text(formatMoney(response.total_after_discount));

                    // ❌ Không ẩn modal nữa
                    // => Người dùng có thể thử các mã khác nếu muốn
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi AJAX:', error);
                toastr.error('Đã có lỗi xảy ra. Vui lòng thử lại!');
            }
        });
    });
</script> --}}
