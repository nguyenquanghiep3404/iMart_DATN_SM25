@extends('auth.layouts.app')
@section('main')
<div class="d-lg-flex">

    <!-- Login form + Footer -->
    <!-- Cập nhật: Thêm 'justify-content-center' để căn giữa nội dung theo chiều dọc -->
    <div class="d-flex flex-column justify-content-center min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">

        <!-- Logo -->
        <header class="navbar px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4 justify-content-center">
            <a href="/">
                <img src="{{asset('assets\users\logo\logo-full.svg')}}" alt="Logo" style="height: 4rem;"></a> 
            
        </header>

        <!-- Tiêu đề được đẩy lên trên và bỏ mt-auto -->
        <!-- Cập nhật: Thêm text-center cho mobile và lg:text-start cho desktop -->
        <h1 class="h2 mb-2 text-center lg:text-start">Chào mừng trở lại</h1>
        <!-- Thêm dòng chữ "Vui lòng đăng nhập để tiếp tục" -->
        <p class="fs-sm text-body-secondary mb-3 mb-lg-4 text-center lg:text-start">Vui lòng đăng nhập để tiếp tục.</p>
        
        <form class="needs-validation" novalidate method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="position-relative mb-4">
                <label for="register-email" class="form-label">Email</label>
                <input
                    type="email"
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
                    Nhập địa chỉ email hợp lệ!
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <!-- Label và link Quên mật khẩu được gộp vào một dòng -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="register-password" class="form-label mb-0">Mật khẩu</label>
                    @if (Route::has('password.request'))
                    <!-- Cập nhật: Chữ nhỏ hơn nữa (fs-xs) và bỏ gạch chân -->
                    <a class="nav-link fs-xs text-decoration-none p-0" href="{{ route('password.request') }}">
                        Quên mật khẩu?
                    </a>
                    @endif
                </div>
                <div class="password-toggle">
                    <input
                        type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        id="register-password"
                        name="password"
                        minlength="8"
                        placeholder="Tối thiểu 8 ký tự"
                        required
                        autocomplete="current-password">
                    <div class="invalid-tooltip bg-transparent py-0">
                        @error('password')
                        {{ $message }}
                        @else
                        Mật khẩu không đáp ứng đủ tiêu chí yêu cầu!
                        @enderror
                    </div>
                    <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                        <input type="checkbox" class="btn-check">
                    </label>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="save-pass" name="remember">
                <label for="save-pass" class="form-check-label">Nhớ mật khẩu</label>
            </div>

            <!-- Nút Đăng nhập đã được cập nhật màu sắc và bỏ icon -->
            <button type="submit" class="btn btn-lg btn-dark w-100">
                Đăng nhập
            </button>
        </form>

        <!-- Divider -->
        <div class="d-flex align-items-center my-4">
            <hr class="w-100 m-0">
            <!-- Cập nhật văn bản cho giống mẫu -->
            <span class="text-body-emphasis fw-medium text-nowrap mx-4">Hoặc</span>
            <hr class="w-100 m-0">
        </div>

        <!-- Social login -->
        <div class="d-flex flex-column flex-sm-row gap-3">
            <!-- Nút Google đã được cập nhật màu sắc và icon SVG -->
            <a href="{{ route('auth.google') }}" class="btn btn-lg btn-outline-secondary w-100 d-flex justify-content-center align-items-center">
                <svg class="w-5 h-5 me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="width: 20px; height: 20px;"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.574l6.19,5.238C39.901,36.62,44,30.631,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path></svg>
                <span>Đăng nhập với Google</span>
            </a>
        </div>

        <!-- Link đăng ký được chuyển xuống đây -->
        <div class="text-center mt-4">
            Bạn chưa có tài khoản?
            <a class="nav-link text-decoration-underline p-0 d-inline-block ms-1" href="{{ route('register') }}">Đăng ký</a>
        </div>

        <!-- Footer đã được xoá -->
    </div>

    <!-- Cover image visible on screens > 992px wide (lg breakpoint) -->
    <div class="d-none d-lg-block w-100 py-4 ms-auto" style="max-width: 1034px">
        <div class="d-flex flex-column justify-content-end h-100 rounded-5 overflow-hidden">
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
            <div class="ratio position-relative z-2" style="--cz-aspect-ratio: calc(1030 / 1032 * 100%)">
                <img src="assets/users/img/account/cover.png" alt="Girl">
            </div>
        </div>
    </div>
</div>
@endsection
