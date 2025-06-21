@extends('auth.layouts.app')
@section('main')
<div class="d-lg-flex">

    <!-- Form + Footer -->
    <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">

        <!-- Logo -->
        <header class="navbar align-items-center px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4">
            <a href="index.html" class="navbar-brand pt-0">
                <!-- SVG logo here -->
            </a>
            <div class="nav">
                <a class="nav-link fs-base animate-underline p-0" href="{{ route('login') }}">
                    <i class="ci-chevron-left fs-lg ms-n1 me-1"></i>
                    <span class="animate-target">Quay lại đăng nhập</span>
                </a>
            </div>
        </header>

        <!-- Tiêu đề -->
        <h1 class="h2 mt-auto">Đặt lại mật khẩu</h1>
        <p class="pb-2 pb-md-3">Nhập email của bạn và mật khẩu mới để đặt lại mật khẩu.</p>

        <!-- Form Laravel -->
        <form method="POST" action="{{ route('password.store') }}" class="needs-validation pb-4 mb-3 mb-lg-4" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email -->
            <div class="position-relative mb-4">
                <i class="ci-mail position-absolute top-50 start-0 translate-middle-y fs-lg ms-3"></i>
                <input name="email" type="email" value="{{ old('email', $request->email) }}"
                    class="form-control form-control-lg form-icon-start @error('email') is-invalid @enderror"
                    placeholder="Email của bạn" required>
                @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Mật khẩu -->
            <div class="mb-4">
                <input name="password" type="password"
                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                    placeholder="Mật khẩu mới" required>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="mb-4">
                <input name="password_confirmation" type="password"
                    class="form-control form-control-lg"
                    placeholder="Xác nhận mật khẩu mới" required>
                @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-lg btn-primary w-100">Đặt lại mật khẩu</button>
        </form>

        <!-- Footer -->
        <footer class="mt-auto">
            <div class="nav mb-4">
                <a class="nav-link text-decoration-underline p-0" href="#">Cần trợ giúp?</a>
            </div>
            <p class="fs-xs mb-0">
                © All rights reserved. Made by
                <span class="animate-underline">
                    <a class="animate-target text-dark-emphasis text-decoration-none" href="https://createx.studio/" target="_blank">Createx Studio</a>
                </span>
            </p>
        </footer>
    </div>

    <!-- Cover image -->
    <div class="d-none d-lg-block w-100 py-4 ms-auto" style="max-width: 1034px">
        <div class="d-flex flex-column justify-content-end h-100 rounded-5 overflow-hidden">
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
            <div class="ratio position-relative z-2" style="--cz-aspect-ratio: calc(1030 / 1032 * 100%)">
                <img src="{{ asset('assets/admin/img/bg/login-bg.jpg') }}" alt="Ảnh minh họa">
            </div>
        </div>
    </div>
</div>
@endsection