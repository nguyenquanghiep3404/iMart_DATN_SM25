@extends('auth.layouts.app')
@section('main')
<div class="d-lg-flex">

    <!-- Register form -->
    <!-- Cập nhật: Áp dụng layout tương tự trang đăng nhập -->
    <div class="d-flex flex-column justify-content-center min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">

        <!-- Logo -->
        <!-- Cập nhật: Sử dụng logo giống trang đăng nhập -->
        <header class="navbar px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4 justify-content-center">
            <a href="/">
                <img src="{{asset('assets\users\logo\logo-full.svg')}}" alt="Logo" style="height: 4rem;"></a> 
        </header>

        <!-- Tiêu đề -->
        <!-- Cập nhật: Áp dụng style tiêu đề từ trang đăng nhập -->
        <h1 class="h2 mb-2 text-center lg:text-start">Đăng ký tài khoản</h1>
        <p class="fs-sm text-body-secondary mb-3 mb-lg-4 text-center lg:text-start">Tạo tài khoản để tận hưởng các lợi ích của chúng tôi.</p>
        
        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
            @csrf

            <!-- Name -->
            <div class="position-relative mb-4">
                <label for="register-name" class="form-label">Họ và tên</label>
                <input type="text"
                    class="form-control form-control-lg @error('name') is-invalid @enderror"
                    id="register-name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name">
                <div class="invalid-tooltip bg-transparent py-0">
                    @error('name')
                        {{ $message }}
                    @else
                        Vui lòng nhập họ và tên của bạn.
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="position-relative mb-4">
                <label for="register-email" class="form-label">Email</label>
                <input type="email"
                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                    id="register-email"
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
            
            <!-- Phone Number -->
            <div class="position-relative mb-4">
                <label for="register-phone" class="form-label">Số điện thoại</label>
                <input type="text"
                    class="form-control form-control-lg @error('phone_number') is-invalid @enderror"
                    id="register-phone"
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
                <label for="register-password" class="form-label">Mật khẩu</label>
                <div class="password-toggle">
                    <input type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        id="register-password"
                        name="password"
                        minlength="8"
                        placeholder="Tối thiểu 8 ký tự"
                        required
                        autocomplete="new-password">
                    <div class="invalid-tooltip bg-transparent py-0">
                        @error('password')
                            {{ $message }}
                        @else
                            Mật khẩu phải có ít nhất 8 ký tự.
                        @enderror
                    </div>
                    <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                        <input type="checkbox" class="btn-check">
                    </label>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm-password" class="form-label">Xác nhận mật khẩu</label>
                <div class="password-toggle">
                    <input type="password"
                        class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                        id="confirm-password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password">
                    <div class="invalid-tooltip bg-transparent py-0">
                        @error('password_confirmation')
                            {{ $message }}
                        @else
                            Mật khẩu xác nhận không khớp.
                        @enderror
                    </div>
                    <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                        <input type="checkbox" class="btn-check">
                    </label>
                </div>
            </div>

            <!-- Privacy Policy -->
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="privacy" required>
                <label for="privacy" class="form-check-label">
                    Tôi đã đọc và chấp nhận <a class="text-dark-emphasis" href="#!">Chính sách bảo mật</a>
                </label>
            </div>

            <!-- Nút Đăng ký -->
            <!-- Cập nhật: Sử dụng style nút giống trang đăng nhập -->
            <button type="submit" class="btn btn-lg btn-dark w-100">
                Đăng ký
            </button>
        </form>

        <!-- Divider -->
        <!-- Cập nhật: Sử dụng divider giống trang đăng nhập -->
        <div class="d-flex align-items-center my-4">
            <hr class="w-100 m-0">
            <span class="text-body-emphasis fw-medium text-nowrap mx-4">Hoặc</span>
            <hr class="w-100 m-0">
        </div>

        <!-- Social login -->
        <!-- Cập nhật: Sử dụng nút Google login giống trang đăng nhập -->
        <div class="d-flex flex-column flex-sm-row gap-3">
            <a href="{{ route('auth.google') }}" class="btn btn-lg btn-outline-secondary w-100 d-flex justify-content-center align-items-center">
                <svg class="w-5 h-5 me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="width: 20px; height: 20px;"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.574l6.19,5.238C39.901,36.62,44,30.631,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path></svg>
                <span>Đăng ký với Google</span>
            </a>
        </div>

        <!-- Link đăng nhập -->
        <!-- Cập nhật: Chuyển link xuống dưới cùng -->
        <div class="text-center mt-4">
            Bạn đã có tài khoản?
            <a class="nav-link text-decoration-underline p-0 d-inline-block ms-1" href="{{ route('login') }}">Đăng nhập</a>
        </div>
    </div>

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
                                    <h3 class="h6 pt-2 my-2">Mua với giá ưu đãi</h3>
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
