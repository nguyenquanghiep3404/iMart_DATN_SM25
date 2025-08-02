<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
    <main class="content-wrapper bg-body">
        @yield('content')
    </main>

    <!-- Page footer -->
    @include('users.partials.footer')

    <!-- Back to top button -->
    @include('users.partials.back_to_top_button')

    <!-- Vendor scripts -->

    <!-- Bootstrap + Theme scripts -->
    <script src="{{ asset('assets/users/js/theme.min.js') }}"></script>

    @stack('scripts') {{-- Cho phép các trang con thêm JS cụ thể --}}
</body>

</html>
