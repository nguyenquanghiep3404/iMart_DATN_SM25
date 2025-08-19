<footer class="footer position-relative bg-dark">
    <span class="position-absolute top-0 start-0 w-100 h-100 bg-body d-none d-block-dark"></span>
    <div class="container position-relative z-1 pt-sm-2 pt-md-3 pt-lg-4" data-bs-theme="dark">

        <!-- Vietnamese Footer Sections -->
        <div class="py-5">
            <div class="row">
                <!-- Left Column: Logo & Social Media & Hotline -->
                <div class="col-lg-3 col-md-4 mb-4">
                    <!-- Social Media Section -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <a href="/" class="flex-shrink-0">
                                <img class="h-12 sm:h-14 w-auto" src="{{ asset('assets/users/logo/logo-full.svg') }}"
                                    alt="Logo">
                            </a>
                            <div class="bg-secondary mx-2" style="width: 1px; height: 40px;"></div>
                            <a href="/" class="flex-shrink-0">
                                <img class="h-12 sm:h-14 w-auto" src="{{ asset('assets/users/logo/Apple.png') }}"
                                    alt="Logo">
                            </a>
                        </div>
                        <h6 class="text-white fw-bold mb-3">KẾT NỐI VỚI IMART SHOP</h6>
                        <div class="d-flex gap-2 mb-4">
                            <a href="/" class="flex-shrink-0">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ asset('assets/users/logo/Facebook.webp') }}" alt="Logo">
                            </a>
                            <a href="/" class="flex-shrink-0">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ asset('assets/users/logo/Zalo.webp') }}" alt="Logo">
                            </a>
                            <a href="/" class="flex-shrink-0">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ asset('assets/users/logo/Youtube.jpg') }}" alt="Logo">
                            </a>
                            <a href="/" class="flex-shrink-0">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ asset('assets/users/logo/Tiktok.jpg') }}" alt="Logo">
                            </a>
                        </div>
                    </div>

                    <!-- About iMart Section -->
                    <div>
                        <p class="text-body-secondary small">
                            Năm 2025, iMart trở thành đại lý ủy quyền của Apple.
                            Chúng tôi phát triển chuỗi cửa hàng tiêu chuẩn và Apple Mono Store
                            nhằm mang đến trải nghiệm tốt nhất về sản phẩm và dịch vụ của Apple
                            cho người dùng Việt Nam.
                        </p>
                    </div>
                </div>

                <!-- About Us Section -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <h6 class="text-white fw-bold mb-3">VỀ CHÚNG TÔI</h6>
                    <ul class="nav flex-column gap-2">
                        <li><a class="nav-link text-body-secondary p-0 small" href="{{ route('users.about') }}">Giới
                                thiệu</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Tin tức khuyến mại</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Giới thiệu máy đổi trả</a>
                        </li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Hướng dẫn mua hàng & thanh
                                toán online</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Đại lý ủy quyền và TTBH ủy
                                quyền của Apple</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Tra cứu bảo hành</a>
                        </li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Câu hỏi thường gặp</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Đánh giá chất lượng khiếu nại</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Tin tức</a></li>
                    </ul>
                </div>

                <!-- Policies Section -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <h6 class="text-white fw-bold mb-3">CHÍNH SÁCH</h6>
                    <ul class="nav flex-column gap-2">
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách bảo hành</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách đổi trả</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách bảo mật</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách hủy giao dịch</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách giao hàng</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách dịch vụ</a></li>
                        <li><a class="nav-link text-body-secondary p-0 small" href="#!">Chính sách chương trình khách hàng thân thiết</a></li>
                    </ul>
                </div>

                <!-- Hotline Section moved here -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <h6 class="text-white fw-bold mb-3">TỔNG ĐÀI MIỄN PHÍ</h6>
                    <div class="text-body-secondary small">
                        <div class="mb-2">
                            <span class="fw-medium">Tư vấn mua hàng (Miễn phí)</span><br>
                            <span class="fw-bold text-white">1800.6601</span> <span>(Nhánh 1)</span>
                        </div>
                        <div class="mb-2">
                            <span class="fw-medium">Hỗ trợ kỹ thuật</span><br>
                            <span class="fw-bold text-white">1800.6601</span> <span>(Nhánh 2)</span>
                        </div>
                        <div class="mb-2">
                            <span class="fw-medium">Góp ý, khiếu nại và tiếp nhận cảnh báo vi phạm</span><br>
                            <span class="fw-bold text-white">1800.6616</span> <span>(8h00 - 22h00)</span>
                        </div>
                    </div>
                </div>

                {{-- Temporarily hidden Payment Support Section --}}
                {{-- <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <h6 class="text-white fw-bold mb-3">HỖ TRỢ THANH TOÁN</h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/visa-dark-mode.svg') }}"
                                alt="Visa" class="img-fluid">
                        </div>
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/mastercard.svg') }}" alt="Mastercard"
                                class="img-fluid">
                        </div>
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/amex.svg') }}" alt="American Express"
                                class="img-fluid">
                        </div>
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/apple-pay-dark-mode.svg') }}"
                                alt="Apple Pay" class="img-fluid">
                        </div>
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/google-pay-dark-mode.svg') }}"
                                alt="Google Pay" class="img-fluid">
                        </div>
                        <div class="col-4">
                            <img src="{{ asset('assets/users/img/payment-methods/paypal-dark-mode.svg') }}"
                                alt="PayPal" class="img-fluid">
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>

        <!-- Category / tag links -->
        {{-- <div class="d-flex flex-column gap-3 pb-3 pb-md-4 pb-lg-5 mt-n2 mt-sm-n4 mt-lg-0 mb-4">
          <ul class="nav align-items-center text-body-tertiary gap-2">
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Computers</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Smartphones</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">TV, Video</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Speakers</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Cameras</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Printers</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Video Games</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Headphones</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Wearable</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">HDD/SSD</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Smart Home</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Apple Devices</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Tablets</a>
            </li>
          </ul>
          <ul class="nav align-items-center text-body-tertiary gap-2">
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Monitors</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Scanners</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Servers</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Heating and Cooling</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">E-readers</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Data Storage</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Networking</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Power Strips</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Plugs and Outlets</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Detectors and Sensors</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="#!">Accessories</a>
            </li>
            <li class="px-1">/</li>
            <li class="animate-underline">
              <a class="nav-link fw-normal p-0 animate-target" href="{{ route('users.terms') }}">Terms &amp; conditions</a>
            </li>
          </ul>
        </div> --}}

        <!-- Copyright & BCT Logo -->
        <div class="border-top py-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="text-body-secondary fs-sm mb-0">
                        © 2025 iMart. Tất cả các quyền được bảo lưu.
                        Địa chỉ: Tòa nhà FPT Polytechnic, 13 Trịnh Văn Bô, Phường Xuân Phương, TP Hà Nội<br>
                        GPĐKKD số 0123456789 do Sở KH & ĐT TP. Hà Nội cấp ngày 10/08/2025
                        Điện thoại: (028) 7302 3456 Email: imartshop@imart.com
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="" target="_blank" class="d-inline-block" style="max-width: 150px;">
                        <img src="{{ asset('assets/users/logo/BCT.png') }}" alt="Đã thông báo Bộ Công Thương"
                            class="img-fluid">
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
