<!DOCTYPE html>
<html lang="en">
<!-- Mirrored from html.hixstudio.net/ebazer/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 25 May 2025 14:06:02 GMT -->

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Bán sản phẩm iMart</title>
    <link rel="shortcut icon" href="assets/admin/img/logo/favicon.png" type="image/x-icon">

    <!-- css links -->
    <link rel="stylesheet" href="{{asset('assets/admin/css/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/choices.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/apexcharts.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/quill.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/rangeslider.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/main.css') }}">
</head>

<body>
    <div class="tp-main-wrapper bg-slate-100 h-screen" x-data="{ sideMenu: false }">
        @include('admin.partials.sidebar')
        <!-- <div class="fixed top-0 left-0 w-full h-full z-40 bg-black/70 transition-all duration-300" :class="sideMenu ? 'visible opacity-1' : '  invisible opacity-0 '" x-on:click="sideMenu = ! sideMenu"> -->

            <div class="tp-main-content lg:ml-[250px] xl:ml-[300px] w-[calc(100% - 300px)]" x-data="{ searchOverlay: false }">
                @include('admin.partials.header')
                @yield('content')
                @include('admin.partials.footer')
            </div>
        <!-- </div> -->
    </div>


    <!-- Scripts -->
    <script src="{{ asset('assets/admin/js/vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('assets/admin/js/alpine.js') }}"></script>
    <script src="{{ asset('assets/admin/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/quill.js') }}"></script>
    <script src="{{ asset('assets/admin/js/rangeslider.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
</body>

</html>