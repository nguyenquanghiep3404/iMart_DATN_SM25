<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pwa="true">
<!-- Mirrored from cartzilla-html.createx.studio/account-signup.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 07 May 2025 16:34:47 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->

<head>
    <meta charset="utf-8">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title>Cartzilla | Account - Sign Up</title>
    <meta name="description" content="Cartzilla - Multipurpose E-Commerce Bootstrap HTML Template">
    <meta name="keywords" content="online shop, e-commerce, online store, market, multipurpose, product landing, cart, checkout, ui kit, light and dark mode, bootstrap, html5, css3, javascript, gallery, slider, mobile, pwa">
    <meta name="author" content="Createx Studio">

    <!-- Webmanifest + Favicon / App icons -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/users/app-icons/icon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="assets/users/app-icons/icon-180x180.png">

    <!-- Theme switcher (color modes) -->
    <script src="assets/users/js/theme-switcher.js"></script>

    <!-- Preloaded local web font (Inter) -->
    <link rel="preload" href="assets/users/fonts/inter-variable-latin.woff2" as="font" type="font/woff2" crossorigin="">

    <!-- Font icons -->
    <link rel="preload" href="assets/users/icons/cartzilla-icons.woff2" as="font" type="font/woff2" crossorigin="">
    <link rel="stylesheet" href="assets/users/icons/cartzilla-icons.min.css">

    <!-- Bootstrap + Theme styles -->
    <link rel="preload" href="assets/users/css/theme.min.css" as="style">
    <link rel="preload" href="assets/users/css/theme.rtl.min.css" as="style">
    <link rel="stylesheet" href="assets/users/css/theme.min.css" id="theme-styles">

    <!-- Customizer -->
    <script src="assets/users/js/customizer.min.js"></script>
</head>


<!-- Body -->

<body>

    <!-- Customizer offcanvas -->
    <div class="offcanvas offcanvas-end" id="customizer" tabindex="-1">
        <div class="offcanvas-header border-bottom">
            <h4 class="h5 offcanvas-title">Tùy chỉnh chủ đề</h4>
            <button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <!-- Customizer settings -->
            <div class="customizer-collapse collapse show" id="customizerSettings">

                <!-- Colors -->
                <div class="pb-4 mb-2">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ci-paint text-body-tertiary fs-5 me-2"></i>
                        <h5 class="fs-lg mb-0">Màu sắc</h5>
                    </div>
                    <div class="row row-cols-2 g-3" id="theme-colors">
                        <div class="col">
                            <h6 class="fs-sm mb-2">Sơ đẳng</h6>
                            <div class="color-swatch d-flex border rounded gap-3 p-2" id="theme-primary" data-color-labels="[&quot;theme-primary&quot;, &quot;primary&quot;, &quot;primary-rgb&quot;]">
                                <input type="text" class="form-control bg-transparent border-0 rounded-0 p-1" value="#f55266">
                                <label for="primary" class="ratio ratio-1x1 flex-shrink-0 w-100 cursor-pointer rounded-circle" style="max-width: 38px; background-color: #f55266"></label>
                                <input type="color" class="visually-hidden" id="primary" value="#f55266">
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="fs-sm mb-2">Thành công</h6>
                            <div class="color-swatch d-flex border rounded gap-3 p-2" id="theme-success" data-color-labels="[&quot;theme-success&quot;, &quot;success&quot;, &quot;success-rgb&quot;]">
                                <input type="text" class="form-control bg-transparent border-0 rounded-0 p-1" value="#33b36b">
                                <label for="success" class="ratio ratio-1x1 flex-shrink-0 w-100 cursor-pointer rounded-circle" style="max-width: 38px; background-color: #33b36b"></label>
                                <input type="color" class="visually-hidden" id="success" value="#33b36b">
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="fs-sm mb-2">Cảnh báo</h6>
                            <div class="color-swatch d-flex border rounded gap-3 p-2" id="theme-warning" data-color-labels="[&quot;theme-warning&quot;, &quot;warning&quot;, &quot;warning-rgb&quot;]">
                                <input type="text" class="form-control bg-transparent border-0 rounded-0 p-1" value="#fc9231">
                                <label for="warning" class="ratio ratio-1x1 flex-shrink-0 w-100 cursor-pointer rounded-circle" style="max-width: 38px; background-color: #fc9231"></label>
                                <input type="color" class="visually-hidden" id="warning" value="#fc9231">
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="fs-sm mb-2">Nguy hiểm</h6>
                            <div class="color-swatch d-flex border rounded gap-2 p-2" id="theme-danger" data-color-labels="[&quot;theme-danger&quot;, &quot;danger&quot;, &quot;danger-rgb&quot;]">
                                <input type="text" class="form-control bg-transparent border-0 rounded-0 p-1" value="#f03d3d">
                                <label for="danger" class="ratio ratio-1x1 flex-shrink-0 w-100 cursor-pointer rounded-circle" style="max-width: 38px; background-color: #f03d3d"></label>
                                <input type="color" class="visually-hidden" id="danger" value="#f03d3d">
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="fs-sm mb-2">Thông tin</h6>
                            <div class="color-swatch d-flex border rounded gap-2 p-2" id="theme-info" data-color-labels="[&quot;theme-info&quot;, &quot;info&quot;, &quot;info-rgb&quot;]">
                                <input type="text" class="form-control bg-transparent border-0 rounded-0 p-1" value="#2f6ed5">
                                <label for="info" class="ratio ratio-1x1 flex-shrink-0 w-100 cursor-pointer rounded-circle" style="max-width: 38px; background-color: #2f6ed5"></label>
                                <input type="color" class="visually-hidden" id="info" value="#2f6ed5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Direction -->
                <div class="pb-4 mb-2">
                    <div class="d-flex align-items-center pb-1 mb-2">
                        <i class="ci-sort text-body-tertiary fs-lg me-2" style="transform: rotate(90deg)"></i>
                        <h5 class="fs-lg mb-0">Phương hướng</h5>
                    </div>
                    <div class="d-flex align-items-center justify-content-between border rounded p-3">
                        <div class="me-3">
                            <h6 class="mb-1">RTL</h6>
                            <p class="fs-sm mb-0">Thay đổi hướng văn bản</p>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input type="checkbox" class="form-check-input" role="switch" id="rtl-switch">
                        </div>
                    </div>
                    <div class="alert alert-info p-2 mt-2 mb-0">
                        <div class="d-flex text-body-emphasis fs-xs py-1 pe-1">
                            <i class="ci-info text-info fs-lg mb-2 mb-sm-0" style="margin-top: .125rem"></i>
                            <div class="ps-2">Để chuyển hướng văn bản trên trang web của bạn từ LTR sang RTL, vui lòng tham khảo hướng dẫn chi tiết được cung cấp trong phần có liên quan của tài liệu của chúng tôi.</div>
                        </div>
                    </div>
                </div>

                <!-- Border width -->
                <div class="pb-4 mb-2">
                    <div class="d-flex align-items-center pb-1 mb-2">
                        <i class="ci-menu text-body-tertiary fs-lg me-2"></i>
                        <h5 class="fs-lg mb-0">Độ rộng đường viền, px</h5>
                    </div>
                    <div class="slider-input d-flex align-items-center gap-3 border rounded p-3" id="border-input">
                        <input type="range" class="form-range" min="0" max="10" step="1" value="1">
                        <input type="number" class="form-control" id="border-width" min="0" max="10" value="1" style="max-width: 5.5rem">
                    </div>
                </div>

                <!-- Rounding -->
                <div class="d-flex align-items-center pb-1 mb-2">
                    <i class="ci-maximize text-body-tertiary fs-lg me-2"></i>
                    <h5 class="fs-lg mb-0">Làm tròn, rem</h5>
                </div>
                <div class="slider-input d-flex align-items-center gap-3 border rounded p-3">
                    <input type="range" class="form-range" min="0" max="5" step=".05" value="0.5">
                    <input type="number" class="form-control" id="border-radius" min="0" max="5" step=".05" value="0.5" style="max-width: 5.5rem">
                </div>
            </div>

            <!-- Customizer code -->
            <div class="customizer-collapse collapse" id="customizerCode">
                <div class="nav mb-3">
                    <a class="nav-link animate-underline fs-base p-0" href=".html" data-bs-toggle="collapse" aria-expanded="true" aria-controls="customizerSettings customizerCode">
                        <i class="ci-chevron-left fs-lg ms-n1 me-1"></i>
                        <span class="animate-target">Quay lại cài đặt</span>
                    </a>
                </div>
                <p class="fs-sm pb-1">Để áp dụng các kiểu được cung cấp cho trang web của bạn, hãy bao gồm chúng trong một <code>&lt;style&gt;</code> gắn thẻ và chèn thẻ này vào <code>&lt;head&gt;</code> gắn thẻ một phần của tài liệu HTML của bạn sau liên kết sau đến bảng định kiểu chính chèn thẻ này vào:<br><code>&lt;link href="assets/users/css/theme.min.css"&gt;</code></p>
                <div class="position-relative bg-body-tertiary rounded overflow-hidden pt-3">
                    <div class="position-absolute top-0 start-0 w-100 p-3">
                        <button type="button" class="btn btn-sm btn-outline-dark w-100" data-copy-text-from="#generated-styles" data-done-label="Code copied">
                            <i class="ci-copy fs-sm me-1"></i>
                            Sao chép code
                        </button>
                    </div>
                    <pre class="text-wrap bg-transparent border-0 fs-xs text-body-emphasis p-4 pt-5" id="generated-styles"></pre>
                </div>
            </div>
        </div>

        <!-- Offcanvas footer (Action buttons) -->
        <div class="offcanvas-header border-top gap-3 d-none" id="customizer-btns">
            <button type="button" class="btn btn-lg btn-secondary w-100 fs-sm" id="customizer-reset">
                <i class="ci-trash fs-lg me-2 ms-n1"></i>
                Cài lại
            </button>
            <button class="btn btn-lg btn-primary hiding-collapse-toggle w-100 fs-sm collapsed" type="button" data-bs-toggle="collapse" data-bs-target=".customizer-collapse" aria-expanded="false" aria-controls="customizerSettings customizerCode">
                <i class="ci-code fs-lg me-2 ms-n1"></i>
                Show code
            </button>
        </div>
    </div>


    <!-- Page content -->
    <main class="content-wrapper w-100 px-3 ps-lg-5 pe-lg-4 mx-auto" style="max-width: 1920px">
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

                <h1 class="h2 mt-auto">Chào mừng trở lại</h1>
                <div class="nav fs-sm mb-3 mb-lg-4">
                    Bạn chưa có tài khoản?
                    <a class="nav-link text-decoration-underline p-0 ms-2" href="{{ route('register') }}">Đăng ký</a>
                </div>
                <!-- <div class="nav fs-sm mb-4 d-lg-none">
                    <span class="me-2">Uncertain about creating an account?</span>
                    <a class="nav-link text-decoration-underline p-0" href="#benefits" data-bs-toggle="offcanvas" aria-controls="benefits">Explore the Benefits</a>
                </div> -->

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
                        <label for="register-password" class="form-label">Mật khẩu</label>
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
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="save-pass" name="remember">
                        <label for="save-pass" class="form-check-label">Nhớ mật khẩu</label>
                    </div>
        

                    <!-- Forgot password & Submit -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        @if (Route::has('password.request'))
                        <a class="text-sm text-primary" href="{{ route('password.request') }}">
                            Quên mật khẩu?
                        </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-lg btn-primary w-100">
                        Đăng nhập
                        <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                    </button>
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
                        © All rights reserved. Made by <span class="animate-underline"><a class="animate-target text-dark-emphasis text-decoration-none" href="https://createx.studio/" target="_blank" rel="noreferrer">Createx Studio</a></span>
                    </p>
                </footer>
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
    </main>


    <!-- Customizer toggle -->
    <div class="floating-buttons position-fixed top-50 end-0 z-sticky me-3 me-xl-4 pb-4">
        <a class="btn btn-sm btn-outline-secondary text-uppercase bg-body rounded-pill shadow animate-rotate ms-2 me-n5" href="#customizer" style="font-size: .625rem; letter-spacing: .05rem;" data-bs-toggle="offcanvas" role="button" aria-controls="customizer">
            Tùy chỉnh
            <i class="ci-settings fs-base ms-1 me-n2 animate-target"></i>
        </a>
    </div>


    <!-- Bootstrap + Theme scripts -->
    <script src="assets/users/js/theme.min.js"></script>


</body>
<!-- Mirrored from cartzilla-html.createx.studio/account-signup.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 07 May 2025 16:34:47 GMT -->

</html>