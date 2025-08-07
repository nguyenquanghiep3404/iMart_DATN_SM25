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
                    voucher_code: voucherCode,
                    type: 'buy-now'
                },
                success: function(response) {
                    const formatMoney = (amount) => amount.toLocaleString('vi-VN') + '₫';

                    if (response.success) {
                        toastr.success(response.message);
                        $('#cart-discount').text('-' + formatMoney(response.discount));
                        $('#cart-total').text(formatMoney(response.total_after_discount));
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
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
    // xử lý điểm thưởng
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
    });
</script>
