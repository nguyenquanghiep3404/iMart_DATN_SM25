<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('users.partials.head')
</head>

<body>
    <!-- Customizer offcanvas -->
    @include('users.partials.ai_chatbot')

    <!-- Shopping cart offcanvas -->
    @include('users.partials.cart_offcanvas')

    <!-- Navigation bar (Page header) -->
    @include('users.partials.header')

    <!-- Page content -->
    <main class="content-wrapper">
        <div class="container py-5 mt-n2 mt-sm-0">
            <div class="row pt-md-2 pt-lg-3 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
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

    <!-- Page footer -->
    @include('users.partials.footer')

    <!-- Back to top button -->
    @include('users.partials.back_to_top_button')

    <!-- Vendor scripts -->
    <script src="{{ asset('assets/users/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Bootstrap + Theme scripts -->
    <script src="{{ asset('assets/users/js/theme.min.js') }}"></script>

    @stack('scripts') {{-- Cho phép các trang con thêm JS cụ thể --}}
</body>

</html>
