<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pwa="true">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'iMart')</title>
    <meta name="description" content="@yield('meta_description', 'iMart - Apple store e-commerce')">
    <meta name="keywords" content="iMart, Apple, e-commerce, online store">
    <meta name="author" content="iMart Dev">

    <!-- Favicon -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/users/app-icons/icon-32x32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/users/app-icons/icon-180x180.png') }}">

    <!-- Fonts & Icons -->
    <link rel="preload" href="{{ asset('assets/users/fonts/inter-variable-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('assets/users/icons/cartzilla-icons.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/users/icons/cartzilla-icons.min.css') }}">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/users/css/theme.min.css') }}" id="theme-styles">

    <!-- Scripts -->
    <script src="{{ asset('assets/users/js/theme-switcher.js') }}"></script>
    <script src="{{ asset('assets/users/js/customizer.min.js') }}"></script>
</head>

<body>
    @include('auth.partials.customizer')

    <main class="content-wrapper w-100 px-3 ps-lg-5 pe-lg-4 mx-auto" style="max-width: 1920px">
        @yield('main')
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