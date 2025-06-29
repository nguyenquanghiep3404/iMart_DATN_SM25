@extends('auth.layouts.app')
@section('main')
<div class="d-lg-flex">

    <!-- Login form + Footer -->
    <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">

        <!-- Logo -->
        <header class="navbar px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4">
            <a href="index.html" class="navbar-brand pt-0">
                <span class="d-flex flex-shrink-0 text-primary me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36">
                        <path d="M36 18.01c0 8.097-5.355 14.949-12.705 17.2a18.12 18.12 0 0 1-5.315.79C9.622 36 2.608 30.313.573 22.611.257 21.407.059 20.162 0 18.879v-1.758c.02-.395.059-.79.099-1.185.099-.908.277-1.817.514-2.686C2.687 5.628 9.682 0 18 0c5.572 0 10.551 2.528 13.871 6.517 1.502 1.797 2.648 3.91 3.359 6.201.494 1.659.771 3.436.771 5.292z" fill="currentColor"></path>
                        <g fill="#fff">
                            <path d="M17.466 21.624c-.514 0-.988-.316-1.146-.829-.198-.632.138-1.303.771-1.501l7.666-2.469-1.205-8.254-13.317 4.621a1.19 1.19 0 0 1-1.521-.75 1.19 1.19 0 0 1 .751-1.521l13.89-4.818c.553-.197 1.166-.138 1.64.158a1.82 1.82 0 0 1 .85 1.284l1.344 9.183c.138.987-.494 1.994-1.482 2.33l-7.864 2.528-.375.04zm7.31.138c-.178-.632-.85-1.007-1.482-.81l-5.177 1.58c-2.331.79-3.28.02-3.418-.099l-6.56-8.412a4.25 4.25 0 0 0-4.406-1.758l-3.122.987c-.237.889-.415 1.777-.514 2.686l4.228-1.363a1.84 1.84 0 0 1 1.857.81l6.659 8.551c.751.948 2.015 1.323 3.359 1.323.909 0 1.857-.178 2.687-.474l5.078-1.54c.632-.178 1.008-.829.81-1.481z"></path>
                            <use href="#czlogo"></use>
                            <use href="#czlogo" x="8.516" y="-2.172"></use>
                        </g>
                        <defs>
                            <path id="czlogo" d="M18.689 28.654a1.94 1.94 0 0 1-1.936 1.935 1.94 1.94 0 0 1-1.936-1.935 1.94 1.94 0 0 1 1.936-1.935 1.94 1.94 0 0 1 1.936 1.935z"></path>
                        </defs>
                    </svg>
                </span>
                Cartzilla
            </a>
        </header>

        <h1 class="h2 mt-auto">Đăng ký tài khoản</h1>
        <div class="nav fs-sm mb-4">
            Bạn đã có tài khoản?
            <a class="nav-link text-decoration-underline p-0 ms-2" href="{{ route('login') }}">Đăng nhập</a>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
            @csrf

            <!-- Name -->
            <div class="position-relative mb-4">
                <input type="text"
                    class="form-control form-control-lg @error('name') is-invalid @enderror"
                    placeholder="Tên của bạn"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name">
                <div class="invalid-tooltip bg-transparent py-0">
                    @error('name')
                    {{ $message }}
                    @else
                    Vui lòng nhập tên của bạn.
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="position-relative mb-4">
                <input type="email"
                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                    placeholder="Email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username">
                <div class="invalid-tooltip bg-transparent py-0">
                    @error('email')
                    {{ $message }}
                    @else
                    Vui lòng nhập địa chỉ email hợp lệ.
                    @enderror
                </div>
            </div>

            <div class="position-relative mb-4">
                <input
                    type="text"
                    class="form-control form-control-lg @error('phone_number') is-invalid @enderror"
                    placeholder="Số điện thoại"
                    name="phone_number"
                    value="{{ old('phone_number') }}"
                    required
                    autocomplete="tel">
                <div class="invalid-tooltip bg-transparent py-0">
                    @error('phone_number')
                    {{ $message }}
                    @else
                    Vui lòng nhập số điện thoại hợp lệ.
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="password-toggle">
                    <input type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        placeholder="Mật khẩu(8 ký tự trở lên)"
                        name="password"
                        required
                        autocomplete="new-password">
                    <div class="invalid-tooltip bg-transparent py-0">
                        @error('password')
                        {{ $message }}
                        @else
                        Vui lòng nhập mật khẩu của bạn! (ít nhất 8 ký tự)
                        @enderror
                    </div>
                    <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                        <input type="checkbox" class="btn-check">
                    </label>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <div class="password-toggle">
                    <input type="password"
                        class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                        placeholder="Xác nhận mật khẩu"
                        name="password_confirmation"
                        required
                        autocomplete="new-password">
                    <div class="invalid-tooltip bg-transparent py-0">
                        @error('password_confirmation')
                        {{ $message }}
                        @else
                        Vui lòng xác nhận mật khẩu của bạn!
                        @enderror
                    </div>
                    <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                        <input type="checkbox" class="btn-check">
                    </label>
                </div>
            </div>

            <!-- Login Redirect -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-check me-2">
                    <input type="checkbox" class="form-check-input" id="remember-register">
                    <label for="remember-register" class="form-check-label">Nhớ mật khẩu</label>
                </div>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="privacy" required>
                <label for="privacy" class="form-check-label">
                    Tôi đã đọc và chấp nhận <a class="text-dark-emphasis" href="#!">Chính sách bảo mật</a>
                </label>
            </div>

            <button type="submit" class="btn btn-lg btn-primary w-100">Đăng ký</button>
        </form>


        <!-- Divider -->
        <div class="d-flex align-items-center my-4">
            <hr class="w-100 m-0">
            <span class="text-body-emphasis fw-medium text-nowrap mx-4">hoặc tiếp tục với</span>
            <hr class="w-100 m-0">
        </div>

        <!-- Social login -->
        <div class="d-flex flex-column flex-sm-row gap-3 pb-4 mb-3 mb-lg-4">
            <a href="{{ route('auth.google') }}" class="btn btn-lg btn-outline-secondary w-100 px-2">
                <i class="ci-google ms-1 me-1"></i>
                Google
            </a>
        </div>

        <!-- Footer -->
        <footer class="mt-auto">
            <div class="nav mb-4">
                <a class="nav-link text-decoration-underline p-0" href="help-topics-v1.html">Cần giúp đỡ?</a>
            </div>
            <p class="fs-xs mb-0">
                © Mọi quyền được bảo lưu. Được thực hiện bởi <span class="animate-underline"><a class="animate-target text-dark-emphasis text-decoration-none" href="https://createx.studio/" target="_blank" rel="noreferrer">iMart Dev</a></span>
            </p>
        </footer>
    </div>

    <!-- Benefits section that turns into offcanvas on screens < 992px wide (lg breakpoint) -->
    <div class="offcanvas-lg offcanvas-end w-100 py-lg-4 ms-auto" id="benefits" style="max-width: 1034px">
        <div class="offcanvas-header justify-content-end position-relative z-2 p-3">
            <button type="button" class="btn btn-icon btn-outline-dark text-dark border-dark bg-transparent rounded-circle d-none-dark" data-bs-dismiss="offcanvas" data-bs-target="#benefits" aria-label="Close">
                <i class="ci-close fs-lg"></i>
            </button>
            <button type="button" class="btn btn-icon btn-outline-dark text-light border-light bg-transparent rounded-circle d-none d-inline-flex-dark" data-bs-dismiss="offcanvas" data-bs-target="#benefits" aria-label="Close">
                <i class="ci-close fs-lg"></i>
            </button>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-lg-none">
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
        </div>
        <div class="offcanvas-body position-relative z-2 d-lg-flex flex-column align-items-center justify-content-center h-100 pt-2 px-3 p-lg-0">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-lg-block">
                <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
                <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
            </div>
            <div class="position-relative z-2 w-100 text-center px-md-2 p-lg-5">
                <h2 class="h4 pb-3">Lợi ích của tài khoản iMart</h2>
                <div class="mx-auto" style="max-width: 790px">
                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-md-4 g-lg-3 g-xl-4">
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-mail position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Đăng ký sản phẩm yêu thích của bạn</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-settings position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Xem và quản lý đơn hàng và danh sách mong muốn của bạn</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-gift position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Kiếm phần thưởng cho những lần mua hàng trong tương lai</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-percent position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Nhận ưu đãi và giảm giá độc quyền</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-heart position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Tạo nhiều danh sách mong muốn</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 bg-transparent border-0">
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-25 border border-white border-opacity-50 rounded-4 d-none-dark"></span>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded-4 d-none d-block-dark" style="--cz-bg-opacity: .05"></span>
                                <div class="card-body position-relative z-2">
                                    <div class="d-inline-flex position-relative text-info p-3">
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-pill d-none-dark"></span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary rounded-pill d-none d-block-dark"></span>
                                        <i class="ci-pie-chart position-relative z-2 fs-4 m-1"></i>
                                    </div>
                                    <h3 class="h6 pt-2 my-2">Thanh toán mua hàng theo từng đợt</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection