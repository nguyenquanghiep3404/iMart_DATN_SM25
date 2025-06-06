<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- SEO Meta Tags -->
    <title>{{ config('app.name', 'iMart') }}</title>
    {{-- <title>Cartzilla | Electronics Store</title> --}}
    <meta name="description" content="Cartzilla - Multipurpose E-Commerce Bootstrap HTML Template">
    <meta name="keywords" content="online shop, e-commerce, online store, market, multipurpose, product landing, cart, checkout, ui kit, light and dark mode, bootstrap, html5, css3, javascript, gallery, slider, mobile, pwa">
    <meta name="author" content="Createx Studio">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Webmanifest + Favicon / App icons -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="{{asset('frontend/manifest.json')}}">
    <link rel="icon" type="image/png" href="{{ asset ('assets/users/app-icons/icon-32x32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset ('assets/users/app-icons/icon-180x180.png') }}">

    <!-- Theme switcher (color modes) -->
    <script src="{{ asset ('assets/users/js/theme-switcher.js')}}"></script>

    <!-- Preloaded local web font (Inter) -->
    <link rel="preload" href="{{ asset ('assets/users/fonts/inter-variable-latin.woff2') }}" as="font" type="font/woff2" crossorigin="">

    <!-- Font icons -->
    <link rel="preload" href="{{ asset ('assets/users/icons/cartzilla-icons.woff2') }}" as="font" type="font/woff2" crossorigin="">
    <link rel="stylesheet" href="{{ asset ('assets/users/icons/cartzilla-icons.min.css') }}">

    <!-- Vendor styles -->
    <link rel="stylesheet" href="{{ asset ('assets/users/vendor/swiper/swiper-bundle.min.css') }}">

    <!-- Bootstrap + Theme styles -->
    <link rel="preload" href="{{ asset ('assets/users/css/theme.min.css') }}" as="style">
    <link rel="preload" href="{{ asset ('assets/users/css/theme.rtl.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset ('assets/users/css/theme.min.css') }}" id="theme-styles">

    <!-- Customizer -->
    <script src="{{ asset ('assets/users/js/customizer.min.js') }}"></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Customizer offcanvas -->
    <!-- Navigation bar (Page header) -->
    @include('users.partials.header')

    <!-- Page content -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    <main class="content-wrapper">
    <div class="container py-5 mt-n2 mt-sm-0">
        <div class="row pt-md-2 pt-lg-3 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
            @include('users.partials.menu_profile')
            <div class="col-lg-9">
                <div class="ps-lg-3 ps-xl-0">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    </main>
    <!-- Page footer -->
    @include('users.partials.footer')

    <!-- Back to top button -->
    @include('users.partials.back_to_top_button')

    <!-- Vendor scripts -->
    <script src="{{ asset ('assets/users/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Bootstrap + Theme scripts -->
    <script src="{{ asset ('assets/users/js/theme.min.js') }}"></script>

    @stack('scripts') {{-- Cho phép các trang con thêm JS cụ thể --}}

</body>

</html>