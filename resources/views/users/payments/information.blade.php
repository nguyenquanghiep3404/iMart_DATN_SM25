@extends('users.layouts.app')

@section('content')
    <style>
        .option-card {
            border: 2px solid #e5e7eb;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
            cursor: pointer;
        }
        .option-card.selected {
            border-color: #dc3545;
            background-color: #fff5f5;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }
        .form-control:focus, .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none; /* Sẽ được JS bật lên khi có lỗi */
            align-items: center;
            gap: 0.25rem;
        }
        .error-message i { font-size: 1rem; }
        .shipment-block {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #ffffff;
        }
        .cursor-pointer { cursor: pointer; }
        .address-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .address-card:hover {
            border-color: #dc3545;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .address-card.selected {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        .store-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .store-item:hover {
            border-color: #dc3545;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .store-item.selected {
            border-color: #dc3545;
            background-color: #fff5f5;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }

        .order-summary-sticky {
            position: sticky;
            top: 2rem;
        }
        
        @media (max-width: 991.98px) {
            .order-summary-sticky {
                position: static;
            }
        }
    </style>

    <main class="content-wrapper" style="min-height: 100vh;">
        <div class="container py-5">
            <div class="row pt-1 pt-sm-3 pt-lg-4 pb-2 pb-md-3 pb-lg-4 pb-xl-5">
                <div class="col-lg-8 col-xl-7 mb-5 mb-lg-0">
                    <div class="d-flex flex-column gap-4">

                        {{-- Section 1: Shipping Information --}}
                        <div class="bg-white rounded shadow-sm p-4">
                            <h2 class="h5 mb-4">1. Thông tin nhận hàng</h2>
                            <div class="mb-4">
                                <h3 class="h6 mb-3">Phương thức nhận hàng</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="option-card rounded p-3 selected" id="delivery-method-delivery">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_method" value="delivery" id="delivery_method_radio_delivery" checked>
                                                <label class="form-check-label fw-medium w-100" for="delivery_method_radio_delivery">Giao hàng tận nơi</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="option-card rounded p-3" id="delivery-method-pickup">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_method" value="pickup" id="delivery_method_radio_pickup">
                                                <label class="form-check-label fw-medium w-100" for="delivery_method_radio_pickup">Nhận tại cửa hàng</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Error message for delivery method --}}
                                <div id="delivery_method_error" class="error-message" style="display: none;">
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                    <span>Vui lòng chọn phương thức nhận hàng</span>
                                </div>
                            </div>

                            {{-- Delivery Address Section --}}
                            <div id="delivery-address-section">
                                <hr class="my-4">
                                @auth
                                    @if(Auth::user()->addresses->isNotEmpty())
                                    <div id="address-book">
                                        <h3 class="h6 mb-3">Địa chỉ giao hàng</h3>
                                        <div id="main-address-list" class="mb-3"></div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" id="open-address-modal-btn" class="btn btn-outline-secondary btn-sm"><i class="fas fa-exchange-alt me-1"></i>Chọn từ sổ địa chỉ</button>
                                            <button type="button" id="use-new-address-btn" class="btn btn-outline-dark btn-sm"><i class="fas fa-plus me-1"></i>Thêm địa chỉ mới</button>
                                        </div>
                                    </div>
                                    @endif
                                @endauth
                                <div id="new-address-form-wrapper" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h3 id="form-intro-text" class="h6 mb-0">Thông tin nhận hàng</h3>
                                        @auth
                                            @if(Auth::user()->addresses->isNotEmpty())
                                                <button type="button" id="back-to-address-list-btn" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                                                </button>
                                            @endif
                                        @endauth
                                    </div>
                                    <form id="address-form" class="row g-3">
                                        {{-- Content from address_form_fields.blade.php --}}
                                        <div class="col-md-6">
                                            <label for="full_name" class="form-label">Họ & tên <span class="text-danger">*</span></label>
                                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                                            <div id="full_name_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng nhập tên</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                            <input type="tel" id="phone_number" name="phone_number" class="form-control" required>
                                            <div id="phone_number_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng nhập số điện thoại</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" id="email" name="email" class="form-control" required>
                                            <div id="email_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng nhập email hợp lệ</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="province_id" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                            <select id="province_id" name="province_id" class="form-select" required>
                                                <option value="">Chọn Tỉnh/Thành phố</option>
                                            </select>
                                            <div id="province_id_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng chọn Tỉnh/Thành phố</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="district_id" class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                            <select id="district_id" name="district_id" class="form-select" required disabled>
                                                <option value="">Chọn Quận/Huyện</option>
                                            </select>
                                            <div id="district_id_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng chọn Quận/Huyện</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ward_id" class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                            <select id="ward_id" name="ward_id" class="form-select" required disabled>
                                                <option value="">Chọn Phường/Xã</option>
                                            </select>
                                            <div id="ward_id_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng chọn Phường/Xã</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="address_line1" class="form-label">Số nhà, Tên đường <span class="text-danger">*</span></label>
                                            <input type="text" id="address_line1" name="address_line1" class="form-control" required>
                                            <div id="address_line1_error" class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Vui lòng nhập số nhà, tên đường</span>
                                            </div>
                                        </div>
                                        {{-- Hidden fields for address system and location codes validation --}}
                                        <input type="hidden" name="address_system" value="old">
                                        <input type="hidden" id="province_code" name="province_code">
                                        <input type="hidden" id="district_code" name="district_code">
                                        <input type="hidden" id="ward_code" name="ward_code">
                                        
                                        @auth
                                        <div id="save-address-wrapper" class="col-12 d-none">
                                            <div class="form-check">
                                                <input id="save-address-check" name="save_address" type="checkbox" class="form-check-input" checked>
                                                <label for="save-address-check" class="form-check-label">
                                                    Lưu địa chỉ này vào sổ địa chỉ
                                                </label>
                                            </div>
                                        </div>
                                        @endauth
                                        {{-- End of address_form_fields.blade.php content --}}
                                    </form>
                                </div>
                            </div>

                            {{-- Pickup Location Section --}}
                            <div id="pickup-location-section" class="d-none">
                                <hr class="my-4">
                                <h3 class="h6 mb-3">Thông tin người nhận</h3>
                                <div class="row g-3 mb-4">
                                    {{-- Content from pickup_form_fields.blade.php --}}
                                    <div class="col-md-6">
                                        <label for="pickup_full_name" class="form-label">Họ & tên <span class="text-danger">*</span></label>
                                        <input type="text" id="pickup_full_name" name="pickup_full_name" class="form-control" required>
                                        <div id="pickup_full_name_error" class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>Vui lòng nhập tên</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pickup_phone_number" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" id="pickup_phone_number" name="pickup_phone_number" class="form-control" required>
                                        <div id="pickup_phone_number_error" class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>Vui lòng nhập số điện thoại</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="pickup_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" id="pickup_email" name="pickup_email" class="form-control" required>
                                        <div id="pickup_email_error" class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>Vui lòng nhập email hợp lệ</span>
                                        </div>
                                    </div>
                                    {{-- End of pickup_form_fields.blade.php content --}}
                                </div>
                                <hr class="my-4">
                                <div id="pickup-store-selection">
                                    <h3 class="h6 mb-3" style="color: black; font-weight: bold;">Chọn cửa hàng để nhận hàng <span class="text-danger">*</span></h3>
                                    <!-- Button chọn cửa hàng -->
                                    <div class="mb-3">
                                        <button type="button" id="select-store-btn"
                                            class="btn btn-outline-secondary w-100 text-start d-flex align-items-center justify-content-between">
                                            <span id="store-selection-text">Chọn cửa hàng nhận hàng</span>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </button>
                                    </div>
                                    <!-- Hiển thị cửa hàng đã chọn -->
                                    <div id="selected-store-display" class="mb-3" style="display: none;">
                                        <div class="border rounded p-3 bg-light">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-store text-primary me-3 mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <strong id="selected-store-name" class="d-block text-dark"></strong>
                                                    <span id="selected-store-address" class="d-block small text-muted"></span>
                                                    <span id="selected-store-phone" class="d-block small text-muted"></span>
                                                </div>
                                                <button type="button" id="change-store-btn" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Đổi
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="store_location_error" class="error-message"><i class="fas fa-exclamation-circle"></i><span>Vui lòng chọn cửa hàng</span></div>
                                </div>
                                
                                <!-- Phần ngày nhận hàng đã được xóa -->
                            </div>
                        </div>

                        {{-- Section 2: Products and Shipping --}}
                        <div class="bg-white rounded shadow-sm p-4">
                            <h2 class="h5 mb-4">2. Sản phẩm và Vận chuyển (<span id="product-count">{{ count($items) }}</span>)</h2>
                            <div id="main-product-list" class="mb-4"></div>
                            <div id="shipping_method_error" class="error-message mb-3"><i class="fas fa-exclamation-circle"></i><span>Vui lòng chọn phương thức vận chuyển</span></div>
                            <div id="shipment-list-section" class="d-flex flex-column gap-4"></div>
                            <div id="pickup-summary-section" class="d-none">
                                <h3 class="h6 mb-3">Sản phẩm sẽ nhận (<span id="pickup-product-count">0</span>)</h3>
                                <div id="pickup-product-list" class="d-flex flex-column gap-3"></div>
                            </div>
                        </div>

                        {{-- Section 3: Payment Method --}}
                        <div class="bg-white rounded shadow-sm p-4">
                            <h2 class="h5 mb-4">3. Chọn hình thức thanh toán</h2>
                            <div id="payment_method_error" class="error-message mb-3"><i class="fas fa-exclamation-circle"></i><span>Vui lòng chọn phương thức thanh toán</span></div>
                            <div class="row g-3">
                                {{-- Content from payment_methods.blade.php --}}
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check">
                                            <input id="cod" name="payment_method" type="radio" value="cod" class="form-check-input">
                                            <label for="cod" class="form-check-label w-100 cursor-pointer">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-truck fs-4 text-muted me-3"></i>
                                                    <div>
                                                        <div class="fw-medium">Thanh toán khi nhận hàng (COD)</div>
                                                        <small class="text-muted">Thanh toán bằng tiền mặt</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check">
                                            <input id="qrcode" name="payment_method" type="radio" value="bank_transfer_qr" class="form-check-input">
                                            <label for="qrcode" class="form-check-label w-100 cursor-pointer">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-qrcode fs-4 text-muted me-3"></i>
                                                    <div>
                                                        <div class="fw-medium">Thanh toán bằng mã QR</div>
                                                        <small class="text-muted">Quét mã QR để chuyển khoản</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check">
                                            <input id="vnpay" name="payment_method" type="radio" value="vnpay" class="form-check-input">
                                            <label for="vnpay" class="form-check-label w-100 cursor-pointer">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                         <i class="fas fa-credit-card fs-4 text-muted me-3"></i>
                                                        <div>
                                                            <div class="fw-medium">Thanh toán qua VNPay</div>
                                                            <small class="text-muted">Hỗ trợ thẻ ATM & Ví điện tử</small>
                                                        </div>
                                                    </div>
                                                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Icon-VNPAY-QR.png" alt="VNPay" style="height: 24px;">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check">
                                            <input id="momo" name="payment_method" type="radio" value="momo" class="form-check-input">
                                            <label for="momo" class="form-check-label w-100 cursor-pointer">
                                                <div class="d-flex align-items-center justify-content-between">
                                                     <div class="d-flex align-items-center">
                                                         <i class="fas fa-wallet fs-4 text-muted me-3"></i>
                                                        <div>
                                                            <div class="fw-medium">Thanh toán qua Ví MoMo</div>
                                                            <small class="text-muted">Ví điện tử MoMo</small>
                                                        </div>
                                                    </div>
                                                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="height: 24px;">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                {{-- End of payment_methods.blade.php content --}}
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="col-lg-4 offset-xl-1">
                    <div class="order-summary-sticky">
                        {{-- Content from order_summary.blade.php --}}
                        <div class="bg-white rounded shadow-sm p-4">
                            <!-- Coupon & Points Section -->
                            <div class="mb-3">
                                <button type="button" class="d-flex justify-content-between align-items-center p-3 border rounded bg-light mb-3 w-100 text-start" data-bs-toggle="modal" data-bs-target="#couponModal" style="background-color: #f8f9fa;">
                                    <span class="fw-medium text-danger">
                                        <i class="fas fa-tags me-2"></i>Chọn hoặc nhập mã ưu đãi
                                    </span>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </button>

                                <div class="bg-light rounded p-3">
                                    @guest
                                    {{-- Guest View --}}
                                    <a href="{{ route('login') }}" class="d-flex align-items-center text-decoration-none">
                                        <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                        <div class="ps-3">
                                            <div class="fw-medium text-dark">Đăng nhập để dùng điểm</div>
                                            <p class="fs-xs text-muted mb-0">Tích lũy và sử dụng điểm cho mọi đơn hàng.</p>
                                        </div>
                                    </a>
                                    @endguest

                                    @auth
                                    {{-- Logged-in User View --}}
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                                            <i class="fas fa-star text-primary"></i>
                                        </div>
                                        <div class="ps-3">
                                            <div class="fw-medium text-dark">Điểm thưởng của bạn</div>
                                            <p class="fs-sm text-primary fw-semibold mb-0">
                                                {{ number_format(Auth::user()->loyalty_points_balance) }} điểm
                                            </p>
                                        </div>
                                    </div>

                                    @if (Auth::user()->loyalty_points_balance > 0)
                                    <div id="points-form" class="mt-3">
                                        <div class="d-flex gap-2">
                                            <input type="number" id="points-to-use" class="form-control form-control-sm" placeholder="Nhập số điểm">
                                            <button type="button" id="apply-points-btn" class="btn btn-dark btn-sm flex-shrink-0">Áp dụng</button>
                                        </div>
                                        <div id="points-message" class="mt-2 small"></div>
                                    </div>
                                    @else
                                    <p class="fs-xs text-muted mb-0 mt-2">
                                        Bạn chưa có điểm thưởng. Hãy mua sắm để tích lũy ngay!
                                    </p>
                                    @endif
                                    @endauth
                                </div>
                            </div>

                            <!-- Order Totals -->
                            <div class="border-top pt-4">
                                <h4 class="h6 mb-3">Thông tin đơn hàng</h4>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Tổng tiền:</span>
                                        <span id="cart-subtotal" class="text-dark fw-medium">{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Giảm từ voucher:</span>
                                        <span id="cart-discount" class="text-danger fw-medium">{{ $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫' }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2" id="points-discount-row" @if(($pointsDiscount ?? 0) == 0) style="display: none;" @endif>
                                        <span class="text-muted small">Giảm từ điểm:</span>
                                        <span id="points-discount-amount" class="text-danger fw-medium">-{{ number_format($pointsDiscount ?? 0, 0, ',', '.') }}₫</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Phí vận chuyển:</span>
                                        <span id="shipping-fee-summary" class="fw-medium">Chưa xác định</span>
                                    </li>
                                    <li class="d-flex justify-content-between border-top pt-3 mb-2">
                                        <span class="fw-bold">Cần thanh toán:</span>
                                        <span id="cart-total" class="fw-bold text-danger h6">{{ number_format($total, 0, ',', '.') }}₫</span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted small">Điểm thưởng sẽ nhận</span>
                                        <span id="points-summary" class="fw-medium text-warning small">
                                            <i class="fas fa-star"></i> +{{ number_format($totalPointsToEarn ?? 0) }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Order Button -->
                            <div class="mt-4 pt-3 border-top">
                                <button type="button" id="place-order-btn" class="btn btn-danger btn-lg w-100 mb-3">
                                    <i class="fas fa-shopping-cart me-2"></i>Đặt hàng
                                </button>
                                <p class="text-muted small text-center mb-0" style="font-size: 0.75rem; line-height: 1.3;">
                                    Bằng việc tiến hành đặt hàng, bạn đồng ý với
                                    <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> và
                                    <a href="#" class="text-decoration-none">Chính sách bảo mật</a> của chúng tôi.
                                </p>
                            </div>
                        </div>
                        {{-- End of order_summary.blade.php content --}}
                    </div>
                </aside>
            </div>
        </div>
    </main>

    {{-- Modals --}}
    {{-- Content from address_modal.blade.php --}}
    <div class="modal fade" id="address-modal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>Chọn địa chỉ giao hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="address-search-input" placeholder="Tìm kiếm theo tên, SĐT, địa chỉ..." class="form-control">
                        </div>
                    </div>
                    <div id="modal-address-list" class="address-list d-flex flex-column gap-2">
                        {{-- Address list will be rendered here by JavaScript --}}
                        <p class="text-center text-muted">Đang tải danh sách địa chỉ...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="button" id="confirm-address-selection-btn" class="btn btn-danger" disabled>
                        <i class="fas fa-check me-1"></i>Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- End of address_modal.blade.php content --}}

    {{-- Content from store_modal.blade.php --}}
    <div class="modal fade" id="store-selection-modal" tabindex="-1" aria-labelledby="storeSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storeSelectionModalLabel" style="color: white; font-weight: bold;">
                        <i class="fas fa-store me-2 text-danger"></i>Chọn cửa hàng nhận hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filter Section -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modal-store-province-select" class="form-label small">Tỉnh/Thành phố</label>
                            <select id="modal-store-province-select" class="form-select">
                                <option value="">Tất cả Tỉnh/Thành phố</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modal-store-district-select" class="form-label small">Quận/Huyện</label>
                            <select id="modal-store-district-select" class="form-select" disabled>
                                <option value="">Tất cả Quận/Huyện</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small" id="store-count-text">Có 0 cửa hàng phù hợp</span>
                    </div>

                    <!-- Store List -->
                    <div id="modal-store-list" class="store-list d-flex flex-column gap-2">
                        <p class="text-muted small text-center">Đang tải danh sách cửa hàng...</p>
                        {{-- Store list will be rendered here by JavaScript --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="button" id="confirm-store-selection-btn" class="btn btn-danger" disabled>
                        <i class="fas fa-check me-1"></i>Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- End of store_modal.blade.php content --}}

    @include('users.cart.layout.partials.modal') {{-- Coupon Modal --}}

    {{-- TEMPLATES for JavaScript --}}
    <template id="shipment-block-template">
        <div class="shipment-block">
            <div class="p-3">
                <h3 class="h6 mb-3 fw-bold"></h3>
                <div class="product-list-container d-flex flex-column gap-3"></div>
            </div>
            <div class="mt-3 border-top p-3">
                <h4 class="small fw-bold mb-2">Phương thức vận chuyển</h4>
                <div class="shipping-options-container">
                    <p class="text-muted small">Vui lòng chọn địa chỉ để tính phí.</p>
                </div>
                <div class="delivery-time-slot-section mt-3 d-none">
                    <h4 class="small fw-bold mb-2">Thời gian giao hàng (Giao bởi cửa hàng)</h4>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Ngày giao</label>
                            <input type="date" name="delivery_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Giờ giao</label>
                            <select name="delivery_time_slot" class="form-select form-select-sm">
                                <option value="">Chọn khung giờ</option>
                                <option value="8-11">8:00 - 11:00</option>
                                <option value="11-14">11:00 - 14:00</option>
                                <option value="14-17">14:00 - 17:00</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="product-item-template">
        <div class="d-flex align-items-start gap-3">
            <img src="" alt="" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
            <div class="flex-grow-1 min-w-0">
                <p class="fw-medium text-dark small mb-1 product-name" title=""></p>
                <span class="badge bg-light text-dark small product-variant"></span>
            </div>
            <div class="text-end">
                <p class="fw-bold text-danger small mb-0 product-price"></p>
                <p class="text-muted small mb-1 product-quantity"></p>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
@php
    // Chuẩn bị toàn bộ dữ liệu trong một khối PHP để tránh lỗi parse của Blade
    $checkout_config = [
        'isLoggedIn' => Auth::check(),
        'hasAddresses' => isset($userAddresses) && $userAddresses->isNotEmpty(),
        'user' => Auth::check() && Auth::user() ? ['name' => Auth::user()->name, 'email' => Auth::user()->email, 'phone' => Auth::user()->phone_number] : null,
        'addresses' => isset($userAddresses) && $userAddresses->isNotEmpty() ? $userAddresses->map(function($addr) {
            return [
                'id' => $addr->id,
                'name' => $addr->full_name,
                'phone' => $addr->phone_number,
                'full' => $addr->full_address_with_type,
                'default' => $addr->is_default_shipping,
                'province_code' => $addr->address_system === 'new' ? $addr->new_province_code : $addr->old_province_code,
                'address_system' => $addr->address_system,
                'new_province_code' => $addr->new_province_code,
                'old_province_code' => $addr->old_province_code,
                'province_name' => $addr->province ? $addr->province->name : '',
                'district_name' => $addr->district ? $addr->district->name : '',
                'ward_name' => $addr->ward ? $addr->ward->name : ''
            ];
        })->values()->all() : [],
        'cartItems' => $items->map(function($item) {
            $productVariant = $item->productVariant ?? ($item->cartable ?? null);
            if ($productVariant && $productVariant->product) {
                return [
                    'id' => $item->id,
                    'product_variant_id' => $productVariant->id,
                    'name' => $productVariant->product->name,
                    'variant' => $productVariant->attributeValues->pluck('value')->implode(', '),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'image' => $productVariant->image_url ?? asset('assets/users/img/no-image.png'),
                    'store_location_name' => $item->store_location_name ?? 'Kho tổng',
                    'store_location_id' => $item->store_location_id ?? null,
                    'weight' => $productVariant->weight ?? 1000,
                    'length' => $productVariant->length ?? 20,
                    'width' => $productVariant->width ?? 10,
                    'height' => $productVariant->height ?? 10,
                ];
            }
            return null;
        })->filter()->values()->all(),
        'subtotal' => $subtotal,
        'discount' => $discount,
        'pointsDiscount' => $pointsDiscount ?? 0,
        'totalPointsToEarn' => $totalPointsToEarn ?? 0,
        'urls' => [
            'process' => isset($is_buy_now) && $is_buy_now ? route('buy-now.process') : (Auth::check() ? route('payments.process') : route('payments.process.guest')),
            'ghnFee' => route("ajax.ghn.shipping-fee"),
            'provinces' => '/api/locations/old/provinces',
            'districts' => '/api/locations/old/districts',
            'wards' => '/api/locations/old/wards',
            'storeProvinces' => route("api.stores.provinces"),
            'storeDistricts' => route("api.stores.districts"),
            'storeLocations' => route("api.stores.locations")
        ],
        'csrfToken' => csrf_token()
    ];
@endphp
<script>
    // --- DATA FROM BLADE ---
    window.checkoutConfig = @json($checkout_config);

    // --- REFACTORED JAVASCRIPT LOGIC ---
    const CheckoutPage = {
        state: {},
        elements: {},
        ghnFeeCache: {},
        
        init(config) {
            this.state = { ...config };
            this.state.selectedAddressId = this.state.addresses?.find(a => a.default === true || a.default === 1)?.id || 
                                            (this.state.addresses?.length > 0 ? this.state.addresses[0].id : null);
            this.state.selectedStore = null;
            this.state.shippingFee = null;
            this.cacheElements();
            this.bindEvents();
            this.setupInitialUI();
            this.setupInputValidation(); 
        },

        cacheElements() {
            // Main Sections
            this.elements.deliveryMethodCards = document.querySelectorAll('#delivery-method-delivery, #delivery-method-pickup');
            this.elements.deliveryAddressSection = document.getElementById('delivery-address-section');
            this.elements.pickupLocationSection = document.getElementById('pickup-location-section');
            this.elements.shipmentListSection = document.getElementById('shipment-list-section');
            this.elements.pickupSummarySection = document.getElementById('pickup-summary-section');

            // Address-related elements
            this.elements.addressBook = document.getElementById('address-book');
            this.elements.mainAddressList = document.getElementById('main-address-list');
            this.elements.newAddressFormWrapper = document.getElementById('new-address-form-wrapper');
            this.elements.useNewAddressBtn = document.getElementById('use-new-address-btn');
            this.elements.backToAddressListBtn = document.getElementById('back-to-address-list-btn');
            this.elements.openAddressModalBtn = document.getElementById('open-address-modal-btn');
            this.elements.addressForm = document.getElementById('address-form');
            this.elements.saveAddressWrapper = document.getElementById('save-address-wrapper');
            this.elements.formIntroText = document.getElementById('form-intro-text');

            // Pickup-related elements
            this.elements.pickupFullName = document.getElementById('pickup_full_name');
            this.elements.pickupPhoneNumber = document.getElementById('pickup_phone_number');
            this.elements.pickupEmail = document.getElementById('pickup_email');
            this.elements.selectStoreBtn = document.getElementById('select-store-btn');
            this.elements.selectedStoreDisplay = document.getElementById('selected-store-display');
            this.elements.pickupProductList = document.getElementById('pickup-product-list');
            this.elements.pickupProductCount = document.getElementById('pickup-product-count');

            // Order Summary and Payment
            this.elements.shippingFeeSummary = document.getElementById('shipping-fee-summary');
            this.elements.grandTotalSummary = document.getElementById('cart-total');
            this.elements.placeOrderBtn = document.getElementById('place-order-btn');
            this.elements.paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            
            // Templates
            this.elements.shipmentBlockTemplate = document.getElementById('shipment-block-template');
            this.elements.productItemTemplate = document.getElementById('product-item-template');
        },

        bindEvents() {
            // Kiểm tra tất cả các phần tử trước khi thêm sự kiện
            if (this.elements.deliveryMethodCards) {
                this.elements.deliveryMethodCards.forEach(card => {
                    if (card) {
                        card.addEventListener('click', () => this.handleDeliveryMethodChange(card));
                    }
                });
            }
            
            // Kiểm tra từng phần tử trước khi thêm sự kiện
            if (this.elements.useNewAddressBtn) {
                this.elements.useNewAddressBtn.addEventListener('click', () => this.toggleAddressForm(true));
            }
            
            if (this.elements.backToAddressListBtn) {
                this.elements.backToAddressListBtn.addEventListener('click', () => this.toggleAddressForm(false));
            }
            
            if (this.elements.openAddressModalBtn) {
                this.elements.openAddressModalBtn.addEventListener('click', () => this.handleAddressModalOpen());
            }
            
            // Event listeners for dynamic address form
            document.addEventListener('change', (e) => {
                if (e.target && e.target.id === 'province_id') {
                    this.handleLocationChange('province', e.target.value);
                } else if (e.target && e.target.id === 'district_id') {
                    this.handleLocationChange('district', e.target.value);
                } else if (e.target && e.target.id === 'ward_id') {
                    this.handleLocationChange('ward', e.target.value);
                }
            });

            if (this.elements.selectStoreBtn) {
                this.elements.selectStoreBtn.addEventListener('click', () => this.handleStoreModalOpen());
            } else {
                console.warn('Element selectStoreBtn not found');
            }
            
            // Store modal event listeners
            document.addEventListener('change', (e) => {
                if (e.target && e.target.id === 'modal-store-province-select') {
                    this.loadModalStoreDistricts(e.target.value);
                } else if (e.target && e.target.id === 'modal-store-district-select') {
                    this.loadModalStoreLocations();
                }
            });
            
            document.addEventListener('click', (e) => {
                if (e.target && e.target.id === 'confirm-store-selection-btn') {
                    this.confirmStoreSelection();
                } else if (e.target && e.target.id === 'change-store-btn') {
                    this.handleStoreModalOpen();
                } else if (e.target && e.target.classList && e.target.classList.contains('store-item')) {
                    // Remove previous selection
                    document.querySelectorAll('.store-item').forEach(item => item.classList.remove('selected'));
                    // Add selection to clicked item
                    e.target.classList.add('selected');
                }
            });
            
            if (this.elements.placeOrderBtn) {
                this.elements.placeOrderBtn.addEventListener('click', () => this.processOrder());
            }

            if (this.elements.paymentMethodRadios) {
                this.elements.paymentMethodRadios.forEach(radio => {
                    if (radio) {
                        radio.addEventListener('change', () => this.helpers.hideError('payment_method'));
                    }
                });
            }
        },
        setupInputValidation() {
            const fieldsToValidate = {
                'full_name': { validator: this.helpers.validateName, message: 'Vui lòng nhập họ và tên' },
                'phone_number': { validator: this.helpers.validatePhone, message: 'Số điện thoại không hợp lệ' },
                'email': { validator: this.helpers.validateEmail, message: 'Email không hợp lệ' },
                'address_line1': { validator: (val) => val.trim().length > 5, message: 'Địa chỉ quá ngắn' },
                'pickup_full_name': { validator: this.helpers.validateName, message: 'Vui lòng nhập họ và tên' },
                'pickup_phone_number': { validator: this.helpers.validatePhone, message: 'Số điện thoại không hợp lệ' },
                'pickup_email': { validator: this.helpers.validateEmail, message: 'Email không hợp lệ' },
            };

            for (const fieldId in fieldsToValidate) {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', (e) => {
                        const { validator, message } = fieldsToValidate[fieldId];
                        if (!validator(e.target.value)) {
                            this.helpers.showError(fieldId, message);
                        } else {
                            this.helpers.hideError(fieldId);
                        }
                    });
                }
            }
        },

        setupInitialUI() {
            const deliveryMethodElement = document.querySelector('input[name="delivery_method"]:checked');
            const deliveryMethod = deliveryMethodElement ? deliveryMethodElement.value : 'delivery';
            const isDelivery = deliveryMethod === 'delivery';

            this.elements.deliveryAddressSection.classList.toggle('d-none', !isDelivery);
            this.elements.shipmentListSection.classList.toggle('d-none', !isDelivery);
            this.elements.pickupLocationSection.classList.toggle('d-none', isDelivery);
            
            // Chỉ ẩn pickup-summary-section khi đang ở delivery mode
            // Khi ở pickup mode, sẽ hiển thị ngay cả khi chưa chọn store (để hiển thị thông báo)
            console.log('setupInitialUI - delivery method:', deliveryMethod, 'isDelivery:', isDelivery);
            if (isDelivery) {
                this.elements.pickupSummarySection.classList.add('d-none');
                console.log('setupInitialUI - hiding pickup-summary-section (delivery mode)');
            } else {
                this.elements.pickupSummarySection.classList.remove('d-none');
                     this.elements.pickupSummarySection.style.display = 'block';
                     this.elements.pickupSummarySection.style.visibility = 'visible';
                     this.elements.pickupSummarySection.style.opacity = '1';
                     this.elements.pickupSummarySection.style.height = 'auto';
                console.log('setupInitialUI - showing pickup-summary-section (pickup mode)');
            }

            // Đã xóa phần khởi tạo ngày nhận hàng

            if (isDelivery) {
                this.renderMainProductList();
                if (this.state.isLoggedIn && this.state.hasAddresses) {
                    this.elements.addressBook.classList.remove('d-none');
                    this.elements.newAddressFormWrapper.classList.add('d-none');
                    this.renderMainAddressList();
                    // Tự động load tùy chọn vận chuyển cho địa chỉ đã chọn
                    this.getShippingOptionsForSelectedAddress();
                } else {
                    this.elements.addressBook?.classList.add('d-none');
                    this.elements.newAddressFormWrapper.classList.remove('d-none');
                    this.elements.saveAddressWrapper?.classList.toggle('d-none', !this.state.isLoggedIn);
                    this.elements.formIntroText.textContent = "Vui lòng điền đầy đủ thông tin để chúng tôi giao hàng cho bạn.";
                    this.helpers.prefillUserInfo('delivery');
                    this.helpers.loadProvinces();
                    this.renderShipments();
                }
            } else {
                this.renderPickupOrderSummary();
                this.helpers.prefillUserInfo('pickup');
                this.updateOrderInformation({ shippingFee: 0 });
            }
        },
        
        // Đã xóa hàm initializePickupDate

        handleDeliveryMethodChange(card) {
            const radio = card.querySelector('input[type="radio"]');
            if (radio.checked) return;

            radio.checked = true;
            document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            this.setupInitialUI();
            
            // Nếu chuyển sang pickup method và đã chọn store, hiển thị thông tin cửa hàng và render pickup summary
            if (radio.value === 'pickup') {
                if (this.state.selectedStore && this.state.selectedStore.id) {
                    // Hiển thị thông tin cửa hàng đã chọn
                    const storeDisplay = document.getElementById('selected-store-display');
                    if (storeDisplay) {
                        storeDisplay.style.display = 'block';
                        document.getElementById('selected-store-name').textContent = this.state.selectedStore.name;
                        document.getElementById('selected-store-address').textContent = this.state.selectedStore.address;
                        document.getElementById('selected-store-phone').textContent = this.state.selectedStore.phone || '';
                    }
                    // Cập nhật text của nút chọn cửa hàng
                    document.getElementById('store-selection-text').textContent = 'Thay đổi cửa hàng';
                    // Gọi lại API để lấy thông tin gói hàng
                    this.renderPickupOrderSummary();
                }
            }
        },

        toggleAddressForm(showForm) {
            const hasAddresses = this.state.hasAddresses;
            const isLoggedIn = this.state.isLoggedIn;
            
            if (showForm) {
                this.elements.addressBook.classList.add('d-none');
                this.elements.newAddressFormWrapper.classList.remove('d-none');
                this.elements.saveAddressWrapper?.classList.toggle('d-none', !isLoggedIn);
                
                this.elements.formIntroText.textContent = isLoggedIn && hasAddresses 
                    ? "Thông tin địa chỉ mới" 
                    : "Vui lòng điền đầy đủ thông tin để chúng tôi giao hàng cho bạn.";
                
                this.helpers.prefillUserInfo('delivery');
                this.state.selectedAddressId = null;
                this.helpers.loadProvinces();
                this.disableShippingOptions("Vui lòng nhập địa chỉ đầy đủ.");
            } else {
                if (!hasAddresses) return;
                this.elements.addressBook.classList.remove('d-none');
                this.elements.newAddressFormWrapper.classList.add('d-none');
                
                if (!this.state.selectedAddressId) {
                    const defaultAddress = this.state.addresses.find(a => a.default === true || a.default === 1);
                    if (defaultAddress) {
                        this.state.selectedAddressId = defaultAddress.id;
                    } else if (this.state.addresses.length > 0) {
                        // Nếu không có địa chỉ mặc định, chọn địa chỉ đầu tiên
                        this.state.selectedAddressId = this.state.addresses[0].id;
                    }
                }
                this.renderMainAddressList();
                this.getShippingOptionsForSelectedAddress();
            }
        },

        handleLocationChange(type, code) {
            this.helpers.hideError(type + '_id');
            
            // Update corresponding code fields
            if (type === 'province') {
                const provinceSelect = document.getElementById('province_id');
                const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
                document.getElementById('province_code').value = selectedOption ? selectedOption.value : '';
                
                this.helpers.loadDistricts(code);
                this.disableShippingOptions("Vui lòng chọn Tỉnh/Thành phố, Quận/Huyện, Phường/Xã đầy đủ.");
                
                // Reset district and ward codes
                document.getElementById('district_code').value = '';
                document.getElementById('ward_code').value = '';
            } else if (type === 'district') {
                const districtSelect = document.getElementById('district_id');
                const selectedOption = districtSelect.options[districtSelect.selectedIndex];
                document.getElementById('district_code').value = selectedOption ? selectedOption.value : '';
                
                this.helpers.loadWards(code);
                this.disableShippingOptions("Vui lòng chọn Quận/Huyện và Phường/Xã đầy đủ.");
                
                // Reset ward code
                document.getElementById('ward_code').value = '';
            } else if (type === 'ward') {
                const wardSelect = document.getElementById('ward_id');
                const selectedOption = wardSelect.options[wardSelect.selectedIndex];
                document.getElementById('ward_code').value = selectedOption ? selectedOption.value : '';
                
                if (code) {
                    this.getShippingOptionsForNewAddress();
                } else {
                    this.disableShippingOptions("Vui lòng chọn Phường/Xã.");
                }
            }
        },
        
        handleAddressModalOpen() {
            const modalEl = document.getElementById('address-modal');
            const addressModal = new bootstrap.Modal(modalEl);
            const listContainer = modalEl.querySelector('#modal-address-list');
            const confirmBtn = modalEl.querySelector('#confirm-address-selection-btn');
            
            listContainer.innerHTML = '';
            confirmBtn.disabled = true;

            this.state.addresses.forEach(addr => {
                const card = document.createElement('div');
                card.className = 'address-card';
                if (addr.id === this.state.selectedAddressId) {
                    card.classList.add('selected');
                }
                card.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="modal_address" value="${addr.id}" id="modal_addr_${addr.id}" ${addr.id === this.state.selectedAddressId ? 'checked' : ''}>
                        <label class="form-check-label w-100" for="modal_addr_${addr.id}">
                            <strong>${addr.name}</strong>
                            <div class="small text-muted">${addr.phone}</div>
                            <div class="small text-muted">${addr.full}</div>
                             ${addr.default ? '<span class="badge bg-success small mt-1">Mặc định</span>' : ''}
                        </label>
                    </div>
                `;
                card.addEventListener('click', () => {
                    card.querySelector('input').checked = true;
                    confirmBtn.disabled = false;
                    listContainer.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                });
                listContainer.appendChild(card);
            });
            
            confirmBtn.onclick = () => {
                const selectedRadio = listContainer.querySelector('input[name="modal_address"]:checked');
                if (selectedRadio) {
                    this.state.selectedAddressId = parseInt(selectedRadio.value, 10);
                    this.toggleAddressForm(false); // Switch back to address book view
                    addressModal.hide();
                }
            };

            addressModal.show();
        },
        
        handleStoreModalOpen() {
            this.loadModalStoreProvinces();
            this.loadModalStoreLocations();
            const modal = new bootstrap.Modal(document.getElementById('store-selection-modal'));
            modal.show();
        },

        renderMainAddressList() {
            const selectedAddress = this.state.addresses.find(addr => 
                addr.id == this.state.selectedAddressId || addr.id === this.state.selectedAddressId
            );
            this.elements.mainAddressList.innerHTML = '';
            if (selectedAddress) {
                const card = document.createElement('div');
                card.className = 'address-card selected';
                card.innerHTML = `
                    <div class="d-flex align-items-start">
                        <label class="ms-3 flex-grow-1 cursor-pointer">
                            <strong class="d-block text-dark">${selectedAddress.name}</strong>
                            <span class="d-block small text-muted">${selectedAddress.phone}</span>
                            <span class="d-block small text-muted">${selectedAddress.full}</span>
                            ${selectedAddress.default ? '<span class="badge bg-success small">Mặc định</span>' : ''}
                        </label>
                    </div>
                `;
                this.elements.mainAddressList.appendChild(card);
            }
        },

        async renderShipments() {
            this.elements.shipmentListSection.innerHTML = '';
            
            // Lấy thông tin địa chỉ giao hàng
            let destinationProvinceCode = null;
            if (this.state.selectedAddressId) {
                const selectedAddress = this.state.addresses.find(addr => 
                    addr.id == this.state.selectedAddressId || addr.id === this.state.selectedAddressId
                );
                if (selectedAddress) {
                    // Lấy mã tỉnh thành dựa trên hệ thống địa chỉ
                    if (selectedAddress.address_system === 'new') {
                        destinationProvinceCode = selectedAddress.new_province_code;
                    } else {
                        destinationProvinceCode = selectedAddress.old_province_code;
                    }
                }
            } else {
                const provinceSelect = document.getElementById('province_id');
                if (provinceSelect?.value) {
                    destinationProvinceCode = provinceSelect.value;
                }
            }

            if (!destinationProvinceCode) {
                this.elements.shipmentListSection.innerHTML = '<p class="text-muted">Vui lòng chọn địa chỉ giao hàng để xem các gói hàng.</p>';
                return;
            }

            try {
                // Gọi API để tính toán shipments
                const response = await fetch('/api/shipments/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        cart_items: this.state.cartItems,
                        destination_province_code: destinationProvinceCode
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.renderShipmentsFromAPI(result.shipments);
                } else {
                    this.elements.shipmentListSection.innerHTML = '<p class="text-danger">Có lỗi xảy ra khi tính toán gói hàng.</p>';
                }
            } catch (error) {
                console.error('Error calculating shipments:', error);
                this.elements.shipmentListSection.innerHTML = '<p class="text-danger">Có lỗi xảy ra khi tính toán gói hàng.</p>';
            }
        },

        renderShipmentsFromAPI(shipments) {
            let shipmentIndex = 0;
            shipments.forEach(shipment => {
                shipmentIndex++;
                const shipmentBlock = this.elements.shipmentBlockTemplate.content.cloneNode(true);
                const shipmentElement = shipmentBlock.querySelector('.shipment-block');
                const productContainer = shipmentElement.querySelector('.product-list-container');
                const shippingOptionsContainer = shipmentElement.querySelector('.shipping-options-container');

                shipmentElement.dataset.warehouseId = shipment.store_location_id;
                shipmentElement.querySelector('h3').textContent = `Gói hàng ${shipmentIndex}: Giao từ ${shipment.store_name || 'Cửa hàng'}`;
                
                // Hiển thị thông tin giao hàng dự kiến
                if (shipment.estimated_delivery_date) {
                    const deliveryInfo = document.createElement('p');
                    deliveryInfo.className = 'text-muted small mb-2';
                    deliveryInfo.textContent = `Dự kiến giao: ${shipment.estimated_delivery_date}`;
                    shipmentElement.querySelector('h3').after(deliveryInfo);
                }
                
                shipment.items.forEach(product => {
                    const productNode = this.elements.productItemTemplate.content.cloneNode(true);
                    productNode.querySelector('img').src = product.image;
                    productNode.querySelector('.product-name').textContent = product.name;
                    productNode.querySelector('.product-variant').textContent = product.variant;
                    productNode.querySelector('.product-price').textContent = this.helpers.formatCurrency(product.price);
                    productNode.querySelector('.product-quantity').textContent = `x${product.quantity}`;
                    productContainer.appendChild(productNode);
                });

                this.renderShippingOptions(shippingOptionsContainer, shipment.store_location_id, shipment.items);

                this.elements.shipmentListSection.appendChild(shipmentBlock);
            });
            this.updateOrderInformation();
        },
        
        async renderShippingOptions(container, warehouseId, products) {
            container.innerHTML = `<p class="text-muted small">Vui lòng chọn địa chỉ để tính phí.</p>`;
            let totalWeight = products.reduce((sum, item) => sum + (item.weight * item.quantity), 0);
            
            // Lấy thông tin địa chỉ giao hàng
            let destinationProvinceCode = null;
            if (this.state.selectedAddressId) {
                const selectedAddress = this.state.addresses.find(addr => 
                    addr.id == this.state.selectedAddressId || addr.id === this.state.selectedAddressId
                );
                if (selectedAddress) {
                    // Lấy mã tỉnh thành dựa trên hệ thống địa chỉ
                    if (selectedAddress.address_system === 'new') {
                        destinationProvinceCode = selectedAddress.new_province_code;
                    } else {
                        destinationProvinceCode = selectedAddress.old_province_code;
                    }
                }
            } else {
                const provinceSelect = document.getElementById('province_id');
                if (provinceSelect?.value) {
                    destinationProvinceCode = provinceSelect.value;
                }
            }

            if (!destinationProvinceCode) return;

            container.innerHTML = '<p class="text-muted small">Đang tính phí vận chuyển...</p>';
            
            try {
                // Gọi API để tính phí vận chuyển
                const response = await fetch('/api/shipments/shipping-fee', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        store_location_id: warehouseId,
                        destination_province_code: destinationProvinceCode,
                        total_weight: totalWeight
                    })
                });

                const result = await response.json();
                let html = '';
                
                if (result.success) {
                    // Hiển thị tùy chọn vận chuyển của cửa hàng
                    html += `
                        <div class="border rounded p-3 mb-2">
                            <div class="form-check">
                                <input type="radio" name="shipping_method_${warehouseId}" id="ship-store-${warehouseId}" value="store_shipper" data-fee="${result.shipping_fee}" data-name="Giao hàng của cửa hàng" class="form-check-input">
                                <label for="ship-store-${warehouseId}" class="form-check-label w-100 cursor-pointer d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">Giao hàng của cửa hàng</span>
                                    <span class="fw-medium text-success">${this.helpers.formatCurrency(result.shipping_fee)}</span>
                                </label>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="border rounded p-3 mb-2">
                            <div class="form-check disabled">
                                <input type="radio" disabled name="shipping_method_${warehouseId}" id="ship-store-${warehouseId}" value="store_shipper" data-fee="unsupported" data-name="Giao hàng của cửa hàng" class="form-check-input">
                                <label for="ship-store-${warehouseId}" class="form-check-label w-100 cursor-pointer d-flex justify-content-between align-items-center">
                                    <span class="fw-medium text-muted">Giao hàng của cửa hàng</span>
                                    <span class="fw-medium text-danger">Không hỗ trợ</span>
                                </label>
                            </div>
                        </div>
                    `;
                }
                
                container.innerHTML = html;
                
                // Thêm event listener cho các radio button
                if (container) {
                    container.querySelectorAll('input[type="radio"]').forEach(radio => {
                        if (radio) {
                            radio.addEventListener('change', () => {
                                this.updateOrderInformation();
                            });
                        }
                    });
                }
                
            } catch (error) {
                console.error('Error fetching shipping fee:', error);
                container.innerHTML = '<p class="text-danger small">Có lỗi xảy ra khi tính phí vận chuyển.</p>';
            }
        },
        
        async getShippingOptionsForSelectedAddress() {
            // Kiểm tra xem có địa chỉ được chọn không
            if (!this.state.selectedAddressId) {
                this.elements.shipmentListSection.innerHTML = '<p class="text-muted">Vui lòng chọn địa chỉ giao hàng để xem các gói hàng.</p>';
                return;
            }
            
            // Tìm địa chỉ được chọn (so sánh cả string và number)
            const selectedAddress = this.state.addresses.find(addr => 
                addr.id == this.state.selectedAddressId || addr.id === this.state.selectedAddressId
            );
            if (!selectedAddress) {
                this.elements.shipmentListSection.innerHTML = '<p class="text-muted">Địa chỉ không hợp lệ. Vui lòng chọn lại địa chỉ giao hàng.</p>';
                return;
            }
            
            // Gọi renderShipments để tính toán và hiển thị tùy chọn vận chuyển
            await this.renderShipments();
        },

        getShippingOptionsForNewAddress() {
             this.renderShipments();
        },

        disableShippingOptions(message) {
            document.querySelectorAll('.shipment-block').forEach(block => {
                const container = block.querySelector('.shipping-options-container');
                container.innerHTML = `<p class="text-muted small">${message}</p>`;
            });
             this.updateOrderInformation({ shippingFee: null });
        },

        renderMainProductList() {
            const productListContainer = document.getElementById('main-product-list');
            const productCount = document.getElementById('product-count');
            if (!productListContainer || !productCount) return;

            productListContainer.innerHTML = '';
            productCount.textContent = this.state.cartItems.length;

            this.state.cartItems.forEach(product => {
                const productDiv = document.createElement('div');
                productDiv.className = 'd-flex align-items-start gap-3 pb-3 mb-3';
                if (this.state.cartItems.indexOf(product) < this.state.cartItems.length - 1) {
                    productDiv.classList.add('border-bottom');
                }
                productDiv.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/80x80/e2e8f0/e2e8f0?text=Img';">
                    <div class="flex-grow-1 min-w-0">
                        <p class="fw-medium text-dark small mb-1" title="${product.name}">${product.name}</p>
                        <span class="badge bg-light text-dark small">
                            ${product.variant}
                        </span>
                    </div>
                    <div class="text-end">
                        <p class="fw-bold text-danger small mb-0">${this.helpers.formatCurrency(product.price)}</p>
                        <p class="text-muted small mb-1">x${product.quantity}</p>
                    </div>
                `;
                productListContainer.appendChild(productDiv);
            });
        },

        async renderPickupOrderSummary() {
            // Đảm bảo pickup-summary-section luôn hiển thị khi ở pickup mode
            if (this.elements.pickupSummarySection) {
                this.elements.pickupSummarySection.classList.remove('d-none');
                this.elements.pickupSummarySection.style.display = 'block';
            }
            
            // Kiểm tra xem đã chọn cửa hàng chưa
            if (!this.state.selectedStore || !this.state.selectedStore.id) {
                if (this.elements.pickupSummarySection) {
                    this.elements.pickupSummarySection.innerHTML = '<p class="text-muted">Vui lòng chọn cửa hàng nhận hàng để xem thông tin sản phẩm.</p>';
                }
                return;
            }
            
            // Kiểm tra xem thông tin cửa hàng đã hiển thị chưa
            const storeDisplay = document.getElementById('selected-store-display');
            if (storeDisplay && storeDisplay.style.display === 'none') {
                storeDisplay.style.display = 'block';
            }
            
            // Hiển thị thông báo đang tải
            this.elements.pickupSummarySection.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải thông tin gói hàng...</p>';

            // Đảm bảo cartItems có định dạng đúng cho API
            const formattedCartItems = this.state.cartItems.map(item => ({
                product_variant_id: item.product_variant_id || item.productVariant?.id || item.id,
                quantity: item.quantity,
                name: item.name || item.productVariant?.product?.name || '',
                image: item.image || item.productVariant?.product?.thumbnail || '',
                price: item.price || item.productVariant?.price || 0,
                variant: item.variant || ''
            }));

            try {
                console.log('Sending request with data:', {
                    cart_items: formattedCartItems,
                    pickup_store_id: this.state.selectedStore.id
                });
                
                // Tạo AbortController để có thể hủy request nếu cần
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 giây timeout
                
                // Gọi API để tính toán pickup shipments
                const response = await fetch('/api/shipments/calculate-pickup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        cart_items: formattedCartItems,
                        pickup_store_id: this.state.selectedStore.id
                    }),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('API Response:', result); // Debug log
                
                if (result.success && result.shipments && result.shipments.length > 0) {
                    console.log('Rendering shipments:', result.shipments);
                    this.renderPickupShipmentsFromAPI(result.shipments);
                } else {
                    console.error('API Error or no shipments:', result);
                    let errorMessage = result.message || 'Có lỗi xảy ra khi tính toán gói hàng.';
                    if (result.unavailable_items && result.unavailable_items.length > 0) {
                        errorMessage += '<br><small>Một số sản phẩm không có đủ hàng tại cửa hàng được chọn.</small>';
                    }
                    this.elements.pickupSummarySection.innerHTML = `<p class="text-danger">${errorMessage}</p>`;
                }
            } catch (error) {
                console.error('Error calculating pickup shipments:', error);
                let errorMessage = 'Có lỗi xảy ra khi tính toán gói hàng';
                
                if (error.name === 'AbortError') {
                    errorMessage = 'Yêu cầu bị hủy do quá thời gian chờ. Vui lòng thử lại.';
                } else if (error.message) {
                    errorMessage += `: ${error.message}`;
                }
                
                this.elements.pickupSummarySection.innerHTML = `<p class="text-danger">${errorMessage}</p>`;
            }
        },

        renderPickupShipmentsFromAPI(shipments) {
            console.log('Rendering pickup shipments:', shipments); // Debug log
            console.log('pickupProductList element:', this.elements.pickupProductList);
            console.log('pickupSummarySection element:', this.elements.pickupSummarySection);
            
            // Kiểm tra xem phần tử pickupProductList có tồn tại không
            if (!this.elements.pickupProductList) {
                console.error('Element pickupProductList not found');
                // Thử tìm lại element
                this.elements.pickupProductList = document.getElementById('pickup-product-list');
                if (!this.elements.pickupProductList) {
                    console.error('Still cannot find pickup-product-list element');
                    return;
                }
            }
            
            // Kiểm tra xem shipments có dữ liệu không
            if (!shipments || shipments.length === 0) {
                console.log('No shipments data');
                if (this.elements.pickupSummarySection) {
                    this.elements.pickupSummarySection.innerHTML = '<p class="text-danger">Không có thông tin gói hàng.</p>';
                }
                return;
            }
            
            let totalProducts = 0;
            shipments.forEach(shipment => {
                if (shipment.items && Array.isArray(shipment.items)) {
                    totalProducts += shipment.items.length;
                }                
            });
            
            // Đảm bảo phần tử pickup-product-count được cập nhật
            // Re-find the element in case it was recreated
            this.elements.pickupProductCount = document.getElementById('pickup-product-count');
            if (this.elements.pickupProductCount) {
                this.elements.pickupProductCount.textContent = totalProducts;
                console.log('Updated pickup-product-count to:', totalProducts);
            } else {
                console.warn('Element with ID "pickup-product-count" not found');
            }
            
            // Hiển thị phần pickup-summary-section
            if (this.elements.pickupSummarySection) {
                console.log('Showing pickup summary section');
                this.elements.pickupSummarySection.classList.remove('d-none');
                this.elements.pickupSummarySection.style.display = 'block'; // Đảm bảo hiển thị
                this.elements.pickupSummarySection.style.visibility = 'visible'; // Đảm bảo visible
                this.elements.pickupSummarySection.style.opacity = '1';
                this.elements.pickupSummarySection.style.height = 'auto';
            } else {
                console.warn('Element pickupSummarySection not found');
                // Thử tìm lại element
                this.elements.pickupSummarySection = document.getElementById('pickup-summary-section');
                if (!this.elements.pickupSummarySection) {
                    console.error('Still cannot find pickup-summary-section element');
                    return;
                }
                this.elements.pickupSummarySection.classList.remove('d-none');
                this.elements.pickupSummarySection.style.display = 'block';
                this.elements.pickupSummarySection.style.visibility = 'visible';
            }
            
            // Clear loading message from pickup-summary-section and restore proper structure
            if (this.elements.pickupSummarySection && this.elements.pickupSummarySection.innerHTML.includes('Đang tải')) {
                console.log('Clearing loading message from pickup-summary-section and restoring structure');
                this.elements.pickupSummarySection.innerHTML = `
                    <h3 class="h6 mb-3">Sản phẩm sẽ nhận (<span id="pickup-product-count">0</span>)</h3>
                    <div id="pickup-product-list" class="d-flex flex-column gap-3"></div>
                `;
                // Re-initialize the pickup product list element
                this.elements.pickupProductList = document.getElementById('pickup-product-list');
                this.elements.pickupProductCount = document.getElementById('pickup-product-count');
            }
            
            // Xóa nội dung cũ của pickup-product-list
            // Re-find the element in case it was recreated
            this.elements.pickupProductList = document.getElementById('pickup-product-list');
            if (this.elements.pickupProductList) {
                this.elements.pickupProductList.innerHTML = '';
                console.log('Cleared pickup-product-list content');
            } else {
                console.warn('pickup-product-list element not found after structure restore');
            }
            
            console.log('pickup-summary-section final state:', {
                element: this.elements.pickupSummarySection,
                classList: this.elements.pickupSummarySection?.classList.toString(),
                display: this.elements.pickupSummarySection?.style.display,
                visibility: this.elements.pickupSummarySection?.style.visibility,
                innerHTML: this.elements.pickupSummarySection?.innerHTML.substring(0, 100) + '...'
            });

            shipments.forEach((shipment, index) => {
                const shipmentDiv = document.createElement('div');
                shipmentDiv.className = 'border rounded p-3 mb-3';
                
                let statusText = '';
                let statusClass = '';
                if (shipment.requires_transfer) {
                    statusText = `Sẽ chuyển từ ${shipment.source_name || 'kho hàng'} về ${shipment.pickup_store_name || 'cửa hàng đã chọn'}`;
                    statusClass = 'text-warning';
                } else {
                    statusText = `Có sẵn tại ${shipment.pickup_store_name || 'cửa hàng đã chọn'}`;
                    statusClass = 'text-success';
                }

                shipmentDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Gói hàng ${index + 1}</h6>
                        <small class="${statusClass}">${statusText}</small>
                    </div>
                    ${shipment.requires_transfer ? `
                        <div class="small text-muted mb-2">
                            <i class="fas fa-clock me-1"></i>
                            Dự kiến sẵn sàng: ${shipment.estimated_ready_date || 'đang cập nhật'}
                            (${shipment.transit_days_min || '1'}-${shipment.transit_days_max || '3'} ngày)
                        </div>
                    ` : ''}
                    <div class="product-list"></div>
                `;

                const productList = shipmentDiv.querySelector('.product-list');
                
                // Kiểm tra xem shipment có items không
                if (!shipment.items || !Array.isArray(shipment.items) || shipment.items.length === 0) {
                    const noItemsDiv = document.createElement('div');
                    noItemsDiv.className = 'text-muted py-2';
                    noItemsDiv.textContent = 'Không có thông tin sản phẩm';
                    productList.appendChild(noItemsDiv);
                } else {
                    // Tìm thông tin sản phẩm từ cartItems nếu cần
                    shipment.items.forEach(item => {
                        // Tìm thông tin đầy đủ của sản phẩm từ cartItems nếu thiếu
                        let productInfo = item;
                        if (!item.name || !item.image || !item.price) {
                            console.log('Tìm thông tin sản phẩm từ cartItems:', item.product_variant_id);
                            if (this.state && this.state.cartItems && Array.isArray(this.state.cartItems)) {
                                const cartItem = this.state.cartItems.find(cartItem => 
                                    cartItem.product_variant_id == item.product_variant_id);
                                if (cartItem) {
                                    console.log('Đã tìm thấy thông tin sản phẩm:', cartItem);
                                    productInfo = {
                                        ...item,
                                        name: item.name || cartItem.name || 'Sản phẩm không xác định',
                                        image: item.image || cartItem.image || '/images/no-image.png',
                                        price: item.price || cartItem.price || 0,
                                        variant: item.variant || cartItem.variant || ''
                                    };
                                } else {
                                    console.warn('Không tìm thấy thông tin sản phẩm trong cartItems');
                                }
                            } else {
                                console.error('state.cartItems không tồn tại hoặc không phải là mảng');
                            }
                        }
                        
                        try {
                            const productDiv = document.createElement('div');
                            productDiv.className = 'd-flex align-items-center gap-3 py-2 border-bottom';
                            
                            // Đảm bảo các giá trị mặc định nếu không có thông tin
                            const productName = productInfo.name || 'Sản phẩm không xác định';
                            const productImage = productInfo.image || '/images/no-image.png';
                            const productVariant = productInfo.variant || '';
                            const productPrice = productInfo.price || 0;
                            const productQuantity = productInfo.quantity || 1;
                            
                            console.log('Hiển thị sản phẩm:', {
                                name: productName,
                                image: productImage,
                                variant: productVariant,
                                price: productPrice,
                                quantity: productQuantity
                            });
                            
                            productDiv.innerHTML = `
                                <img src="${productImage}" alt="${productName}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-medium">${productName}</div>
                                    <div class="small text-muted">${productVariant}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium">${this.helpers.formatCurrency(productPrice)}</div>
                                    <div class="small text-muted">x${productQuantity}</div>
                                </div>
                            `;
                            productList.appendChild(productDiv);
                        } catch (error) {
                            console.error('Lỗi khi tạo phần tử sản phẩm:', error);
                        }
                    });
                }

                this.elements.pickupProductList.appendChild(shipmentDiv);
            });
            
            // Log trạng thái cuối cùng sau khi render xong
            console.log('Render completed. Final pickup-summary-section state:', {
                element: this.elements.pickupSummarySection,
                classList: this.elements.pickupSummarySection?.classList.toString(),
                display: this.elements.pickupSummarySection?.style.display,
                visibility: this.elements.pickupSummarySection?.style.visibility,
                offsetHeight: this.elements.pickupSummarySection?.offsetHeight,
                offsetWidth: this.elements.pickupSummarySection?.offsetWidth
            });
            
            // Force reflow để đảm bảo DOM được cập nhật
            if (this.elements.pickupSummarySection) {
                this.elements.pickupSummarySection.offsetHeight;
            }
            
            // Đảm bảo hiển thị sau một khoảng thời gian ngắn để tránh bị CSS override
            setTimeout(() => {
                if (this.elements.pickupSummarySection) {
                    this.elements.pickupSummarySection.classList.remove('d-none');
                    this.elements.pickupSummarySection.style.display = 'block';
                    this.elements.pickupSummarySection.style.visibility = 'visible';
                    console.log('Force showing pickup-summary-section after timeout');
                }
            }, 100);
        },

        updateOrderInformation(options = {}) {
            let totalShippingFee = 0;
            let canCalculate = true;
            const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;

            if (deliveryMethod === 'delivery') {
                document.querySelectorAll('.shipment-block').forEach(block => {
                    const selectedRadio = block.querySelector('input[name^="shipping_method_"]:checked');
                    if (selectedRadio) {
                        const fee = parseInt(selectedRadio.dataset.fee, 10);
                        if (!isNaN(fee) && selectedRadio.dataset.fee !== 'unsupported') {
                            totalShippingFee += fee;
                        } else {
                            canCalculate = false;
                        }
                    } else {
                        canCalculate = false;
                    }
                });
            } else { // pickup
                totalShippingFee = 0;
            }

            this.elements.shippingFeeSummary.textContent = canCalculate ? this.helpers.formatCurrency(totalShippingFee) : 'Chưa xác định';
            
            const finalTotal = this.state.subtotal - this.state.discount - this.state.pointsDiscount + totalShippingFee;
            this.elements.grandTotalSummary.textContent = this.helpers.formatCurrency(finalTotal > 0 ? finalTotal : 0);
        },

        processOrder() {
            this.elements.placeOrderBtn.disabled = true;
            this.elements.placeOrderBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...`;
            
            const deliveryMethodElement = document.querySelector('input[name="delivery_method"]:checked');
            const paymentMethodElement = document.querySelector('input[name="payment_method"]:checked');
            
            let orderData = {
                _token: this.state.csrfToken,
                delivery_method: deliveryMethodElement ? deliveryMethodElement.value : null,
                payment_method: paymentMethodElement ? paymentMethodElement.value : null,
            };

            let isValid = this.validateBeforeSubmit(orderData);
            if (!isValid) {
                this.helpers.resetOrderButton();
                return;
            }

            if (orderData.delivery_method === 'delivery') {
                if (this.state.selectedAddressId) {
                    orderData.address_id = this.state.selectedAddressId;
                } else {
                    const formFields = new FormData(this.elements.addressForm);
                    for (let [key, value] of formFields.entries()) {
                        orderData[key] = value;
                    }
                    orderData.save_address = document.getElementById('save-address-check')?.checked || false;
                }
                
                orderData.shipments = [];
                let allValidShipments = true;
                document.querySelectorAll('.shipment-block').forEach(block => {
                    const selectedRadio = block.querySelector('input[name^="shipping_method_"]:checked');
                    if (!selectedRadio || selectedRadio.dataset.fee === 'unsupported') {
                        allValidShipments = false;
                        return;
                    }
                    const shipmentData = {
                        store_location_id: block.dataset.warehouseId,
                        shipping_method: selectedRadio.dataset.name,
                        shipping_fee: parseInt(selectedRadio.dataset.fee, 10),
                    };
                    const deliveryDateInput = block.querySelector('input[name="delivery_date"]');
                    const deliveryTimeSlotSelect = block.querySelector('select[name="delivery_time_slot"]');
                    if (deliveryDateInput) shipmentData.delivery_date = deliveryDateInput.value;
                    if (deliveryTimeSlotSelect) shipmentData.delivery_time_slot = deliveryTimeSlotSelect.value;
                    orderData.shipments.push(shipmentData);
                });

                if (!allValidShipments) {
                    this.helpers.showError('shipping_method', 'Vui lòng chọn phương thức vận chuyển hợp lệ cho tất cả gói hàng.');
                    this.helpers.resetOrderButton();
                    return;
                }
                
            } else { // pickup
                orderData.pickup_full_name = this.elements.pickupFullName?.value.trim();
                orderData.pickup_phone_number = this.elements.pickupPhoneNumber?.value.trim();
                orderData.pickup_email = this.elements.pickupEmail?.value.trim();
                orderData.store_location_id = this.state.selectedStore?.id;
                // Đã xóa tham chiếu đến pickup_date
                orderData.pickup_time_slot = document.getElementById('pickup-time-slot')?.value;
            }

            fetch(this.state.urls.process, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.state.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(orderData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else if (data.order && data.order.id) {
                        window.location.href = `{{ route('payments.success') }}?order_id=${data.order.id}`;
                    } else {
                        window.location.href = `{{ route('payments.success') }}`;
                    }
                } else {
                    this.helpers.showAlert(data.message || 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.', 'danger');
                    if (data.errors) {
                        this.helpers.handleBackendValidationErrors(data.errors);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                this.helpers.showAlert('Đã có lỗi hệ thống xảy ra. Vui lòng thử lại.', 'danger');
            })
            .finally(() => {
                this.helpers.resetOrderButton();
            });
        },
        
        validateBeforeSubmit(orderData) {
            this.helpers.hideAllErrors();
            let isValid = true;
            
            if (!orderData.delivery_method) {
                this.helpers.showError('delivery_method', 'Vui lòng chọn phương thức nhận hàng');
                isValid = false;
            }

            if (!orderData.payment_method) {
                this.helpers.showError('payment_method', 'Vui lòng chọn phương thức thanh toán');
                isValid = false;
            }

            if (orderData.delivery_method === 'delivery') {
                if (!this.state.selectedAddressId) {
                    const name = this.elements.addressForm.querySelector('#full_name');
                    if (!name.value) { this.helpers.showError('full_name', 'Vui lòng nhập họ và tên'); isValid = false; }
                    const phone = this.elements.addressForm.querySelector('#phone_number');
                    if (!phone.value) { this.helpers.showError('phone_number', 'Vui lòng nhập số điện thoại'); isValid = false; }
                    const email = this.elements.addressForm.querySelector('#email');
                    if (!email.value) { this.helpers.showError('email', 'Vui lòng nhập email'); isValid = false; }
                    const province = this.elements.addressForm.querySelector('#province_id');
                    if (!province.value) { this.helpers.showError('province_id', 'Vui lòng chọn Tỉnh/Thành phố'); isValid = false; }
                    const district = this.elements.addressForm.querySelector('#district_id');
                    if (!district.value) { this.helpers.showError('district_id', 'Vui lòng chọn Quận/Huyện'); isValid = false; }
                    const ward = this.elements.addressForm.querySelector('#ward_id');
                    if (!ward.value) { this.helpers.showError('ward_id', 'Vui lòng chọn Phường/Xã'); isValid = false; }
                    const addressLine = this.elements.addressForm.querySelector('#address_line1');
                    if (!addressLine.value) { this.helpers.showError('address_line1', 'Vui lòng nhập số nhà, tên đường'); isValid = false; }
                }
                
                let allShipmentsSelected = true;
                document.querySelectorAll('.shipment-block').forEach(block => {
                    const selectedRadio = block.querySelector('input[name^="shipping_method_"]:checked');
                    if (!selectedRadio || selectedRadio.dataset.fee === 'unsupported') {
                        allShipmentsSelected = false;
                    }
                });
                if (!allShipmentsSelected) {
                    this.helpers.showError('shipping_method', 'Vui lòng chọn phương thức vận chuyển cho tất cả gói hàng.');
                    isValid = false;
                }

            } else {
                if (!this.elements.pickupFullName.value) { this.helpers.showError('pickup_full_name', 'Vui lòng nhập họ và tên'); isValid = false; }
                if (!this.elements.pickupPhoneNumber.value) { this.helpers.showError('pickup_phone_number', 'Vui lòng nhập số điện thoại'); isValid = false; }
                if (!this.elements.pickupEmail.value) { this.helpers.showError('pickup_email', 'Vui lòng nhập email'); isValid = false; }
                if (!this.state.selectedStore) { this.helpers.showError('store_location_error', 'Vui lòng chọn cửa hàng'); isValid = false; }
                // Đã xóa phần kiểm tra pickup-date
            }
            return isValid;
        },

        helpers: {
            formatCurrency(value) {
                if (value === 0) return 'Miễn phí';
                if (value === null || isNaN(value)) return 'Chưa xác định';
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
            },
            
            resetOrderButton() {
                document.getElementById('place-order-btn').disabled = false;
                document.getElementById('place-order-btn').innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Đặt hàng';
            },

            showError(field, message) {
                const fieldElement = document.getElementById(field);
                const errorElement = document.getElementById(field + '_error');
                if (fieldElement) fieldElement.classList.add('is-invalid');
                if (errorElement) {
                    errorElement.querySelector('span').textContent = message;
                    errorElement.style.display = 'flex';
                }
            },
            
            hideError(field) {
                const fieldElement = document.getElementById(field);
                const errorElement = document.getElementById(field + '_error');
                if (fieldElement) fieldElement.classList.remove('is-invalid');
                if (errorElement) errorElement.style.display = 'none';
            },
            
            hideAllErrors() {
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            },

            loadProvinces: async () => {
                const select = document.getElementById('province_id');
                if (!select) return;
                try {
                    const response = await fetch(window.checkoutConfig.urls.provinces);
                    const data = await response.json();
                    if (data.success) {
                        select.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
                        data.data.forEach(p => {
                            select.innerHTML += `<option value="${p.code}" data-name="${p.name}">${p.name_with_type}</option>`;
                        });
                        select.disabled = false;
                    }
                } catch (e) {
                    console.error("Error loading provinces", e);
                }
            },
            loadDistricts: async (provinceCode) => {
                const select = document.getElementById('district_id');
                const wardSelect = document.getElementById('ward_id');
                if (!select) return;
                
                select.innerHTML = '<option value="">Đang tải...</option>';
                select.disabled = true;
                if (wardSelect) {
                    wardSelect.innerHTML = '<option value="">Chọn Quận/Huyện trước</option>';
                    wardSelect.disabled = true;
                }
                
                if (!provinceCode) {
                    select.innerHTML = '<option value="">Chọn Tỉnh/Thành phố trước</option>';
                    return;
                }
                try {
                    const response = await fetch(`${window.checkoutConfig.urls.districts}/${provinceCode}`);
                    const data = await response.json();
                    if (data.success) {
                        select.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                        data.data.forEach(d => {
                            select.innerHTML += `<option value="${d.code}" data-name="${d.name}">${d.name_with_type}</option>`;
                        });
                        select.disabled = false;
                    } else {
                        select.innerHTML = '<option value="">Không có dữ liệu</option>';
                    }
                } catch (e) {
                    console.error("Error loading districts", e);
                    select.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                }
            },
            loadWards: async (districtCode) => {
                const select = document.getElementById('ward_id');
                if (!select) return;
                select.innerHTML = '<option value="">Đang tải...</option>';
                select.disabled = true;
                if (!districtCode) {
                    select.innerHTML = '<option value="">Chọn Quận/Huyện trước</option>';
                    return;
                }
                try {
                    const response = await fetch(`${window.checkoutConfig.urls.wards}/${districtCode}`);
                    const data = await response.json();
                    if (data.success) {
                        select.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                        data.data.forEach(w => {
                            select.innerHTML += `<option value="${w.code}" data-name="${w.name}">${w.name_with_type}</option>`;
                        });
                        select.disabled = false;
                    } else {
                         select.innerHTML = '<option value="">Không có dữ liệu</option>';
                    }
                } catch (e) {
                    console.error("Error loading wards", e);
                    select.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                }
            },

            fetchGhnFee: async (provinceName, districtName, wardName, totalWeight) => {
                const cacheKey = `${provinceName}|${districtName}|${wardName}|${totalWeight}`;
                if (CheckoutPage.ghnFeeCache[cacheKey]) return CheckoutPage.ghnFeeCache[cacheKey];
                
                try {
                    const res = await fetch(window.checkoutConfig.urls.ghnFee, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.checkoutConfig.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ province_name: provinceName, district_name: districtName, ward_name: wardName, weight: totalWeight })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const result = { success: true, fee: data.fee };
                        CheckoutPage.ghnFeeCache[cacheKey] = result;
                        return result;
                    } else {
                        const result = { success: false, message: data.message };
                        CheckoutPage.ghnFeeCache[cacheKey] = result;
                        return result;
                    }
                } catch (e) {
                    console.error('Error fetching GHN fee', e);
                    const result = { success: false, message: 'Lỗi khi tính phí giao hàng.' };
                    CheckoutPage.ghnFeeCache[cacheKey] = result;
                    return result;
                }
            },

            prefillUserInfo: (type) => {
                if (!window.checkoutConfig.isLoggedIn || !window.checkoutConfig.user) return;
                const user = window.checkoutConfig.user;
                if (type === 'delivery') {
                    if (document.getElementById('full_name')) document.getElementById('full_name').value = user.name || '';
                    if (document.getElementById('phone_number')) document.getElementById('phone_number').value = user.phone || '';
                    if (document.getElementById('email')) document.getElementById('email').value = user.email || '';
                } else if (type === 'pickup') {
                    if (document.getElementById('pickup_full_name')) document.getElementById('pickup_full_name').value = user.name || '';
                    if (document.getElementById('pickup_phone_number')) document.getElementById('pickup_phone_number').value = user.phone || '';
                    if (document.getElementById('pickup_email')) document.getElementById('pickup_email').value = user.email || '';
                }
            },

            handleBackendValidationErrors(errors) {
                this.hideAllErrors();
                let firstErrorField = null;
                Object.keys(errors).forEach(fieldName => {
                    const errorMessage = errors[fieldName][0];
                    if (fieldName === 'shipping_method') {
                        this.showError('shipping_method', errorMessage);
                        if (!firstErrorField) firstErrorField = document.getElementById('shipping_method_error');
                    } else if (fieldName === 'payment_method') {
                        this.showError('payment_method', errorMessage);
                        if (!firstErrorField) firstErrorField = document.getElementById('payment_method_error');
                    } else if (fieldName === 'store_location_id') {
                        this.showError('store_location_error', errorMessage);
                        if (!firstErrorField) firstErrorField = document.getElementById('store_location_error');
                    } else {
                        this.showError(fieldName, errorMessage);
                        if (!firstErrorField) firstErrorField = document.getElementById(fieldName);
                    }
                });
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            },
            
            showAlert: (message, type) => {
                const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                document.querySelector('.container').insertAdjacentHTML('afterbegin', alertHtml);
            }
        },

        // Store selection methods
        loadModalStoreProvinces: async function() {
            const modalStoreProvinceSelect = document.getElementById('modal-store-province-select');
            if (!modalStoreProvinceSelect) return;
            
            try {
                const response = await fetch('/api/store-locations/provinces');
                const data = await response.json();

                if (data.success) {
                    modalStoreProvinceSelect.innerHTML = '<option value="">Tất cả Tỉnh/Thành phố</option>';
                    data.data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        modalStoreProvinceSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading store provinces:', error);
            }
        },

        loadModalStoreDistricts: async function(provinceCode) {
            const modalStoreDistrictSelect = document.getElementById('modal-store-district-select');
            if (!modalStoreDistrictSelect) return;
            
            try {
                modalStoreDistrictSelect.innerHTML = '<option value="">Đang tải...</option>';
                modalStoreDistrictSelect.disabled = true;

                if (!provinceCode) {
                    modalStoreDistrictSelect.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    modalStoreDistrictSelect.disabled = false;
                    return;
                }

                const response = await fetch(`/api/store-locations/districts?province_code=${provinceCode}`);
                const data = await response.json();

                if (data.success) {
                    modalStoreDistrictSelect.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    data.data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.code;
                        option.textContent = district.name;
                        modalStoreDistrictSelect.appendChild(option);
                    });
                    modalStoreDistrictSelect.disabled = false;
                } else {
                    modalStoreDistrictSelect.innerHTML = '<option value="">Không có dữ liệu</option>';
                    modalStoreDistrictSelect.disabled = true;
                }
            } catch (error) {
                console.error('Error loading store districts:', error);
                modalStoreDistrictSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                modalStoreDistrictSelect.disabled = true;
            }
        },

        loadModalStoreLocations: async function(provinceCode = '', districtCode = '') {
            const modalStoreList = document.getElementById('modal-store-list');
            const storeCountText = document.getElementById('store-count-text');
            if (!modalStoreList) return;
            
            try {
                modalStoreList.innerHTML = '<p class="text-muted small text-center">Đang tải danh sách cửa hàng...</p>';

                const params = new URLSearchParams();
                if (provinceCode) params.append('province_code', provinceCode);
                if (districtCode) params.append('district_code', districtCode);
                
                // Lấy danh sách sản phẩm từ giỏ hàng
                const productVariantIds = this.getProductVariantIds();
                if (productVariantIds.length > 0) {
                    productVariantIds.forEach(id => {
                        params.append('product_variant_ids[]', id);
                    });
                }
                
                const response = await fetch(`/api/store-locations/stores?${params.toString()}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Store locations response:', data);

                if (data.success && data.data.length > 0) {
                    storeCountText.textContent = `Có ${data.data.length} cửa hàng`;

                    let html = '';
                    data.data.forEach(store => {
                        html += `
                            <div class="border rounded p-3 mb-2 store-item" data-store-id="${store.id}">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modal_store_location" value="${store.id}" id="modal_store_${store.id}">
                                    <label class="form-check-label w-100" for="modal_store_${store.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong class="d-block">${store.name}</strong>
                                                <span class="d-block small text-muted">${store.address}</span>
                                                ${store.phone ? `<div class="small text-muted mt-1"><i class="fas fa-phone me-1"></i>${store.phone}</div>` : ''}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    modalStoreList.innerHTML = html;
                    
                    // Thêm event listeners cho radio buttons
                    if (modalStoreList) {
                        const radioButtons = modalStoreList.querySelectorAll('input[type="radio"]');
                        if (radioButtons && radioButtons.length > 0) {
                            radioButtons.forEach(radio => {
                                if (radio) {
                                    radio.addEventListener('change', () => {
                                        const confirmBtn = document.getElementById('confirm-store-selection-btn');
                                        if (confirmBtn) {
                                            confirmBtn.disabled = false;
                                        }
                                
                                        // Cập nhật visual state cho các items
                                        const storeItems = document.querySelectorAll('.store-item');
                                        if (storeItems && storeItems.length > 0) {
                                            storeItems.forEach(item => {
                                                if (item) {
                                                    item.classList.remove('border-danger', 'bg-light');
                                                }
                                            });
                                        }

                                        const selectedItem = radio.closest('.store-item');
                                        if (selectedItem) {
                                            selectedItem.classList.add('border-danger', 'bg-light');
                                        }
                                    });
                                }
                            });
                        }
                    }
                } else {
                    console.log('No stores found or API error:', data);
                    if (storeCountText) {
                        storeCountText.textContent = 'Có 0 cửa hàng';
                    }
                    modalStoreList.innerHTML = '<div class="text-center py-4"><div class="text-warning mb-2"><i class="fas fa-store-slash"></i></div><p class="text-muted small mb-2">Không có cửa hàng nào trong khu vực này</p><p class="text-muted small">Vui lòng thử chọn tỉnh/thành phố khác</p></div>';
                }
            } catch (error) {
                console.error('Error loading store locations:', error);
                if (storeCountText) {
                    storeCountText.textContent = 'Có 0 cửa hàng';
                }
                modalStoreList.innerHTML = '<div class="text-center py-4"><div class="text-danger mb-2"><i class="fas fa-exclamation-triangle"></i></div><p class="text-danger small mb-2">Không thể tải danh sách cửa hàng</p><p class="text-muted small">Vui lòng thử lại sau hoặc liên hệ hỗ trợ</p></div>';
            }
        },

        getProductVariantIds: function() {
            const productVariantIds = [];
            // Kiểm tra xem có phải là buy now session không
            const isBuyNow = {{ isset($is_buy_now) && $is_buy_now ? 'true' : 'false' }};
            if (isBuyNow) {
                // Lấy từ buy now session
                const buyNowSession = @json(session('buy_now_session', null));
                if (buyNowSession && buyNowSession.variant_id) {
                    productVariantIds.push(buyNowSession.variant_id);
                }
            } else {
                // Lấy từ giỏ hàng
                const cartItems = @json($items ?? []);
                if (Array.isArray(cartItems)) {
                    cartItems.forEach(item => {
                        if (item.productVariant && item.productVariant.id) {
                            productVariantIds.push(item.productVariant.id);
                        } else if (item.id) {
                            productVariantIds.push(item.id);
                        }
                    });
                } else {
                    console.warn('cartItems không phải là mảng:', cartItems);
                }
            }
            return productVariantIds;
        },

        confirmStoreSelection: function() {
            const selectedRadio = document.querySelector('input[name="modal_store_location"]:checked');
            if (selectedRadio) {
                const storeId = selectedRadio.value;
                const storeItem = selectedRadio.closest('.store-item');
                const storeName = storeItem.querySelector('strong').textContent;
                const storeAddress = storeItem.querySelector('span').textContent;
                const storePhone = storeItem.querySelector('.fa-phone')?.parentElement?.textContent.trim() || '';

                // Lưu thông tin cửa hàng đã chọn
                this.state.selectedStore = {
                    id: storeId,
                    name: storeName,
                    address: storeAddress,
                    phone: storePhone
                };

                // Tự động chuyển sang chế độ pickup khi chọn cửa hàng
                const pickupRadio = document.getElementById('delivery_method_radio_pickup');
                if (pickupRadio && !pickupRadio.checked) {
                    pickupRadio.checked = true;
                    // Cập nhật giao diện các option cards
                    document.querySelectorAll('.option-card').forEach(card => card.classList.remove('selected'));
                    const pickupCard = document.getElementById('delivery-method-pickup');
                    if (pickupCard) {
                        pickupCard.classList.add('selected');
                    }
                    // Cập nhật UI sections
                    this.setupInitialUI();
                }

                // Hiển thị cửa hàng đã chọn
                document.getElementById('selected-store-name').textContent = storeName;
                document.getElementById('selected-store-address').textContent = storeAddress;
                document.getElementById('selected-store-phone').textContent = storePhone;

                // Ẩn button chọn và hiển thị thông tin cửa hàng
                const storeDisplay = document.getElementById('selected-store-display');
                if (storeDisplay) {
                    storeDisplay.style.display = 'block';
                    // Cập nhật text của nút chọn cửa hàng
                    document.getElementById('store-selection-text').textContent = 'Thay đổi cửa hàng';
                }
                
                // Hiển thị phần pickup-summary-section
                if (this.elements.pickupSummarySection) {
                    this.elements.pickupSummarySection.classList.remove('d-none');
                    this.elements.pickupSummarySection.style.display = 'block';
                }

                // Ẩn lỗi nếu có
                const storeError = document.getElementById('store_location_error');
                if (storeError) {
                    storeError.style.display = 'none';
                }

                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('store-selection-modal'));
                modal.hide();

                // Cập nhật thông tin đơn hàng và render lại pickup summary
                this.updateOrderInformation();
                this.renderPickupOrderSummary();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.checkoutConfig) {
            CheckoutPage.init(window.checkoutConfig);
        } else {
            console.error('Checkout configuration not found');
        }
    });
</script>
@endpush
