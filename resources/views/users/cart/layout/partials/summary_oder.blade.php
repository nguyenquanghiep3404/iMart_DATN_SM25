<aside class="col-lg-4" style="margin-top: -100px">
    <div class="order-summary-sticky">
        <div class="bg-white rounded shadow-sm p-4">
            <!-- Always visible promotion section -->
            <div class="mb-3">
                <!-- Nút mở modal chọn mã khuyến mãi, với giao diện giống giao diện đầu -->
                <button type="button"
                    class="d-flex justify-content-between align-items-center p-3 border rounded bg-light mb-3 w-100 text-start"
                    data-bs-toggle="modal" data-bs-target="#couponModal" style="background-color: #f8f9fa;">
                    <span class="fw-medium text-danger">Chọn hoặc nhập ưu đãi</span>
                    <i class="ci-chevron-right text-muted"></i>
                </button>


                <div class="bg-body-tertiary rounded-5 p-4 mb-3">
                    @guest
                        {{-- TRƯỜNG HỢP 1: KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP) --}}
                        <a href="{{ route('login') }}" class="d-flex align-items-center text-decoration-none">
                            <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle flex-shrink-0"
                                style="width: 40px; height: 40px;">
                                <i class="ci-gift fs-xl text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <div class="fw-medium text-dark">Đăng nhập để dùng điểm</div>
                                <p class="fs-xs text-muted mb-0">Tích lũy và sử dụng điểm cho mọi đơn hàng.</p>
                            </div>
                        </a>
                    @endguest

                    @auth
                        {{-- TRƯỜNG HỢP 2 & 3: NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP --}}
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle flex-shrink-0"
                                style="width: 40px; height: 40px;">
                                <i class="ci-gift fs-xl text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <div class="fw-medium text-dark">Điểm thưởng của bạn</div>
                                <p class="fs-sm text-primary fw-semibold mb-0">
                                    {{ number_format(Auth::user()->loyalty_points_balance) }} điểm</p>
                            </div>
                        </div>

                        @if (Auth::user()->loyalty_points_balance > 0)
                            {{-- CÓ ĐIỂM: HIỂN THỊ FORM SỬ DỤNG --}}
                            <div id="points-form" class="mt-3">
                                <div class="d-flex gap-2">
                                    <input type="number" id="points-to-use" class="form-control"
                                        placeholder="Nhập số điểm">
                                    <button type="button" id="apply-points-btn" class="btn btn-dark flex-shrink-0">Áp
                                        dụng</button>
                                </div>
                                <div id="points-message" class="mt-2 small"></div>
                            </div>
                        @else
                            {{-- KHÔNG CÓ ĐIỂM: HIỂN THỊ THÔNG BÁO --}}
                            <p class="fs-xs text-muted mb-0 mt-2">
                                Bạn chưa có điểm thưởng. Hãy mua sắm để tích lũy ngay!
                            </p>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Scrollable order information -->
            <div class="border-top pt-4">
                <h4 class="h6 mb-3">Thông tin đơn hàng</h4>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Tổng (<span id="total-quantity">{{ $items->sum('quantity') }}</span>
                        sản phẩm):</span>
                    <span id="cart-subtotal" class="text-dark-emphasis fw-medium">
                        {{ number_format($subtotal, 0, ',', '.') }}₫
                    </span>
                </div>

                <li class="d-flex justify-content-between mb-2">
                    Giảm giá:
                    <span id="cart-discount" class="text-danger fw-medium">
                        {{ $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫' }}
                    </span>
                </li>

                @php
                    $pointDiscount = session('points_applied.discount', 0);
                @endphp

                <li class="d-flex justify-content-between mb-2" id="points-discount-row"
                    style="{{ $pointDiscount > 0 ? '' : 'display: none;' }}">
                    <span class="text-muted small">Giảm từ điểm:</span>
                    <span id="points-discount-amount" class="text-danger fw-medium">
                        -{{ number_format($pointDiscount, 0, ',', '.') }}₫
                    </span>
                </li>


                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">Phí vận chuyển:</span>
                    <span id="shipping-fee-summary" class="fw-medium">Chưa xác định</span>
                </div>

                <div class="d-flex justify-content-between border-top pt-3 mb-3">
                    <span class="fw-bold">Cần thanh toán:</span>
                    <span id="cart-total"
                        class="fw-bold text-danger h6">{{ number_format($total, 0, ',', '.') }}₫</span>
                </div>

                <!-- <div class="d-flex justify-content-between">
                    <span class="text-muted small">Điểm thưởng</span>
                    <span id="points-summary" class="fw-medium text-warning small">
                        <i class="ci-star-filled"></i> +{{ number_format($totalPointsToEarn) }}
                    </span>
                </div> -->
                <a href="#" class="text-decoration-none small">Xem chi tiết</a>

                <!-- Order Button -->
                <div class="mt-4 pt-3 border-top" style="margin-bottom: 20px;">
                    <a class="btn btn-lg btn-primary w-100 mb-3" href="{{ route('payments.information') }}">
                        Tiến hành thanh toán
                        <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                    </a>
                    <p class="text-muted small text-center mb-0" style="font-size: 0.75rem; line-height: 1.3;">
                        Bằng việc tiến hành đặt mua hàng, bạn đồng ý với
                        <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> và
                        <a href="#" class="text-decoration-none">Chính sách xử lý dữ liệu cá nhân</a> của chúng
                        tôi.
                    </p>
                </div>
            </div>

        </div>
    </div>
</aside>
<!-- Modal chọn mã khuyến mãi -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                <div class="flex items-center gap-3 w-100">
                    <i class="fa-solid fa-ticket text-xl text-red-500"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Chọn mã khuyến mãi</h2>
                </div>
                <button id="closeModalBtn" data-bs-dismiss="modal"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            <div class="modal-body max-h-[65vh] overflow-y-auto pr-2 custom-scrollbar">
                <div class="p-5 space-y-5">
                    <!-- Input field for promo code -->
                    <div class="w-full">
                        <form id="voucher-form" class="needs-validation d-flex gap-2" novalidate>
                            @csrf
                            <input type="text" id="promoInput" name="voucher_code"
                                placeholder="Nhập mã khuyến mãi của bạn"
                                class="flex-grow w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-red-500 transition-colors">
                            <button id="applyInputBtn"
                                class="px-6 py-2.5 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition-colors">
                                Áp dụng
                            </button>
                        </form>
                    </div>

                    <!-- Separator -->
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-200"></div>
                        <span class="flex-shrink mx-4 text-gray-400 text-sm">Hoặc chọn mã có sẵn</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>

                    <!-- List of available promos -->
                    <div id="promoList" class="space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                        @php
                            $now = \Carbon\Carbon::now();
                        @endphp

                        @if ($availableCoupons->count())
                            @foreach ($availableCoupons as $coupon)
                                @php
                                    $startDate = \Carbon\Carbon::parse($coupon->start_date);
                                    $endDate = \Carbon\Carbon::parse($coupon->end_date);
                                    $isDisabled = $now->lt($startDate) || $now->gt($endDate);
                                @endphp

                                <div class="promo-item border-2 rounded-lg p-4 cursor-pointer transition-all duration-200
                                    {{ $isDisabled ? 'border-gray-200 opacity-50 cursor-not-allowed' : 'border-gray-200 hover:border-red-500' }}"
                                    data-code="{{ $coupon->code }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-grow">
                                            <p class="font-bold text-gray-800 text-lg">{{ $coupon->code }}</p>
                                            <p class="text-gray-600 text-sm mt-1">
                                                {{ $coupon->description ?? ($coupon->type === 'percentage' ? "Giảm {$coupon->value}%" : 'Giảm ' . number_format($coupon->value, 0, ',', '.') . '₫') }}
                                            </p>
                                            <p
                                                class="text-xs {{ $isDisabled ? 'text-gray-400' : 'text-gray-500' }} mt-2">
                                                <i class="fa-regular fa-calendar-alt mr-1"></i>
                                                HSD: {{ $endDate->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <input type="radio" name="selected_coupon" value="{{ $coupon->code }}"
                                            class="custom-radio mt-1" {{ $isDisabled ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p>Hiện không có mã khuyến mãi nào khả dụng.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer w-full">
                <button type="button"
                    class="btn btn-primary w-100 px-6 py-2.5 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition-colors"
                    id="applySelectedCouponBtn">Áp dụng mã</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#apply-points-btn').on('click', function() {
            const $btn = $(this);
            const points = $('#points-to-use').val();
            const $messageDiv = $('#points-message');

            if (!points || parseInt(points) <= 0) {
                $messageDiv.html('<span class="text-danger">Vui lòng nhập số điểm hợp lệ.</span>');
                return;
            }

            $btn.prop('disabled', true).html('Đang xử lý...');
            $messageDiv.html('');

            $.ajax({
                url: "{{ route('cart.applyPoints') }}",
                method: 'POST',
                data: {
                    points: points,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $messageDiv.html(
                            `<span class="text-success">${res.message}</span>`);

                        // Cập nhật giao diện tổng tiền
                        $('#points-discount-row').show();
                        $('#points-discount-amount').text(
                            `- ${res.discount_amount.toLocaleString('vi-VN')}₫`);
                        $('#cart-total').text(
                            `${res.new_grand_total.toLocaleString('vi-VN')}₫`);
                    } else {
                        toastr.error(res.message);
                        $messageDiv.html(`<span class="text-danger">${res.message}</span>`);
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                    $messageDiv.html(
                        '<span class="text-danger">Có lỗi xảy ra, vui lòng thử lại.</span>'
                    );
                },
                complete: function() {
                    $btn.prop('disabled', false).html('Áp dụng');
                }
            });
        });


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

        function formatPrice(num) {
            if (typeof num !== 'number') return '0₫';
            return num.toLocaleString('vi-VN') + '₫';
        }

        // --- HÀM CẬP NHẬT GIAO DỊCH (QUAN TRỌNG) ---
        function sendUpdateRequest(itemId, quantity, forceUpdate = false) {
            // Vô hiệu hóa các nút
            $('.cart-item [data-increment], .cart-item [data-decrement]').prop('disabled', true);

            $.post("{{ route('cart.updateQuantity') }}", {
                item_id: itemId,
                quantity: quantity,
                force_update: forceUpdate,
            }).done((res) => {
                if (res.success) {
                    // Cập nhật tóm tắt đơn hàng với dữ liệu số thô từ server
                    $('#cart-subtotal').text(formatPrice(res.subtotal_before_dc));
                    $('#cart-discount').text(res.discount > 0 ? '-' + formatPrice(res.discount) : '0₫');
                    $('#cart-total').text(formatPrice(res.total_after_dc));

                    // CẬP NHẬT ĐIỂM THƯỞNG
                    if (res.total_points_earned !== undefined) {
                        $('#points-summary').html(
                            `<i class="ci-star-filled"></i> +${res.total_points_earned.toLocaleString('vi-VN')}`
                        );
                    }

                    if (res.voucher_removed) {
                        toastr.info('Mã giảm giá đã bị huỷ vì đơn hàng không còn đủ điều kiện.');
                    }
                } else {
                    toastr.error(res.message || 'Không thể cập nhật số lượng.');
                    revertQuantityInput(itemId);
                }
            }).fail(() => {
                toastr.error('Lỗi kết nối khi cập nhật số lượng.');
                revertQuantityInput(itemId);
            }).always(() => {
                // Kích hoạt lại các nút
                $('.cart-item [data-increment], .cart-item [data-decrement]').prop('disabled', false);
                updateButtonState($(`.cart-item[data-item-id="${itemId}"]`));
            });
        }

        // Hàm xử lý gọi ajax dùng chung
        function applyVoucher(voucherCode) {
            $.ajax({
                url: '{{ route('cart.applyVoucherAjax') }}',
                method: 'POST',
                data: {
                    voucher_code: voucherCode,
                    type: 'cart'
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
    document.addEventListener('DOMContentLoaded', function() {
        // Khi click vào promo-item, chọn radio bên trong nếu không bị disabled
        document.querySelectorAll('.promo-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                // Nếu click đúng radio thì không xử lý thêm nữa
                if (e.target.tagName.toLowerCase() === 'input' && e.target.type === 'radio')
                    return;

                const radio = item.querySelector('input[type="radio"]');
                if (radio && !radio.disabled) {
                    radio.checked = true;
                }
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const promoItems = document.querySelectorAll('.promo-item');
        const messageBox = document.getElementById('messageBox');
        const promoInput = document.getElementById('promoInput');

        // Handle selection from the list
        promoItems.forEach(item => {
            item.addEventListener('click', () => {
                // Remove selection style from all items
                promoItems.forEach(i => i.classList.remove('selected', 'border-red-500'));

                // Add selection style to the clicked item
                item.classList.add('selected', 'border-red-500');

                // Check the radio button inside the clicked item
                const radio = item.querySelector('input[type="radio"]');
                radio.checked = true;

                // Clear input field if user selects from list
                promoInput.value = '';
                messageBox.textContent = '';
            });
        });

        // Clear list selection if user types in the input
        promoInput.addEventListener('input', () => {
            if (promoInput.value.trim() !== '') {
                promoItems.forEach(i => {
                    i.classList.remove('selected', 'border-red-500');
                    i.querySelector('input[type="radio"]').checked = false;
                });
                messageBox.textContent = '';
            }
        });

        // Handle "Apply" button for the selected item in the list
        document.getElementById('applySelectedBtn').addEventListener('click', function() {
            const selectedRadio = document.querySelector('input[name="promo_code"]:checked');
            const enteredCode = promoInput.value.trim().toUpperCase();

            let codeToApply = '';

            if (enteredCode) {
                codeToApply = enteredCode;
            } else if (selectedRadio) {
                codeToApply = selectedRadio.value;
            }

            if (codeToApply) {
                messageBox.textContent = `Đã áp dụng mã: ${codeToApply}`;
                messageBox.className = 'text-center mt-3 text-sm font-medium h-5 text-green-600';
                console.log(`Applying code: ${codeToApply}`);
                // You can add logic here to close the modal and apply the code to the cart
            } else {
                messageBox.textContent = 'Vui lòng chọn hoặc nhập một mã khuyến mãi.';
                messageBox.className = 'text-center mt-3 text-sm font-medium h-5 text-red-500';
            }
        });

        // Handle "Apply" button next to the input field
        document.getElementById('applyInputBtn').addEventListener('click', function() {
            const enteredCode = promoInput.value.trim().toUpperCase();
            if (enteredCode) {
                messageBox.textContent = `Đã áp dụng mã: ${enteredCode}`;
                messageBox.className = 'text-center mt-3 text-sm font-medium h-5 text-green-600';
                console.log(`Applying code from input: ${enteredCode}`);
            } else {
                messageBox.textContent = 'Vui lòng nhập mã khuyến mãi.';
                messageBox.className = 'text-center mt-3 text-sm font-medium h-5 text-red-500';
            }
        });

        // Logic to close the modal (for demonstration)
        const modal = document.querySelector('.fixed.inset-0');
        document.getElementById('closeModalBtn').addEventListener('click', () => {
            modal.style.display = 'none'; // In a real app, you would manage this with state
        });
    });
</script>
