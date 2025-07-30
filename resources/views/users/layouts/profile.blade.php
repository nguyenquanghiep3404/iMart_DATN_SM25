<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'iMart') }}</title>
    {{-- <title>Cartzilla | Electronics Store</title> --}}
    <meta name="description" content="Cartzilla - Multipurpose E-Commerce Bootstrap HTML Template">
    <meta name="keywords" content="online shop, e-commerce, online store, market, multipurpose, product landing, cart, checkout, ui kit, light and dark mode, bootstrap, html5, css3, javascript, gallery, slider, mobile, pwa">
    <meta name="author" content="Createx Studio">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="{{asset('frontend/manifest.json')}}">
    <link rel="icon" type="image/png" href="{{ asset ('assets/users/app-icons/icon-32x32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset ('assets/users/app-icons/icon-180x180.png') }}">

    <script src="{{ asset ('assets/users/js/theme-switcher.js')}}"></script>

    <link rel="preload" href="{{ asset ('assets/users/fonts/inter-variable-latin.woff2') }}" as="font" type="font/woff2" crossorigin="">

    <link rel="preload" href="{{ asset ('assets/users/icons/cartzilla-icons.woff2') }}" as="font" type="font/woff2" crossorigin="">
    <link rel="stylesheet" href="{{ asset ('assets/users/icons/cartzilla-icons.min.css') }}">

    <link rel="stylesheet" href="{{ asset ('assets/users/vendor/swiper/swiper-bundle.min.css') }}">

    <link rel="preload" href="{{ asset ('assets/users/css/theme.min.css') }}" as="style">
    <link rel="preload" href="{{ asset ('assets/users/css/theme.rtl.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset ('assets/users/css/theme.min.css') }}" id="theme-styles">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    {{-- THÊM DÒNG NÀY VÀO ĐÂY ĐỂ NHÚNG CSS CỦA CROPPER.JS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="{{ asset ('assets/users/js/customizer.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

<body>
    <!-- Customizer offcanvas -->
    @include('users.partials.ai_chatbot')

    @include('users.partials.cart_offcanvas')

    @include('users.partials.header')

    <main class="content-wrapper">
        <div class="container py-5 mt-n2 mt-sm-0">
            <div class="row pt-md-2 pt-lg-3 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
                {{-- Đảm bảo Cropper.js JavaScript chỉ được nhúng một lần trong menu_profile hoặc ở đây --}}
                @include('users.partials.menu_profile')
                {{-- Nội dung chính của trang --}}
                <div class="col-lg-9">
                    <div class="ps-lg-3 ps-xl-0">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('users.partials.footer')

    @include('users.partials.back_to_top_button')

    <script src="{{ asset('assets/users/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/users/js/theme.min.js') }}"></script>

    {{-- KHI NHÚNG CROPPER.JS Ở ĐÂY, HÃY XÓA NÓ TRONG menu_profile.blade.php NẾU ĐÃ CÓ --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>



    @stack('scripts') {{-- Cho phép các trang con thêm JS cụ thể --}}
</body>

</html>
