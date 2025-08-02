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

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items-center;
            gap: 0.25rem;
        }

        .error-message i {
            font-size: 1rem;
            color: #dc3545;
        }

        .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-select.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .form-select.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .disabled-section {
            opacity: 0.5;
            pointer-events: none;
        }

        .modal-overlay {
            transition: opacity 0.3s ease;
        }

        .modal-content {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .step-hidden {
            display: none !important;
        }

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

        .order-summary-sticky {
            position: sticky;
            top: 2rem;
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
            z-index: 10;
            padding-bottom: 1rem;
        }

        @media (max-width: 991.98px) {
            .order-summary-sticky {
                position: static;
                max-height: none;
                overflow-y: visible;
            }
        }

        .order-summary-sticky .bg-white {
            border: 1px solid #e5e7eb;
        }

        .order-summary-sticky button {
            position: relative;
            z-index: 1;
        }

        .order-summary-sticky::-webkit-scrollbar {
            width: 6px;
        }

        .order-summary-sticky::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }

        .order-summary-sticky::-webkit-scrollbar-thumb {
            background: #dc3545;
            border-radius: 3px;
        }

        .order-summary-sticky::-webkit-scrollbar-thumb:hover {
            background: #c82333;
        }

        .order-summary-sticky .bg-white {
            min-height: auto;
            max-height: none;
        }

        .order-summary-sticky>.bg-white:first-child {
            margin-top: 0;
            padding-top: 1rem;
        }

        .store-location-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .store-location-card:hover {
            border-color: #dc3545;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .store-location-card input[type="radio"]:checked+label {
            color: #dc3545;
        }

        .store-location-card input[type="radio"]:checked~.store-location-card {
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

        .store-item input[type="radio"]:checked+label {
            color: #dc3545;
        }

        .store-item.border-danger {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
        }

        #store-selection-modal .modal-dialog {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        #store-selection-modal .modal-content {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .store-modal-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #dee2e6;
        }

        .store-modal-header .modal-title {
            color: #212529 !important;
        }

        .address-modal-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #dee2e6;
        }

        .address-modal-header .modal-title {
            color: #212529 !important;
        }
    </style>
    <!-- Page content -->
    <main class="content-wrapper" style="min-height: 100vh;">
        <div class="container py-5">
            <div class="row pt-1 pt-sm-3 pt-lg-4 pb-2 pb-md-3 pb-lg-4 pb-xl-5">
                <!-- Main Content -->
                <div class="col-lg-8 col-xl-7 mb-5 mb-lg-0" style="min-height: 100vh; padding-bottom: 50px;">
                    <div class="d-flex flex-column gap-5 pe-lg-4 pe-xl-0">

                        <!-- Product List Section -->
                        <div class="bg-white rounded shadow-sm p-4">
                            <h2 class="h5 mb-4">Sản phẩm trong đơn (<span id="product-count">0</span>)</h2>
                            <div id="main-product-list">
                                <!-- Products will be dynamically inserted here -->
                            </div>
                        </div>

                        <!-- Shipping Details Section -->
                        <div class="bg-white rounded shadow-sm p-4">
                            <!-- Delivery Method Choice -->
                            <div class="mb-4">
                                <h3 class="h5 mb-3">Phương thức nhận hàng</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="option-card rounded p-3" id="delivery-method-delivery">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_method"
                                                    value="delivery" id="delivery_method_radio_delivery" checked>
                                                <label class="form-check-label fw-medium"
                                                    for="delivery_method_radio_delivery">
                                                    Giao hàng tận nơi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="option-card rounded p-3" id="delivery-method-pickup">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_method"
                                                    value="pickup" id="delivery_method_radio_pickup">
                                                <label class="form-check-label fw-medium"
                                                    for="delivery_method_radio_pickup">
                                                    Nhận tại cửa hàng
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Address Section -->
                            <div id="delivery-address-section">
                                <hr class="my-4">

                                <!-- Address Book (for logged-in users with addresses) -->
                                <div id="address-book" class="step-hidden">
                                    <h4 class="h6 mb-3">Sổ địa chỉ của bạn</h4>
                                    <div id="main-address-list" class="mb-3">
                                        <!-- Top addresses will be rendered here by JS -->
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" id="open-address-modal-btn"
                                            class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-exchange-alt me-1"></i>Thay đổi địa chỉ
                                        </button>
                                        <button type="button" id="use-new-address-btn"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-plus me-1"></i>Thêm địa chỉ mới
                                        </button>
                                    </div>
                                </div>

                                <!-- New Address Form (for guests or new address) -->
                                <div id="new-address-form-wrapper" class="step-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p id="form-intro-text" class="text-muted small mb-0"></p>
                                        <button type="button" id="back-to-address-book-btn"
                                            class="btn btn-outline-secondary btn-sm step-hidden">
                                            <i class="fas fa-arrow-left me-1"></i>Quay lại sổ địa chỉ
                                        </button>
                                    </div>
                                    <form id="address-form" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="full_name" class="form-label">Họ & tên <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="full_name" name="full_name" class="form-control"
                                                required>
                                            <div id="full_name_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng nhập tên</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label">Số điện thoại <span
                                                    class="text-danger">*</span></label>
                                            <input type="tel" id="phone_number" name="phone_number" class="form-control"
                                                required>
                                            <div id="phone_number_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng nhập số điện thoại</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="email" class="form-label">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                required>
                                            <div id="email_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng nhập email hợp lệ</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="province_id" class="form-label">Tỉnh/Thành phố <span
                                                    class="text-danger">*</span></label>
                                            <select id="province_id" name="province_id" class="form-select" required>
                                                <option value="">Chọn Tỉnh/Thành phố</option>
                                            </select>
                                            <div id="province_id_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng chọn Tỉnh/Thành phố</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="district_id" class="form-label">Quận/Huyện <span
                                                    class="text-danger">*</span></label>
                                            <select id="district_id" name="district_id" class="form-select" required
                                                disabled>
                                                <option value="">Chọn Quận/Huyện</option>
                                            </select>
                                            <div id="district_id_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng chọn Quận/Huyện</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ward_id" class="form-label">Phường/Xã <span
                                                    class="text-danger">*</span></label>
                                            <select id="ward_id" name="ward_id" class="form-select" required disabled>
                                                <option value="">Chọn Phường/Xã</option>
                                            </select>
                                            <div id="ward_id_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng chọn Phường/Xã</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="address_line1" class="form-label">Số nhà, Tên đường <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="address_line1" name="address_line1"
                                                class="form-control" required>
                                            <div id="address_line1_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng nhập số nhà, tên đường</span>
                                            </div>
                                        </div>
                                        <div id="save-address-wrapper" class="col-12 step-hidden">
                                            <div class="form-check">
                                                <input id="save-address-check" name="save_address" type="checkbox"
                                                    class="form-check-input">
                                                <label for="save-address-check" class="form-check-label">
                                                    Lưu địa chỉ này vào sổ địa chỉ
                                                </label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Pickup Location Section -->
                            <div id="pickup-location-section" class="step-hidden">
                                <hr class="my-4">
                                <h4 class="h6 mb-3">Thông tin người nhận</h4>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="pickup_full_name" class="form-label">Họ & tên <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="pickup_full_name" name="pickup_full_name"
                                            class="form-control" required>
                                        <div id="pickup_full_name_error" class="error-message" style="display: none;">
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                            <span>Vui lòng nhập tên</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pickup_phone_number" class="form-label">Số điện thoại <span
                                                class="text-danger">*</span></label>
                                        <input type="tel" id="pickup_phone_number" name="pickup_phone_number"
                                            class="form-control" required>
                                        <div id="pickup_phone_number_error" class="error-message" style="display: none;">
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                            <span>Vui lòng nhập số điện thoại</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="pickup_email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" id="pickup_email" name="pickup_email" class="form-control"
                                            required>
                                        <div id="pickup_email_error" class="error-message" style="display: none;">
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                            <span>Vui lòng nhập email hợp lệ</span>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <h4 class="h6 mb-3">Chọn cửa hàng để nhận hàng <span class="text-danger">*</span></h4>
                                <!-- Button chọn cửa hàng -->
                                <div class="mb-3">
                                    <button type="button" id="select-store-btn"
                                        class="btn btn-outline-secondary w-100 text-start d-flex align-items-center justify-content-between">
                                        <span id="store-selection-text">Chọn shop có hàng gần nhất</span>
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
                                            <button type="button" id="change-store-btn"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit me-1"></i>Thay đổi
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="store_location_error" class="error-message" style="display: none;">
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                    <span>Vui lòng chọn cửa hàng nhận hàng</span>
                                </div>

                                <h4 class="h6 mb-3">Chọn thời gian nhận hàng</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="pickup-date" class="form-label">Ngày nhận hàng</label>
                                        <div class="input-group">
                                            <input type="date" id="pickup-date" name="pickup_date"
                                                class="form-control">
                                            <span class="input-group-text" style="cursor: pointer;"
                                                onclick="document.getElementById('pickup-date').showPicker()">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pickup-time-slot" class="form-label">Giờ nhận hàng</label>
                                        <select id="pickup-time-slot" name="pickup_time_slot" class="form-select">
                                            <option value="">Chọn khung giờ</option>
                                            <option value="8-11">8:00 - 11:00</option>
                                            <option value="11-14">11:00 - 14:00</option>
                                            <option value="14-17">14:00 - 17:00</option>
                                            <option value="17-20">17:00 - 20:00</option>
                                            <option value="20-22">20:00 - 22:00</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Shipping Methods -->
                            <div id="shipping-options-wrapper">
                                <h4 class="h6 mb-3">Phương thức vận chuyển</h4>
                                <div id="shipping-methods-container">
                                    <p class="text-muted small">Vui lòng chọn phương thức nhận hàng và địa chỉ/cửa hàng.
                                    </p>
                                </div>
                                <div id="shipping_method_error" class="error-message" style="display: none;">
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                    <span>Vui lòng chọn phương thức vận chuyển</span>
                                </div>

                                <!-- Delivery Time Slot Section (for store delivery method) -->
                                <div id="delivery-time-slot-section" class="step-hidden">
                                    <hr class="my-4">
                                    <h4 class="h6 mb-3">Chọn thời gian giao hàng</h4>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="delivery-date" class="form-label">Ngày nhận hàng</label>
                                            <div class="input-group">
                                                <input type="date" id="delivery-date" name="delivery_date"
                                                    class="form-control">
                                                <span class="input-group-text" style="cursor: pointer;"
                                                    onclick="document.getElementById('delivery-date').showPicker()">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                            <div id="delivery_date_error" class="error-message" style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng chọn ngày nhận hàng</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="delivery-time-slot" class="form-label">Giờ nhận hàng</label>
                                            <select id="delivery-time-slot" name="delivery_time_slot"
                                                class="form-select">
                                                <option value="">Chọn khung giờ</option>
                                                <option value="8-11">8:00 - 11:00</option>
                                                <option value="11-14">11:00 - 14:00</option>
                                                <option value="14-17">14:00 - 17:00</option>
                                                <option value="17-20">17:00 - 20:00</option>
                                                <option value="20-22">20:00 - 22:00</option>
                                            </select>
                                            <div id="delivery_time_slot_error" class="error-message"
                                                style="display: none;">
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                <span>Vui lòng chọn khung giờ giao hàng</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="bg-white rounded shadow-sm p-4">
                            <h2 class="h5 mb-4">Thanh toán</h2>
                            <div id="payment_method_error" class="error-message"
                                style="display: none; margin-bottom: 1rem;">
                                <i class="fas fa-exclamation-circle text-danger"></i>
                                <span>Vui lòng chọn phương thức thanh toán</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="form-check">
                                            <input id="cod" name="payment_method" type="radio" value="cod"
                                                class="form-check-input">
                                            <label for="cod" class="form-check-label">
                                                <div class="d-flex align-items-center">
                                                    <i class="ci-wallet fs-4 text-muted me-2"></i>
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
                                    <div class="border rounded p-3">
                                        <div class="form-check">
                                            <input id="qrcode" name="payment_method" type="radio" value="bank_transfer_qr"
                                                class="form-check-input">
                                            <label for="qrcode" class="form-check-label">
                                                <div class="d-flex align-items-center">
                                                    <i class="ci-credit-card fs-4 text-muted me-2"></i>
                                                    <div>
                                                        <div class="fw-medium">Thanh toán bằng mã QR</div>
                                                        <small class="text-muted">Quét mã QR để thanh toán</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="form-check">
                                            <input id="vnpay" name="payment_method" type="radio" value="vnpay"
                                                class="form-check-input">
                                            <label for="vnpay" class="form-check-label w-100">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <div class="fw-medium">Thanh toán qua VNPay</div>
                                                        <small class="text-muted">Ví điện tử VNPay</small>
                                                    </div>
                                                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Icon-VNPAY-QR.png"
                                                        alt="VNPay" style="height: 30px;">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="form-check">
                                            <input id="momo" name="payment_method" type="radio" value="momo"
                                                class="form-check-input">
                                            <label for="momo" class="form-check-label w-100">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <div class="fw-medium">Thanh toán qua Ví MoMo</div>
                                                        <small class="text-muted">Ví điện tử MoMo</small>
                                                    </div>
                                                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                                        alt="MoMo" style="height: 30px;">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <aside class="col-lg-4 offset-xl-1">
                    <div class="order-summary-sticky">
                        <div class="bg-white rounded shadow-sm p-4">
                            <!-- Always visible promotion section -->
                            <div class="mb-3">
                                <button type="button"
                                    class="d-flex justify-content-between align-items-center p-3 border rounded bg-light mb-3 w-100 text-start"
                                    data-bs-toggle="modal" data-bs-target="#couponModal"
                                    style="background-color: #f8f9fa;">
                                    <span class="fw-medium text-danger">Chọn hoặc nhập ưu đãi</span>
                                    <i class="ci-chevron-right text-muted"></i>
                                </button>


                            </div>

                            <!-- Scrollable order information -->
                            <div class="border-top pt-4">
                                <h4 class="h6 mb-3">Thông tin đơn hàng</h4>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Tổng tiền:</span>
                                    <span id="cart-subtotal" class="text-dark-emphasis fw-medium">
                                        {{ number_format($subtotal, 0, ',', '.') }}₫
                                    </span>
                                </div>
                               <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Giảm từ voucher:</span>
                                    <span id="cart-discount" class="text-danger fw-medium">
                                        {{ $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫' }}
                                    </span>
                                </div>

                                {{-- <<< THAY ĐỔI 1: HIỂN THỊ GIẢM GIÁ TỪ ĐIỂM >>> --}}
                                @if(isset($pointsDiscount) && $pointsDiscount > 0)
                                    <div class="d-flex justify-content-between mb-2" id="points-discount-row">
                                        <span class="text-muted small">Giảm từ điểm:</span>
                                        <span id="points-discount-amount" class="text-danger fw-medium">
                                            -{{ number_format($pointsDiscount, 0, ',', '.') }}₫
                                        </span>
                                    </div>
                                @endif
                            </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted small">Phí vận chuyển:</span>
                                    <span id="shipping-fee-summary" class="fw-medium">Chưa xác định</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-3 mb-3">
                                    <span class="fw-bold">Cần thanh toán:</span>
                                    <span id="cart-total"
                                        class="fw-bold text-danger h6">{{ number_format($total, 0, ',', '.') }}₫</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Điểm thưởng</span>
                                    <span id="points-summary" class="fw-medium text-warning small">
                                        <i class="ci-star-filled"></i> +{{ number_format($totalPointsToEarn ?? 0) }}
                                    </span>
                                </div>
                                <a href="#" class="text-decoration-none small">Xem chi tiết</a>

                                <!-- Order Button -->
                                <div class="mt-4 pt-3 border-top" style="margin-bottom: 20px;">
                                    <button type="button" id="place-order-btn" class="btn btn-danger btn-lg w-100 mb-3">
                                        <i class="ci-card me-2"></i>Đặt hàng
                                    </button>
                                    <p class="text-muted small text-center mb-0"
                                        style="font-size: 0.75rem; line-height: 1.3;">
                                        Bằng việc tiến hành đặt mua hàng, bạn đồng ý với
                                        <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> và
                                        <a href="#" class="text-decoration-none">Chính sách xử lý dữ liệu cá
                                            nhân</a> của chúng tôi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- User State Data for JavaScript -->
    <script>
        window.userState = {
            isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
            hasAddresses: {{ Auth::check() && Auth::user()->addresses && Auth::user()->addresses->count() > 0 ? 'true' : 'false' }},
            user: @if (Auth::check())
                {
                    name: '{{ Auth::user()->name }}',
                    email: '{{ Auth::user()->email }}',
                    phone: '{{ Auth::user()->phone_number ?? '' }}'
                }
            @else
                null
            @endif ,
            addresses: @if (Auth::check() && Auth::user()->addresses)
                [
                    @foreach (Auth::user()->addresses as $addr)
                        {
                            id: {{ $addr->id }},
                            name: '{{ $addr->full_name }}',
                            phone: '{{ $addr->phone_number }}',
                            full: '{{ $addr->full_address_with_type }}',
                            default: {{ $addr->is_default_shipping ? 'true' : 'false' }}
                        }
                        {{ !$loop->last ? ',' : '' }}
                    @endforeach
                ]
            @else
                []
            @endif
        };
    </script>

    <!-- Address Selection Modal -->
    <div class="modal fade" id="address-modal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header address-modal-header">
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
                            <input type="text" id="address-search-input"
                                placeholder="Tìm kiếm theo tên, SĐT, địa chỉ..." class="form-control">
                        </div>
                    </div>
                    <div id="modal-address-list" class="address-list" style="max-height: 400px; overflow-y: auto;">
                        <!-- Modal address list will be rendered here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="button" id="confirm-address-selection-btn" class="btn btn-danger" disabled>
                        <i class="fas fa-check me-1"></i>Chọn địa chỉ này
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Selection Modal -->
    <div class="modal fade" id="store-selection-modal" tabindex="-1" aria-labelledby="storeSelectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header store-modal-header">
                    <h5 class="modal-title" id="storeSelectionModalLabel">
                        <i class="fas fa-store me-2"></i>Chọn shop
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filter Section -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <select id="modal-store-province-select" class="form-select">
                                <option value="">Tất cả Tỉnh/Thành phố</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select id="modal-store-district-select" class="form-select" disabled>
                                <option value="">Tất cả Quận/Huyện</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small" id="store-count-text">Có 0 cửa hàng còn hàng</span>
                    </div>

                    <!-- Store List -->
                    <div id="modal-store-list" class="store-list" style="max-height: 350px; overflow-y: auto;">
                        <p class="text-muted small text-center">Đang tải danh sách cửa hàng...</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- DỮ LIỆU NGƯỜI DÙNG TỪ SERVER ---
            const userAddresses = window.userState.addresses || [];

            // Dữ liệu giỏ hàng thực từ Laravel
            const cartItems = [
                @foreach ($items as $item)
                    {
                        id: {{ $item->id }},
                        name: '{{ $item->productVariant->product->name }}',
                        variant: '{{ $item->productVariant->attributeValues->pluck('value')->implode(', ') }}',
                        quantity: {{ $item->quantity }},
                        price: {{ $item->price }},
                        originalPrice: {{ $item->price }},
                        image: '{{ $item->productVariant->image_url ?? asset('assets/users/img/no-image.png') }}'
                    },
                @endforeach
            ];

            // Dữ liệu thực từ Laravel
            const baseSubtotal = {{ $subtotal }};
            const baseDiscount = {{ $discount }};
            const basePointsDiscount = {{ $pointsDiscount ?? 0 }};
            const baseWeight = {{ $baseWeight ?? 1000 }};
            const baseLength = {{ $baseLength ?? 20 }};
            const baseWidth = {{ $baseWidth ?? 10 }};
            const baseHeight = {{ $baseHeight ?? 10 }};

            // Cache phí vận chuyển cho API GHN
            const ghnFeeCache = {};

            // --- CÁC PHẦN TỬ DOM ---
            const deliveryMethodCards = document.querySelectorAll(
                '#delivery-method-delivery, #delivery-method-pickup');
            const deliveryAddressSection = document.getElementById('delivery-address-section');
            const pickupLocationSection = document.getElementById('pickup-location-section');
            const addressBook = document.getElementById('address-book');
            const mainAddressList = document.getElementById('main-address-list');
            const newAddressFormWrapper = document.getElementById('new-address-form-wrapper');
            const useNewAddressBtn = document.getElementById('use-new-address-btn');
            const shippingOptionsWrapper = document.getElementById('shipping-options-wrapper');
            const shippingMethodsContainer = document.getElementById('shipping-methods-container');
            const saveAddressWrapper = document.getElementById('save-address-wrapper');
            const formIntroText = document.getElementById('form-intro-text');
            const provinceSelect = document.getElementById('province_id');
            const districtSelect = document.getElementById('district_id');
            const wardSelect = document.getElementById('ward_id');
            const shippingFeeSummary = document.getElementById('shipping-fee-summary');
            const grandTotalSummary = document.getElementById('grand-total-summary');
            const subtotalSummary = document.getElementById('subtotal-summary');
            const promotionSummary = document.getElementById('promotion-summary');
            const addressForm = document.getElementById('address-form');

            const addressModal = document.getElementById('address-modal');
            const openModalBtn = document.getElementById('open-address-modal-btn');
            const closeModalBtn = addressModal?.querySelector('.btn-close');
            const modalAddressList = document.getElementById('modal-address-list');
            const addressSearchInput = document.getElementById('address-search-input');
            const confirmAddressBtn = document.getElementById('confirm-address-selection-btn');

            const pickupDateInput = document.getElementById('pickup-date');
            const deliveryTimeSlotSection = document.getElementById('delivery-time-slot-section');
            const deliveryDateInput = document.getElementById('delivery-date');
            const deliveryTimeSlotSelect = document.getElementById('delivery-time-slot');

            // Các phần tử chọn cửa hàng
            const selectStoreBtn = document.getElementById('select-store-btn');
            const selectedStoreDisplay = document.getElementById('selected-store-display');
            const selectedStoreName = document.getElementById('selected-store-name');
            const selectedStoreAddress = document.getElementById('selected-store-address');
            const selectedStorePhone = document.getElementById('selected-store-phone');
            const changeStoreBtn = document.getElementById('change-store-btn');

            // Các phần tử modal cửa hàng
            const storeSelectionModal = document.getElementById('store-selection-modal');
            const modalStoreProvinceSelect = document.getElementById('modal-store-province-select');
            const modalStoreDistrictSelect = document.getElementById('modal-store-district-select');
            const modalStoreList = document.getElementById('modal-store-list');
            const storeCountText = document.getElementById('store-count-text');
            const confirmStoreSelectionBtn = document.getElementById('confirm-store-selection-btn');

            let subtotal = 0;
            let selectedAddressId = userAddresses.find(a => a.default)?.id || null;
            let selectedStore = null;

            // --- CÁC HÀM CHÍNH ---
            function setupUIForUserType() {
                // Xác định loại người dùng dựa trên trạng thái đăng nhập thực tế
                let userType = 'guest';
                if (window.userState.isLoggedIn) {
                    userType = window.userState.hasAddresses ? 'logged_in_with_address' : 'logged_in_no_address';
                }

                const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;

                addressBook.classList.add('step-hidden');
                newAddressFormWrapper.classList.add('step-hidden');
                saveAddressWrapper.classList.add('step-hidden');

                if (deliveryMethod === 'delivery') {
                    deliveryAddressSection.style.display = 'block';
                    pickupLocationSection.classList.add('step-hidden');
                    shippingOptionsWrapper.style.display = 'block';
                    shippingOptionsWrapper.classList.remove('disabled-section');

                    const selectedShippingMethod = document.querySelector('input[name="shipping_method"]:checked');
                    if (selectedShippingMethod) {
                        updateOrderInformation({
                            shippingFee: parseInt(selectedShippingMethod.dataset.fee, 10)
                        });
                    } else {
                        disableShippingOptions("Vui lòng chọn địa chỉ đầy đủ.");
                    }

                    if (userType === 'logged_in_with_address') {
                        addressBook.classList.remove('step-hidden');

                        // Đặt địa chỉ mặc định nếu chưa được chọn
                        if (!selectedAddressId) {
                            const defaultAddress = userAddresses.find(a => a.default);
                            if (defaultAddress) {
                                selectedAddressId = defaultAddress.id;
                            }
                        }

                        renderMainAddressList();
                        getShippingOptionsForSavedAddress();
                    } else if (userType === 'logged_in_no_address') {
                        formIntroText.textContent =
                            "Bạn chưa có địa chỉ nào được lưu. Vui lòng nhập địa chỉ mới bên dưới.";
                        saveAddressWrapper.classList.remove('step-hidden');
                        showNewAddressForm();
                        prefillUserInfo();

                        // Ẩn nút quay lại cho người dùng không có địa chỉ
                        const backBtn = document.getElementById('back-to-address-book-btn');
                        if (backBtn) backBtn.classList.add('step-hidden');
                    } else { // Khách
                        formIntroText.textContent =
                            "Vui lòng điền đầy đủ thông tin để chúng tôi giao hàng cho bạn.";
                        showNewAddressForm();

                        // Ẩn nút quay lại cho khách
                        const backBtn = document.getElementById('back-to-address-book-btn');
                        if (backBtn) backBtn.classList.add('step-hidden');
                    }
                } else { // nhận tại cửa hàng
                    deliveryAddressSection.style.display = 'none';
                    pickupLocationSection.classList.remove('step-hidden');
                    shippingOptionsWrapper.style.display = 'block';
                    disableShippingOptions("Không áp dụng cho nhận tại cửa hàng.");
                    updateOrderInformation({
                        shippingFee: 0
                    });
                    prefillUserInfo(); // Điền sẵn thông tin người dùng cho form pickup

                    // Ẩn nút quay lại khi chọn phương thức pickup
                    const backBtn = document.getElementById('back-to-address-book-btn');
                    if (backBtn) backBtn.classList.add('step-hidden');
                }
            }

            function selectAddress(addressId) {
                selectedAddressId = addressId;
                newAddressFormWrapper.classList.add('step-hidden');
                // Không tự động render danh sách địa chỉ chính ở đây để tránh gọi trùng lặp
                // Nó sẽ được gọi rõ ràng khi cần thiết
            }

            function showNewAddressForm() {
                selectedAddressId = null;

                // Ẩn sổ địa chỉ và hiển thị form địa chỉ mới
                addressBook.classList.add('step-hidden');
                newAddressFormWrapper.classList.remove('step-hidden');

                // Hiển thị nút quay lại cho người dùng đã đăng nhập có địa chỉ hiện có
                const backBtn = document.getElementById('back-to-address-book-btn');
                if (backBtn && userAddresses.length > 0) {
                    backBtn.classList.remove('step-hidden');
                }

                if (provinceSelect && provinceSelect.options.length <= 1) {
                    loadProvinces();
                }
                disableShippingOptions("Vui lòng chọn địa chỉ đầy đủ.");
            }

            function showAddressBook() {
                // Ẩn form địa chỉ mới và hiển thị sổ địa chỉ
                newAddressFormWrapper.classList.add('step-hidden');
                addressBook.classList.remove('step-hidden');

                // Khôi phục địa chỉ đã chọn trước đó hoặc mặc định
                if (!selectedAddressId) {
                    const defaultAddress = userAddresses.find(a => a.default);
                    if (defaultAddress) {
                        selectedAddressId = defaultAddress.id;
                    }
                }

                renderMainAddressList();
                getShippingOptionsForSavedAddress();
            }

            function renderMainAddressList() {
                mainAddressList.innerHTML = '';

                // Chỉ hiển thị địa chỉ đã chọn (mặc định hoặc người dùng chọn)
                const selectedAddress = userAddresses.find(addr => addr.id === selectedAddressId);

                if (selectedAddress) {
                    const card = document.createElement('div');
                    card.className = 'address-card selected';
                    card.dataset.addressId = selectedAddress.id;
                    card.innerHTML = `
                         <div class="d-flex align-items-start">
                             <input type="radio" name="selected_address" id="address-${selectedAddress.id}" value="${selectedAddress.id}" class="form-check-input mt-1" checked>
                             <label for="address-${selectedAddress.id}" class="ms-3 flex-grow-1 cursor-pointer">
                                 <strong class="d-block text-dark">${selectedAddress.name}</strong>
                                 <span class="d-block small text-muted">${selectedAddress.phone}</span>
                                 <span class="d-block small text-muted">${selectedAddress.full}</span>
                                 ${selectedAddress.default ? '<span class="badge bg-success small">Mặc định</span>' : ''}
                             </label>
                         </div>
                     `;
                    card.addEventListener('click', () => selectAddress(parseInt(card.dataset.addressId)));
                    mainAddressList.appendChild(card);
                } else {
                    // Nếu không có địa chỉ nào được chọn, hiển thị địa chỉ mặc định
                    const defaultAddress = userAddresses.find(addr => addr.default);
                    if (defaultAddress) {
                        selectAddress(defaultAddress.id);
                        return; // Sẽ render lại với địa chỉ đã chọn
                    }
                }
            }

            function openModal() {
                renderModalAddressList();
                const modal = new bootstrap.Modal(addressModal);
                modal.show();
            }

            function renderModalAddressList(searchTerm = '') {
                modalAddressList.innerHTML = '';
                const filtered = userAddresses.filter(addr =>
                    addr.name.toLowerCase().includes(searchTerm) ||
                    addr.phone.toLowerCase().includes(searchTerm) ||
                    addr.full.toLowerCase().includes(searchTerm)
                );

                if (filtered.length === 0) {
                    modalAddressList.innerHTML =
                        `<p class="text-center text-muted">Không tìm thấy địa chỉ nào.</p>`;
                    confirmAddressBtn.disabled = true;
                    return;
                }

                filtered.forEach(address => {
                    const isSelected = address.id === selectedAddressId;
                    const item = document.createElement('div');
                    item.className = 'border rounded p-3 mb-2 cursor-pointer';
                    item.style.cursor = 'pointer';
                    item.innerHTML = `
                        <div class="d-flex align-items-start">
                            <input type="radio" name="modal_selected_address" id="modal-address-${address.id}" value="${address.id}" class="form-check-input mt-1" ${isSelected ? 'checked' : ''}>
                            <label for="modal-address-${address.id}" class="ms-3 flex-grow-1">
                                <strong class="d-block text-dark">${address.name}${address.default ? ' <span class="badge bg-success ms-2">Mặc định</span>' : ''}</strong>
                                <span class="d-block small text-muted">${address.phone}</span>
                                <span class="d-block small text-muted">${address.full}</span>
                            </label>
                        </div>
                    `;

                    // Thêm listener click cho mỗi item
                    item.addEventListener('click', () => {
                        const radio = item.querySelector('input[type="radio"]');
                        radio.checked = true;
                        confirmAddressBtn.disabled = false;

                        // Cập nhật lựa chọn trực quan
                        document.querySelectorAll('#modal-address-list .border').forEach(el => {
                            el.classList.remove('border-danger');
                            el.classList.add('border');
                        });
                        item.classList.remove('border');
                        item.classList.add('border-danger');
                    });

                    modalAddressList.appendChild(item);
                });

                // Bật/tắt nút xác nhận dựa trên lựa chọn
                const hasSelection = document.querySelector('input[name="modal_selected_address"]:checked');
                confirmAddressBtn.disabled = !hasSelection;
            }

            function handleConfirmSelection() {
                const selectedRadio = document.querySelector('input[name="modal_selected_address"]:checked');
                if (selectedRadio) {
                    const newAddressId = parseInt(selectedRadio.value);
                    selectAddress(newAddressId);

                    // Buộc render lại danh sách địa chỉ chính với lựa chọn mới
                    renderMainAddressList();

                    // Tính lại tùy chọn vận chuyển cho địa chỉ mới
                    getShippingOptionsForSavedAddress();
                }
                const modal = bootstrap.Modal.getInstance(addressModal);
                modal.hide();
            }

            // Tải tỉnh/thành phố từ API (hệ thống cũ)
            async function loadProvinces() {
                try {
                    const response = await fetch('/api/locations/old/provinces');
                    const data = await response.json();

                    if (data.success) {
                        provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
                        data.data.forEach(province => {
                            const option = document.createElement('option');
                            option.value = province.code;
                            option.textContent = province.name_with_type;
                            option.dataset.name = province.name;
                            provinceSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error loading provinces:', data.message);
                    }
                } catch (error) {
                    console.error('Error fetching provinces:', error);
                }
            }

            // Tải quận/huyện từ API (hệ thống cũ)
            async function loadDistricts(provinceCode) {
                try {
                    districtSelect.innerHTML = '<option value="">Đang tải...</option>';
                    districtSelect.disabled = true;
                    wardSelect.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
                    wardSelect.disabled = true;
                    disableShippingOptions("Vui lòng chọn địa chỉ đầy đủ.");

                    if (!provinceCode) {
                        districtSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố trước</option>';
                        return;
                    }

                    const response = await fetch(`/api/locations/old/districts/${provinceCode}`);
                    const data = await response.json();

                    if (data.success) {
                        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
                        data.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.code;
                            option.textContent = district.name_with_type;
                            option.dataset.name = district.name;
                            districtSelect.appendChild(option);
                        });
                        districtSelect.disabled = false;
                    } else {
                        console.error('Error loading districts:', data.message);
                        districtSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                    }
                } catch (error) {
                    console.error('Error fetching districts:', error);
                    districtSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                }
            }

            // Tải phường/xã từ API (hệ thống cũ)
            async function loadWards(districtCode) {
                try {
                    wardSelect.innerHTML = '<option value="">Đang tải...</option>';
                    wardSelect.disabled = true;
                    disableShippingOptions("Vui lòng chọn địa chỉ đầy đủ.");

                    if (!districtCode) {
                        wardSelect.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
                        return;
                    }

                    const response = await fetch(`/api/locations/old/wards/${districtCode}`);
                    const data = await response.json();

                    if (data.success) {
                        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
                        data.data.forEach(ward => {
                            const option = document.createElement('option');
                            option.value = ward.code;
                            option.textContent = ward.name_with_type;
                            option.dataset.name = ward.name;
                            wardSelect.appendChild(option);
                        });
                        wardSelect.disabled = false;
                    } else {
                        console.error('Error loading wards:', data.message);
                        wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                    }
                } catch (error) {
                    console.error('Error fetching wards:', error);
                    wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                }
            }

            function disableShippingOptions(message) {
                shippingOptionsWrapper.classList.add('disabled-section');
                shippingMethodsContainer.innerHTML = `<p class="text-muted small">${message}</p>`;
                updateOrderInformation({
                    shippingFee: null
                });
            }

            function getShippingOptionsForSavedAddress() {
                shippingOptionsWrapper.classList.remove('disabled-section');
                shippingMethodsContainer.innerHTML =
                    `<p class="text-muted small">Vui lòng chọn phương thức vận chuyển</p>`;

                // Xác định loại người dùng
                let userType = 'guest';
                if (window.userState.isLoggedIn) {
                    userType = window.userState.hasAddresses ? 'logged_in_with_address' : 'logged_in_no_address';
                }

                // Tùy chọn vận chuyển thực tế (đã loại bỏ "Giao hàng tiêu chuẩn" theo yêu cầu)
                let shippingOptions = [{
                        method_id: 1,
                        name: 'Giao hàng nhanh',
                        fee: null,
                        isGhn: true
                    }, // Phí sẽ được tính khi chọn
                    {
                        method_id: 2,
                        name: 'Giao hàng của cửa hàng',
                        fee: 25000,
                        isGhn: false
                    }
                ];

                // Lưu ý: "Giao hàng tiêu chuẩn" đã được loại bỏ cho tất cả người dùng theo yêu cầu

                renderShippingOptions(shippingOptions);
            }

            function getShippingOptionsFromForm() {
                if (provinceSelect.value && districtSelect.value && wardSelect.value) {
                    getShippingOptionsForSavedAddress();
                }
            }

            // Lấy phí vận chuyển GHN
            async function fetchGhnFee(provinceName, districtName, wardName, weight, length, width, height) {
                const cacheKey =
                    `${provinceName}|${districtName}|${wardName}|${weight}|${length}|${width}|${height}`;
                if (ghnFeeCache[cacheKey]) return ghnFeeCache[cacheKey];

                try {
                    const res = await fetch('/ajax/ghn/shipping-fee', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            province_name: provinceName,
                            district_name: districtName,
                            ward_name: wardName,
                            weight: weight,
                            length: length,
                            width: width,
                            height: height
                        })
                    });
                    const data = await res.json();

                    // Kiểm tra success từ backend response thay vì chỉ kiểm tra fee
                    if (data.success === true && data.fee !== null && data.fee !== undefined) {
                        const result = {
                            success: true,
                            fee: data.fee
                        };
                        ghnFeeCache[cacheKey] = result; // Cache kết quả đầy đủ
                        return result;
                    } else {
                        console.error('GHN Fee Error:', data.message || 'Không lấy được phí GHN');
                        const result = {
                            success: false,
                            message: data.message || 'Địa điểm này không được hỗ trợ giao hàng nhanh'
                        };
                        ghnFeeCache[cacheKey] = result; // Cache cả kết quả lỗi
                        return result;
                    }
                } catch (e) {
                    console.error('Error calling GHN API:', e);
                    const result = {
                        success: false,
                        message: 'Lỗi khi tính phí giao hàng nhanh'
                    };
                    // Không cache lỗi mạng vì chúng có thể tạm thời
                    return result;
                }
            }

            // Hàm debug cho việc test GHN (chỉ trong môi trường development)
            function debugGhnInfo(addressInfo, savedAddress = null) {
                // if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
                //     console.log('🚚 GHN Debug Info:', {
                //         addressInfo,
                //         savedAddress,
                //         baseWeight,
                //         baseLength,
                //         baseWidth,
                //         baseHeight,
                //         selectedAddressId,
                //         userAddresses: userAddresses.length
                //     });
                // }
            }

            // Trích xuất thông tin địa chỉ từ địa chỉ đã lưu cho GHN
            function extractAddressInfoFromSaved(addressFull) {
                // Phân tích địa chỉ đầy đủ để trích xuất tỉnh, huyện, xã
                // Ví dụ:
                // "Phường Ba Láng, Quận Cái Răng, Thành phố Cần Thơ"
                // "Xã Hòa Quang Nam, Huyện Phú Hoà, Tỉnh Phú Yên"
                // "Ba Láng, Cái Răng, Cần Thơ" (không có tiền tố)

                const parts = addressFull.split(',').map(part => part.trim());

                if (parts.length >= 3) {
                    // Lấy 3 phần cuối (xã, huyện, tỉnh)
                    const wardPart = parts[parts.length - 3];
                    const districtPart = parts[parts.length - 2];
                    const provincePart = parts[parts.length - 1];

                    return {
                        ward: wardPart,
                        district: districtPart,
                        province: provincePart
                    };
                } else if (parts.length === 2) {
                    // Chỉ có huyện và tỉnh
                    return {
                        ward: '',
                        district: parts[0],
                        province: parts[1]
                    };
                }

                return null;
            }

            async function renderShippingOptions(options) {
                if (options.length > 0) {
                    let html = '';

                    for (let i = 0; i < options.length; i++) {
                        const option = options[i];
                        let displayFee = option.fee;
                        let feeText = '';

                        // Không tính phí GHN ban đầu, chỉ khi người dùng chọn
                        if (displayFee === 0) {
                            feeText = 'Miễn phí';
                        } else if (displayFee === null) {
                            feeText = 'Đang tính...';
                        } else {
                            feeText = new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(displayFee);
                        }

                        html += `
                            <div class="border rounded p-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="shipping_method" id="ship-${option.method_id}" value="${option.method_id}" data-fee="${displayFee || 0}" data-name="${option.name}" data-is-ghn="${option.isGhn}" class="form-check-input">
                                    <label for="ship-${option.method_id}" class="ms-3 d-flex justify-content-between w-100 cursor-pointer">
                                        <span class="fw-medium">${option.name}</span>
                                        <span class="fw-medium" id="fee-${option.method_id}">${feeText}</span>
                                    </label>
                                </div>
                            </div>`;
                    }

                    shippingMethodsContainer.innerHTML = html;

                    // Thêm event listeners
                    document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
                        radio.addEventListener('change', async (e) => {
                            // Ẩn lỗi phương thức vận chuyển khi người dùng chọn một tùy chọn
                            const shippingError = document.getElementById(
                                'shipping_method_error');
                            if (shippingError) {
                                shippingError.style.display = 'none';
                            }

                            // Xóa các thông báo GHN hiện có
                            const existingNotification = shippingMethodsContainer.parentNode
                                .querySelector('.alert-warning, .alert-danger');
                            if (existingNotification) {
                                existingNotification.remove();
                            }

                            const isGhn = e.target.dataset.isGhn === 'true';
                            const methodId = e.target.value;
                            const feeElement = document.getElementById(`fee-${methodId}`);

                            if (isGhn) {
                                // Tính phí GHN khi người dùng chọn "Giao hàng nhanh"
                                feeElement.textContent = 'Đang tính...';

                                let provinceText = '',
                                    districtText = '',
                                    wardText = '';

                                // Kiểm tra xem có sử dụng địa chỉ đã lưu hay form địa chỉ mới
                                if (selectedAddressId && userAddresses.length > 0) {
                                    // Sử dụng địa chỉ đã lưu - trích xuất từ chuỗi địa chỉ
                                    const selectedAddress = userAddresses.find(addr => addr
                                        .id === selectedAddressId);
                                    if (selectedAddress) {
                                        const addressInfo = extractAddressInfoFromSaved(
                                            selectedAddress.full);
                                        if (addressInfo) {
                                            wardText = addressInfo.ward;
                                            districtText = addressInfo.district;
                                            provinceText = addressInfo.province;
                                        }
                                    }
                                } else {
                                    // Sử dụng form địa chỉ mới - lấy từ các phần tử select
                                    if (provinceSelect.value && districtSelect.value &&
                                        wardSelect.value) {
                                        provinceText = provinceSelect.options[provinceSelect
                                            .selectedIndex]?.textContent || '';
                                        districtText = districtSelect.options[districtSelect
                                            .selectedIndex]?.textContent || '';
                                        wardText = wardSelect.options[wardSelect
                                            .selectedIndex]?.textContent || '';
                                    }
                                }

                                if (provinceText && districtText && wardText) {
                                    // Thông tin debug cho development
                                    debugGhnInfo({
                                            province: provinceText,
                                            district: districtText,
                                            ward: wardText
                                        }, selectedAddressId ? userAddresses.find(
                                            addr => addr.id === selectedAddressId) :
                                        null);

                                    const ghnResult = await fetchGhnFee(provinceText,
                                        districtText, wardText, baseWeight, baseLength,
                                        baseWidth, baseHeight);

                                    if (ghnResult.success) {
                                        // GHN được hỗ trợ - hiển thị phí
                                        e.target.dataset.fee = ghnResult.fee;
                                        feeElement.textContent = new Intl.NumberFormat(
                                            'vi-VN', {
                                                style: 'currency',
                                                currency: 'VND'
                                            }).format(ghnResult.fee);
                                        updateOrderInformation({
                                            shippingFee: ghnResult.fee
                                        });
                                    } else {
                                        // GHN không được hỗ trợ - hiển thị lỗi và gợi ý giao hàng cửa hàng
                                        e.target.dataset.fee = 'unsupported';
                                        feeElement.innerHTML = `
                                            <div class="text-danger small fw-bold">
                                                <div>Không hỗ trợ</div>
                                            </div>
                                        `;
                                        updateOrderInformation({
                                            shippingFee: null
                                        }); // null để không cập nhật hiển thị phí

                                        // Hiển thị thông báo cho người dùng
                                        const notification = document.createElement('div');
                                        notification.className =
                                            'alert alert-danger alert-dismissible fade show mt-3';
                                        notification.style.border = '2px solid #dc3545';
                                        notification.innerHTML = `
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-exclamation-triangle text-danger me-3 mt-1" style="font-size: 1.2rem;"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-2">
                                                        <strong>Địa điểm bạn chọn không hỗ trợ giao hàng nhanh!</strong>
                                                    </h6>
                                                    <p class="mb-0">
                                                        Vui lòng chọn <span class="text-primary fw-bold">"Giao hàng của cửa hàng"</span> bên dưới.
                                                    </p>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        `;

                                        // Chèn thông báo sau container phương thức vận chuyển
                                        const container = shippingMethodsContainer
                                            .parentNode;
                                        container.insertBefore(notification,
                                            shippingMethodsContainer.nextSibling);

                                        // Tự động ẩn sau 8 giây
                                        setTimeout(() => {
                                            if (notification && notification
                                                .parentNode) {
                                                notification.remove();
                                            }
                                        }, 8000);
                                    }
                                } else {
                                    // Thiếu thông tin địa chỉ - hiển thị thông báo thông tin
                                    e.target.dataset.fee = 0;
                                    feeElement.innerHTML = `
                                        <div class="text-muted small">
                                            <div>Chưa có đủ thông tin địa chỉ</div>
                                            <div class="small">Vui lòng hoàn tất thông tin địa chỉ</div>
                                        </div>
                                    `;
                                    updateOrderInformation({
                                        shippingFee: 0
                                    });
                                }
                            } else {
                                // Sử dụng phí định sẵn cho các phương thức không phải GHN ("Giao hàng của cửa hàng", "Giao hàng tiêu chuẩn")
                                const fee = parseInt(e.target.dataset.fee, 10) || 0;
                                feeElement.textContent = fee === 0 ? 'Miễn phí' : new Intl
                                    .NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND'
                                    }).format(fee);
                                updateOrderInformation({
                                    shippingFee: fee
                                });
                            }

                            // Hiển thị/ẩn phần khung giờ giao hàng cho "Giao hàng của cửa hàng"
                            const methodName = e.target.dataset.name;
                            if (methodName === 'Giao hàng của cửa hàng') {
                                deliveryTimeSlotSection.classList.remove('step-hidden');
                            } else {
                                deliveryTimeSlotSection.classList.add('step-hidden');
                            }
                        });
                    });

                    // Không tự động chọn tùy chọn nào - để người dùng chọn
                    updateOrderInformation({
                        shippingFee: null
                    });
                } else {
                    shippingMethodsContainer.innerHTML =
                        `<p class="text-danger small">Không tìm thấy phương thức vận chuyển.</p>`;
                    updateOrderInformation({
                        shippingFee: null
                    });
                }
            }

            function renderMainProductList() {
                const productListContainer = document.getElementById('main-product-list');
                const productCount = document.getElementById('product-count');
                if (!productListContainer || !productCount) return;

                productListContainer.innerHTML = '';
                subtotal = cartItems.reduce((acc, item) => acc + (item.price * item.quantity), 0);
                productCount.textContent = cartItems.length;

                cartItems.forEach(product => {
                    const productDiv = document.createElement('div');
                    productDiv.className = 'd-flex align-items-start gap-3 pb-3 mb-3';
                    if (cartItems.indexOf(product) < cartItems.length - 1) {
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
                            <p class="fw-bold text-danger small mb-0">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price)}</p>
                            <p class="text-muted small mb-1">x${product.quantity}</p>
                        </div>
                    `;
                    productListContainer.appendChild(productDiv);
                });
            }

           function updateOrderInformation({ shippingFee }) {
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
    if (deliveryMethod === 'pickup') {
        shippingFee = 0;
    }

    const shippingFeeSummary = document.getElementById('shipping-fee-summary');
    const grandTotalSummary = document.getElementById('cart-total');

    let finalTotal;

    if (shippingFee === null || shippingFee === undefined) {
        shippingFeeSummary.textContent = 'Chưa xác định';
        finalTotal = baseSubtotal - baseDiscount - basePointsDiscount;
    } else {
        shippingFeeSummary.textContent = shippingFee === 0 ? 'Miễn phí' : new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(shippingFee);

        // Công thức tính tổng đúng bao gồm cả giảm giá từ điểm
        finalTotal = baseSubtotal - baseDiscount - basePointsDiscount + shippingFee;
    }

    if (grandTotalSummary) {
        grandTotalSummary.textContent = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(finalTotal > 0 ? finalTotal : 0);
    }
}

            // Điền sẵn thông tin người dùng cho người dùng đã đăng nhập
            function prefillUserInfo() {
                if (window.userState.isLoggedIn && window.userState.user) {
                    const user = window.userState.user;

                    // Cho form giao hàng
                    const fullNameInput = document.getElementById('full_name');
                    const emailInput = document.getElementById('email');
                    const phoneInput = document.getElementById('phone_number');

                    if (fullNameInput) {
                        fullNameInput.value = user.name || '';
                        // Kích hoạt validation sau khi điền sẵn
                        if (fullNameInput.value) hideError('full_name');
                    }
                    if (emailInput) {
                        emailInput.value = user.email || '';
                        if (emailInput.value) hideError('email');
                    }
                    if (phoneInput) {
                        phoneInput.value = user.phone || '';
                        if (phoneInput.value) hideError('phone_number');
                    }

                    // Cho form nhận tại cửa hàng
                    const pickupFullNameInput = document.getElementById('pickup_full_name');
                    const pickupEmailInput = document.getElementById('pickup_email');
                    const pickupPhoneInput = document.getElementById('pickup_phone_number');

                    if (pickupFullNameInput) {
                        pickupFullNameInput.value = user.name || '';
                        if (pickupFullNameInput.value) hideError('pickup_full_name');
                    }
                    if (pickupEmailInput) {
                        pickupEmailInput.value = user.email || '';
                        if (pickupEmailInput.value) hideError('pickup_email');
                    }
                    if (pickupPhoneInput) {
                        pickupPhoneInput.value = user.phone || '';
                        if (pickupPhoneInput.value) hideError('pickup_phone_number');
                    }
                }
            }

            // --- GẮN SỰ KIỆN ---
            deliveryMethodCards.forEach(card => card.addEventListener('click', () => {
                const radio = card.querySelector('input[type="radio"]');
                radio.checked = true;
                document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                setupUIForUserType();
            }));

            // Ẩn lỗi phương thức thanh toán khi người dùng chọn một phương thức thanh toán
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    const paymentError = document.getElementById('payment_method_error');
                    if (paymentError) {
                        paymentError.style.display = 'none';
                    }
                });
            });

            // Ẩn lỗi vị trí cửa hàng khi người dùng chọn một cửa hàng
            const storeSelect = document.getElementById('store-location-select');
            if (storeSelect) {
                storeSelect.addEventListener('change', () => {
                    const storeError = document.getElementById('store_location_error');
                    if (storeError) {
                        storeError.style.display = 'none';
                        storeSelect.classList.remove('is-invalid');
                    }
                });
            }

            // Ẩn lỗi khung giờ giao hàng khi người dùng chọn
            if (deliveryDateInput) {
                deliveryDateInput.addEventListener('change', () => {
                    const deliveryDateError = document.getElementById('delivery_date_error');
                    if (deliveryDateError) {
                        deliveryDateError.style.display = 'none';
                        deliveryDateInput.classList.remove('is-invalid');
                    }
                });
            }

            if (deliveryTimeSlotSelect) {
                deliveryTimeSlotSelect.addEventListener('change', () => {
                    const deliveryTimeSlotError = document.getElementById('delivery_time_slot_error');
                    if (deliveryTimeSlotError) {
                        deliveryTimeSlotError.style.display = 'none';
                        deliveryTimeSlotSelect.classList.remove('is-invalid');
                    }
                });
            }
            if (useNewAddressBtn) useNewAddressBtn.addEventListener('click', showNewAddressForm);

            const backToAddressBookBtn = document.getElementById('back-to-address-book-btn');
            if (backToAddressBookBtn) backToAddressBookBtn.addEventListener('click', showAddressBook);

            if (openModalBtn) openModalBtn.addEventListener('click', openModal);
            if (confirmAddressBtn) confirmAddressBtn.addEventListener('click', handleConfirmSelection);
            if (addressSearchInput) addressSearchInput.addEventListener('input', (e) => renderModalAddressList(e
                .target.value.toLowerCase()));

            if (provinceSelect) provinceSelect.addEventListener('change', (e) => loadDistricts(e.target.value));
            if (districtSelect) districtSelect.addEventListener('change', (e) => loadWards(e.target.value));
            if (wardSelect) wardSelect.addEventListener('change', getShippingOptionsFromForm);

            // Event listeners cho việc chọn cửa hàng
            if (selectStoreBtn) {
                selectStoreBtn.addEventListener('click', openStoreSelectionModal);
            }

            if (changeStoreBtn) {
                changeStoreBtn.addEventListener('click', openStoreSelectionModal);
            }

            // Event listeners cho modal cửa hàng
            if (modalStoreProvinceSelect) {
                modalStoreProvinceSelect.addEventListener('change', (e) => {
                    const provinceCode = e.target.value;
                    loadModalStoreDistricts(provinceCode);
                    loadModalStoreLocations(provinceCode);
                });
            }

            if (modalStoreDistrictSelect) {
                modalStoreDistrictSelect.addEventListener('change', (e) => {
                    const provinceCode = modalStoreProvinceSelect.value;
                    const districtCode = e.target.value;
                    loadModalStoreLocations(provinceCode, districtCode);
                });
            }



            if (confirmStoreSelectionBtn) {
                confirmStoreSelectionBtn.addEventListener('click', confirmStoreSelection);
            }

            // Nút đặt hàng
            const placeOrderBtn = document.getElementById('place-order-btn');
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', function() {
                    // Xóa tất cả lỗi hiển thị trước khi xử lý
                    document.querySelectorAll('.error-message').forEach(error => {
                        error.style.display = 'none';
                    });
                    document.querySelectorAll('.is-invalid').forEach(field => {
                        field.classList.remove('is-invalid');
                    });

                    // Cũng xóa các lỗi cụ thể
                    const shippingError = document.getElementById('shipping_method_error');
                    if (shippingError) {
                        shippingError.style.display = 'none';
                    }

                    const paymentError = document.getElementById('payment_method_error');
                    if (paymentError) {
                        paymentError.style.display = 'none';
                    }

                    const storeError = document.getElementById('store_location_error');
                    if (storeError) {
                        storeError.style.display = 'none';
                    }

                    // Xử lý đơn hàng trực tiếp - validation sẽ được xử lý bởi backend
                    processOrder();
                });
            }

            // Hàm xử lý đơn hàng
            function processOrder() {
                const placeOrderBtn = document.getElementById('place-order-btn');

                // Vô hiệu hóa nút và hiển thị loading
                placeOrderBtn.disabled = true;
                placeOrderBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xử lý...';

                const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;

                // Validation frontend bổ sung cho GHN không được hỗ trợ
                if (deliveryMethod === 'delivery') {
                    const selectedShippingMethod = document.querySelector('input[name="shipping_method"]:checked');
                    if (selectedShippingMethod && selectedShippingMethod.dataset.fee === 'unsupported') {
                        // Bật lại nút
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.innerHTML = '<i class="ci-card me-2"></i>Đặt hàng';

                        // Cuộn đến phần phương thức vận chuyển
                        shippingMethodsContainer.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        // Làm nổi bật tùy chọn không được hỗ trợ
                        selectedShippingMethod.closest('.border').style.border = '2px solid #dc3545';
                        setTimeout(() => {
                            selectedShippingMethod.closest('.border').style.border = '';
                        }, 3000);

                        return;
                    }
                }
                let orderData = {
                    delivery_method: deliveryMethod,
                    payment_method: document.querySelector('input[name="payment_method"]:checked')?.value
                };

                // Xóa store_location_id cho phương thức giao hàng để tránh xung đột validation
                if (deliveryMethod === 'delivery') {
                    orderData.store_location_id = null;
                }

                if (deliveryMethod === 'delivery') {
                    if (selectedAddressId) {
                        // Sử dụng địa chỉ đã lưu
                        orderData.address_id = selectedAddressId;
                    } else {
                        // Sử dụng form địa chỉ mới
                        orderData.full_name = document.getElementById('full_name')?.value.trim();
                        orderData.phone_number = document.getElementById('phone_number')?.value.trim();
                        orderData.phone = document.getElementById('phone_number')?.value
                            .trim(); // Cho tương thích PaymentController
                        orderData.email = document.getElementById('email')?.value.trim();
                        orderData.province_code = document.getElementById('province_id')?.value;
                        orderData.district_code = document.getElementById('district_id')?.value;
                        orderData.ward_code = document.getElementById('ward_id')?.value;
                        orderData.address_line1 = document.getElementById('address_line1')?.value.trim();
                        orderData.address = document.getElementById('address_line1')?.value
                            .trim(); // Cho tương thích PaymentController
                        orderData.save_address = document.getElementById('save-address-check')?.checked || false;
                        // Thêm address_system cho validation backend
                        orderData.address_system = 'old'; // Giả sử hệ thống cũ cho bây giờ
                    }

                    // Phương thức vận chuyển
                    const selectedShippingMethod = document.querySelector('input[name="shipping_method"]:checked');
                    if (selectedShippingMethod) {
                        orderData.shipping_method = selectedShippingMethod.dataset.name;
                        orderData.shipping_fee = parseInt(selectedShippingMethod.dataset.fee, 10) || 0;

                        // Thêm khung giờ giao hàng cho "Giao hàng của cửa hàng"
                        if (selectedShippingMethod.dataset.name === 'Giao hàng của cửa hàng') {
                            orderData.delivery_date = document.getElementById('delivery-date')?.value;
                            orderData.delivery_time_slot = document.getElementById('delivery-time-slot')?.value;
                        }
                    }
                } else {
                    // Phương thức nhận tại cửa hàng
                    orderData.pickup_full_name = document.getElementById('pickup_full_name')?.value.trim();
                    orderData.pickup_phone_number = document.getElementById('pickup_phone_number')?.value.trim();
                    orderData.pickup_email = document.getElementById('pickup_email')?.value.trim();
                    orderData.store_location_id = selectedStore?.id;
                    orderData.pickup_date = document.getElementById('pickup-date')?.value;
                    orderData.pickup_time_slot = document.getElementById('pickup-time-slot')?.value;
                    orderData.shipping_method = 'Nhận tại cửa hàng'; // Đặt shipping_method cho pickup
                    orderData.shipping_fee = 0;
                }

                // Thêm ghi chú nếu có
                const notesField = document.getElementById('notes');
                if (notesField) {
                    orderData.notes = notesField.value.trim();
                }

                // Xác định endpoint dựa trên loại đơn hàng
                const isBuyNow = {{ isset($is_buy_now) && $is_buy_now ? 'true' : 'false' }};
                const processUrl = isBuyNow ? '{{ route('buy-now.process') }}' :
                    '{{ route('payments.process') }}';
                console.log('Dữ liệu gửi đi:', JSON.stringify(orderData, null, 2));

                // Gửi đơn hàng qua AJAX
                fetch(processUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw {
                                    status: response.status,
                                    data: data
                                };
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Kiểm tra xem có URL thanh toán không (VNPay, MoMo, v.v.)
                            if (data.payment_url) {
                                window.location.href = data.payment_url;
                            } else if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                // COD hoặc các phương thức khác
                                window.location.href = '{{ route('payments.success') }}?order_id=' + data.order
                                    .id;
                            }
                        } else {
                            throw new Error(data.message || 'Có lỗi xảy ra khi đặt hàng');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Xử lý lỗi validation từ backend (status 422)
                        if (error.status === 422 && error.data && error.data.errors) {
                            handleBackendValidationErrors(error.data.errors);
                        } else if (error.data && error.data.message) {
                            alert('Có lỗi xảy ra: ' + error.data.message);
                        } else {
                            alert('Có lỗi xảy ra: ' + (error.message || 'Lỗi không xác định'));
                        }
                    })
                    .finally(() => {
                        // Bật lại nút
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.innerHTML = 'Đặt hàng';
                    });
            }

            // Xử lý lỗi validation từ backend
            function handleBackendValidationErrors(errors) {
                // Xóa các lỗi frontend hiện có trước
                document.querySelectorAll('.error-message').forEach(error => {
                    error.style.display = 'none';
                });
                document.querySelectorAll('.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });

                // Cũng xóa các lỗi cụ thể
                const shippingError = document.getElementById('shipping_method_error');
                if (shippingError) {
                    shippingError.style.display = 'none';
                }

                const paymentError = document.getElementById('payment_method_error');
                if (paymentError) {
                    paymentError.style.display = 'none';
                }

                const storeError = document.getElementById('store_location_error');
                if (storeError) {
                    storeError.style.display = 'none';
                }

                let firstErrorField = null;

                // Hiển thị lỗi từ backend
                Object.keys(errors).forEach(fieldName => {
                    const errorMessage = errors[fieldName][0]; // Lấy thông báo lỗi đầu tiên

                    // Xử lý đặc biệt cho shipping_method (nhóm radio)
                    if (fieldName === 'shipping_method') {
                        const errorElement = document.getElementById('shipping_method_error');
                        const shippingContainer = document.getElementById('shipping-methods-container');

                        if (errorElement) {
                            errorElement.querySelector('span').textContent = errorMessage;
                            errorElement.style.display = 'flex';

                            if (shippingContainer && !firstErrorField) {
                                firstErrorField = shippingContainer;
                            }
                        }
                        return;
                    }

                    // Xử lý đặc biệt cho payment_method (nhóm radio)
                    if (fieldName === 'payment_method') {
                        const errorElement = document.getElementById('payment_method_error');
                        const paymentSection = document.querySelector('.bg-white.rounded.shadow-sm p-4');

                        if (errorElement) {
                            errorElement.querySelector('span').textContent = errorMessage;
                            errorElement.style.display = 'flex';

                            if (paymentSection && !firstErrorField) {
                                firstErrorField = paymentSection;
                            }
                        }
                        return;
                    }

                    // Xử lý đặc biệt cho store_location_id (radio buttons)
                    if (fieldName === 'store_location_id') {
                        const errorElement = document.getElementById('store_location_error');
                        const storeSelect = document.getElementById('store-location-select');

                        if (errorElement) {
                            errorElement.querySelector('span').textContent = errorMessage;
                            errorElement.style.display = 'flex';

                            if (!firstErrorField) {
                                firstErrorField = storeLocationsContainer;
                            }
                        }
                        return;
                    }

                    // Xử lý trường thông thường
                    const errorElement = document.getElementById(fieldName + '_error');
                    const fieldElement = document.getElementById(fieldName);

                    if (errorElement && fieldElement) {
                        // Hiển thị lỗi từ backend
                        fieldElement.classList.add('is-invalid');
                        errorElement.querySelector('span').textContent = errorMessage;
                        errorElement.style.display = 'flex';

                        // Theo dõi trường lỗi đầu tiên để focus
                        if (!firstErrorField) {
                            firstErrorField = fieldElement;
                        }
                    }
                });

                // Focus vào trường lỗi đầu tiên
                if (firstErrorField) {
                    firstErrorField.focus();
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            // --- KHỞI TẠO GIAO DIỆN ---
            if (pickupDateInput) {
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + 7);

                const toYYYYMMDD = (date) => date.toISOString().split('T')[0];

                pickupDateInput.setAttribute('min', toYYYYMMDD(today));
                pickupDateInput.setAttribute('max', toYYYYMMDD(maxDate));
            }

            // Thiết lập date picker giao hàng với cùng ràng buộc
            if (deliveryDateInput) {
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + 7);

                const toYYYYMMDD = (date) => date.toISOString().split('T')[0];

                deliveryDateInput.setAttribute('min', toYYYYMMDD(today));
                deliveryDateInput.setAttribute('max', toYYYYMMDD(maxDate));
            }

            // Tải tỉnh/thành phố khi trang tải
            if (provinceSelect) {
                loadProvinces();
            }

            // Không cần tải dữ liệu store locations khi trang tải vì sẽ tải khi mở modal

            // Hàm hỗ trợ cho định dạng số
            function number_format(number) {
                return new Intl.NumberFormat('vi-VN').format(number);
            }

            // Hàm mở modal chọn cửa hàng
            function openStoreSelectionModal() {
                loadModalStoreProvinces();
                loadModalStoreLocations();
                const modal = new bootstrap.Modal(storeSelectionModal);
                modal.show();
            }

            // Hàm lấy danh sách tỉnh/thành phố có cửa hàng cho modal
            async function loadModalStoreProvinces() {
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
            }

            // Hàm lấy danh sách quận/huyện có cửa hàng theo tỉnh cho modal
            async function loadModalStoreDistricts(provinceCode) {
                try {
                    modalStoreDistrictSelect.innerHTML = '<option value="">Đang tải...</option>';
                    modalStoreDistrictSelect.disabled = true;

                    if (!provinceCode) {
                        modalStoreDistrictSelect.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                        modalStoreDistrictSelect.disabled = false;
                        return;
                    }

                    const response = await fetch(
                        `/api/store-locations/districts?province_code=${provinceCode}`);
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
            }

            // Hàm lấy danh sách cửa hàng cho modal
            async function loadModalStoreLocations(provinceCode = '', districtCode = '') {
                try {
                    modalStoreList.innerHTML =
                        '<p class="text-muted small text-center">Đang tải danh sách cửa hàng...</p>';

                    const params = new URLSearchParams();
                    if (provinceCode) params.append('province_code', provinceCode);
                    if (districtCode) params.append('district_code', districtCode);

                    const response = await fetch(`/api/store-locations/stores?${params.toString()}`);
                    const data = await response.json();

                    if (data.success && data.data.length > 0) {
                        storeCountText.textContent = `Có ${data.data.length} cửa hàng còn hàng`;

                        let html = '';
                        data.data.forEach(store => {
                            html += `
                                    <div class="border rounded p-3 mb-2 store-item" data-store-id="${store.id}">
                                        <div class="d-flex align-items-start">
                                            <input type="radio" name="modal_store_location" id="modal-store-${store.id}" value="${store.id}" class="form-check-input mt-1">
                                            <label for="modal-store-${store.id}" class="ms-3 flex-grow-1 cursor-pointer">
                                                <strong class="d-block text-dark">${store.name}</strong>
                                                <span class="d-block small text-muted">${store.address}</span>
                                                ${store.phone ? `<span class="d-block small text-muted"><i class="fas fa-phone me-1"></i>${store.phone}</span>` : ''}
                                                <span class="d-block small text-muted">${store.full_address}</span>
                                            </label>
                                        </div>
                                        {{-- <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="viewDirections('${store.full_address}')">
                                                <i class="fas fa-map-marker-alt me-1"></i>Xem chỉ đường
                                            </button>
                                        </div> --}}
                                    </div>
                                `;
                        });
                        modalStoreList.innerHTML = html;

                        // Thêm event listeners cho các radio buttons
                        document.querySelectorAll('input[name="modal_store_location"]').forEach(radio => {
                            radio.addEventListener('change', () => {
                                confirmStoreSelectionBtn.disabled = false;

                                // Cập nhật visual state cho các items
                                document.querySelectorAll('.store-item').forEach(item => {
                                    item.classList.remove('border-danger', 'bg-light');
                                });

                                const selectedItem = radio.closest('.store-item');
                                if (selectedItem) {
                                    selectedItem.classList.add('border-danger', 'bg-light');
                                }
                            });
                        });
                    } else {
                        storeCountText.textContent = 'Có 0 cửa hàng còn hàng';
                        modalStoreList.innerHTML =
                            '<p class="text-muted small text-center">Không có cửa hàng nào trong khu vực này.</p>';
                    }
                } catch (error) {
                    console.error('Error loading store locations:', error);
                    modalStoreList.innerHTML =
                        '<p class="text-danger small text-center">Lỗi khi tải danh sách cửa hàng.</p>';
                }
            }
            // Hàm xem chỉ đường
            // function viewDirections(address) {
            //     const encodedAddress = encodeURIComponent(address);
            //     window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
            // }
            // Hàm xác nhận chọn cửa hàng
            function confirmStoreSelection() {
                const selectedRadio = document.querySelector('input[name="modal_store_location"]:checked');
                if (selectedRadio) {
                    const storeId = selectedRadio.value;
                    const storeItem = selectedRadio.closest('.store-item');
                    const storeName = storeItem.querySelector('strong').textContent;
                    const storeAddress = storeItem.querySelector('span').textContent;
                    const storePhone = storeItem.querySelector('.fa-phone')?.parentElement?.textContent.trim() ||
                        '';

                    // Lưu thông tin cửa hàng đã chọn
                    selectedStore = {
                        id: storeId,
                        name: storeName,
                        address: storeAddress,
                        phone: storePhone
                    };

                    // Hiển thị cửa hàng đã chọn
                    selectedStoreName.textContent = storeName;
                    selectedStoreAddress.textContent = storeAddress;
                    selectedStorePhone.textContent = storePhone;

                    // Ẩn button chọn và hiển thị thông tin cửa hàng
                    selectStoreBtn.style.display = 'none';
                    selectedStoreDisplay.style.display = 'block';

                    // Ẩn lỗi nếu có
                    const storeError = document.getElementById('store_location_error');
                    if (storeError) {
                        storeError.style.display = 'none';
                    }

                    // Đóng modal
                    const modal = bootstrap.Modal.getInstance(storeSelectionModal);
                    modal.hide();
                }
            }
            // Các hàm validation
            function validateName(value) {
                // Chỉ cho phép chữ cái, khoảng trắng và ký tự tiếng Việt
                const nameRegex = /^[a-zA-ZÀ-ỹ\s]+$/;
                return value.trim() !== '' && nameRegex.test(value.trim());
            }

            function validatePhone(value) {
                // Định dạng số điện thoại Việt Nam: 10-11 chữ số, bắt đầu bằng 0
                const phoneRegex = /^0[0-9]{9,10}$/;
                return phoneRegex.test(value.replace(/\s/g, ''));
            }

            function validateEmail(value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value.trim());
            }

            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorDiv = document.getElementById(fieldId + '_error');

                if (field && errorDiv) {
                    field.classList.add('is-invalid');
                    errorDiv.querySelector('span').textContent = message;
                    errorDiv.style.display = 'flex';
                }
            }

            function hideError(fieldId) {
                const field = document.getElementById(fieldId);
                const errorDiv = document.getElementById(fieldId + '_error');

                if (field && errorDiv) {
                    field.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                }
            }

            function setupInputValidation() {
                // Validation các trường tên
                const nameFields = ['full_name', 'pickup_full_name'];
                nameFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function() {
                            const value = this.value;
                            if (value === '') {
                                showError(fieldId, 'Vui lòng nhập tên');
                            } else if (!validateName(value)) {
                                showError(fieldId, 'Vui lòng chỉ nhập chữ cái');
                            } else {
                                hideError(fieldId);
                            }
                        });

                        field.addEventListener('blur', function() {
                            if (this.value === '') {
                                showError(fieldId, 'Vui lòng nhập tên');
                            }
                        });

                        // Ngăn chặn nhập số và ký tự đặc biệt
                        field.addEventListener('keypress', function(e) {
                            const char = String.fromCharCode(e.which);
                            if (!/[a-zA-ZÀ-ỹ\s]/.test(char)) {
                                e.preventDefault();
                            }
                        });
                    }
                });

                // Validation các trường số điện thoại
                const phoneFields = ['phone_number', 'pickup_phone_number'];
                phoneFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function() {
                            const value = this.value;
                            if (value === '') {
                                showError(fieldId, 'Vui lòng nhập số điện thoại');
                            } else if (!validatePhone(value)) {
                                showError(fieldId, 'Vui lòng nhập đúng định dạng số điện thoại');
                            } else {
                                hideError(fieldId);
                            }
                        });

                        field.addEventListener('blur', function() {
                            if (this.value === '') {
                                showError(fieldId, 'Vui lòng nhập số điện thoại');
                            }
                        });

                        // Chỉ cho phép số
                        field.addEventListener('keypress', function(e) {
                            const char = String.fromCharCode(e.which);
                            if (!/[0-9]/.test(char) && e.which !== 8) {
                                e.preventDefault();
                            }
                        });
                    }
                });

                // Validation các trường email
                const emailFields = ['email', 'pickup_email'];
                emailFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function() {
                            const value = this.value;
                            if (value === '') {
                                showError(fieldId, 'Vui lòng nhập email');
                            } else if (!validateEmail(value)) {
                                showError(fieldId, 'Vui lòng nhập email hợp lệ');
                            } else {
                                hideError(fieldId);
                            }
                        });

                        field.addEventListener('blur', function() {
                            if (this.value === '') {
                                showError(fieldId, 'Vui lòng nhập email');
                            }
                        });
                    }
                });

                // Validation trường địa chỉ
                const addressField = document.getElementById('address_line1');
                if (addressField) {
                    addressField.addEventListener('input', function() {
                        const value = this.value;
                        if (value === '') {
                            showError('address_line1', 'Vui lòng nhập số nhà, tên đường');
                        } else if (value.trim().length < 5) {
                            showError('address_line1', 'Địa chỉ quá ngắn, vui lòng nhập đầy đủ');
                        } else {
                            hideError('address_line1');
                        }
                    });
                }

                // Validation các trường select
                const selectFields = ['province_id', 'district_id', 'ward_id'];
                selectFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('change', function() {
                            if (this.value === '') {
                                const label = this.labels[0]?.textContent.replace(' *', '');
                                showError(fieldId, `Vui lòng chọn ${label}`);
                            } else {
                                hideError(fieldId);
                            }
                        });
                    }
                });
            }

            document.getElementById('delivery-method-delivery').classList.add('selected');

            renderMainProductList();
            updateOrderInformation({
                shippingFee: null
            });
            setupUIForUserType();
            setupInputValidation();
            // Kết thúc
        });
    </script>
    @include('users.cart.layout.partials.modal')
@endsection
