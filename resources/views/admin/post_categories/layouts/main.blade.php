<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from html.hixstudio.net/ebazer/reviews.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 25 May 2025 14:06:45 GMT -->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iMart Trang Quản Trị - @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('assets/admin/img/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/choices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/quill.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rangeslider.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/main.css') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

</head>
<body>

    <div class="tp-main-wrapper bg-slate-100 h-screen" x-data="{ sideMenu: false }">
        @include('admin.partials.sidebar')

        <div class="fixed top-0 left-0 w-full h-full z-40 bg-black/70 transition-all duration-300" :class="sideMenu ? 'visible opacity-1' : '  invisible opacity-0 '" x-on:click="sideMenu = ! sideMenu"> </div>

        <div class="tp-main-content lg:ml-[250px] xl:ml-[300px] w-[calc(100% - 300px)]"  x-data="{ searchOverlay: false }">

            @include('admin.partials.header')

            <div class="body-content px-8 py-8 bg-slate-100">
                <div class="flex justify-between mb-10">
                    <div class="page-title">
                        <h3 class="mb-0 text-[28px]">Reviews</h3>
                        <ul class="text-tiny font-medium flex items-center space-x-3 text-text3">
                            <li class="breadcrumb-item text-muted">
                                <a href="product-list.html" class="text-hover-primary"> Home</a>
                            </li>
                            <li class="breadcrumb-item flex items-center">
                                <span class="inline-block bg-text3/60 w-[4px] h-[4px] rounded-full"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Reviews List</li>            
                        </ul>
                    </div>
                </div>

                <!-- table -->
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/admin/js/alpine.js') }}"></script>
    <script src="{{ asset('assets/admin/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/quill.js') }}"></script>
    <script src="{{ asset('assets/admin/js/rangeslider.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
    
</body>

<!-- Mirrored from html.hixstudio.net/ebazer/reviews.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 25 May 2025 14:06:46 GMT -->
</html>