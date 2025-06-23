@extends('auth.layouts.app')
@section('main')
<main class="content-wrapper w-100 px-3 ps-lg-5 pe-lg-4 mx-auto" style="max-width: 1920px">
  <div class="d-lg-flex">

    <!-- Left form -->
    <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">
      <!-- Logo -->
      <header class="navbar align-items-center px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4">
        <a href="{{ url('/') }}" class="navbar-brand pt-0">
          <span class="d-flex flex-shrink-0 text-primary me-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor"><path d="..."/></svg>
          </span>
          iMart
        </a>
        <div class="nav">
          <a class="nav-link fs-base animate-underline p-0" href="{{ route('login') }}">
            <i class="ci-chevron-left fs-lg ms-n1 me-1"></i>
            <span class="animate-target">Quay lại đăng nhập</span>
          </a>
        </div>
      </header>

      <h1 class="h3 mt-auto">Quên mật khẩu</h1>
      <p class="pb-2 pb-md-3">Nhập email đã đăng ký, chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>

      <!-- Session Status -->
      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>
      @endif

      <!-- Form -->
      <form method="POST" action="{{ route('password.email') }}" class="needs-validation pb-4 mb-3 mb-lg-4" novalidate>
        @csrf

        <div class="position-relative mb-4">
          <i class="ci-mail position-absolute top-50 start-0 translate-middle-y fs-lg ms-3"></i>
          <input id="email" name="email" type="email"
                 class="form-control form-control-lg form-icon-start @error('email') is-invalid @enderror"
                 placeholder="Địa chỉ email" value="{{ old('email') }}" required autofocus>
          @error('email')
            <div class="invalid-feedback d-block">
              {{ $message }}
            </div>
          @enderror
        </div>

        <button type="submit" class="btn btn-lg btn-primary w-100">
          Gửi liên kết đặt lại mật khẩu
        </button>
      </form>

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

    <!-- Right Image (large screen only) -->
    <div class="d-none d-lg-block w-100 py-4 ms-auto" style="max-width: 1034px">
      <div class="d-flex flex-column justify-content-end h-100 rounded-5 overflow-hidden">
        <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
        <div class="ratio position-relative z-2" style="--cz-aspect-ratio: calc(1030 / 1032 * 100%)">
          <img src="{{ asset('assets/admin/img/bg/login-bg.jpg') }}" alt="Ảnh nền">
        </div>
      </div>
    </div>

  </div>

@endsection