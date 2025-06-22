@extends('auth.layouts.app')

@section('main')
  <div class="d-lg-flex">

    <!-- Xác minh + Footer -->
    <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">

      <!-- Logo -->
      <header class="navbar align-items-center px-0 pb-4 mt-n2 mt-sm-0 mb-2 mb-md-3 mb-lg-4">
        <a href="/" class="navbar-brand pt-0">
          <span class="d-flex flex-shrink-0 text-primary me-2">
            <!-- SVG logo -->
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36">
              <path d="M36 18.01c0 8.097-5.355 14.949-12.705 17.2a18.12 18.12 0 0 1-5.315.79C9.622 36 2.608 30.313.573 22.611.257 21.407.059 20.162 0 18.879v-1.758c.02-.395.059-.79.099-1.185.099-.908.277-1.817.514-2.686C2.687 5.628 9.682 0 18 0c5.572 0 10.551 2.528 13.871 6.517 1.502 1.797 2.648 3.91 3.359 6.201.494 1.659.771 3.436.771 5.292z" fill="currentColor"></path>
              <g fill="#fff"><path d="..."></path></g>
            </svg>
          </span>
          Cartzilla
        </a>
      </header>

      <!-- Nội dung xác minh -->
      <h1 class="h2 mt-auto">Xác minh email</h1>
      <p class="pb-2 pb-md-3 text-muted">
        Cảm ơn bạn đã đăng ký! Vui lòng xác minh địa chỉ email của bạn bằng cách nhấp vào liên kết đã được gửi đến hộp thư của bạn. Nếu bạn không nhận được email, bạn có thể yêu cầu gửi lại bên dưới.
      </p>

      @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success text-center">
          Một liên kết xác minh mới đã được gửi đến địa chỉ email bạn đã đăng ký.
        </div>
      @endif

      <!-- Form -->
      <div class="pb-4 mb-3 mb-lg-4">
        <form method="POST" action="{{ route('verification.send') }}" class="d-grid gap-2 mb-3">
          @csrf
          <button type="submit" class="btn btn-primary btn-lg w-100">
            Gửi lại email xác minh
          </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-link w-100 text-muted text-decoration-underline">
            Đăng xuất
          </button>
        </form>
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

    <!-- Cover image -->
    <div class="d-none d-lg-block w-100 py-4 ms-auto" style="max-width: 1034px">
      <div class="d-flex flex-column justify-content-end h-100 rounded-5 overflow-hidden">
        <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
        <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
        <div class="ratio position-relative z-2" style="--cz-aspect-ratio: calc(1030 / 1032 * 100%)">
          <img src="{{ asset('assets/users/img/account/cover.png') }}" alt="Cover">
        </div>
      </div>
    </div>

  </div>
@endsection
