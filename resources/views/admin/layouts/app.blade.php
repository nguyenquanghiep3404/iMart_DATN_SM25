<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
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
    <div class="tp-main-wrapper bg-slate-100 min-h-screen" x-data="{ sideMenu: false }">
        @include('admin.partials.sidebar')
        
        <div class="fixed top-0 left-0 w-full h-full z-40 bg-black/70 transition-all duration-300 print:hidden" :class="sideMenu ? 'visible opacity-1' : 'invisible opacity-0'" x-on:click="sideMenu = false"></div>

        {{-- Gợi ý cải thiện: Dùng w-full và margin responsive thay vì width cố định --}}
         <div class="tp-main-content lg:ml-[250px] xl:ml-[300px] w-[calc(100% - 300px)]" x-data="{ searchOverlay: false }">
            @include('admin.partials.header')
            
            <main>
                @yield('content')
            </main>
            
            {{-- Bạn có thể giữ hoặc bỏ footer tùy theo thiết kế --}}
            {{-- @include('admin.partials.footer') --}}
        </div>
    </div>


    {{-- Các script chung cho toàn bộ trang --}}
    <script src="{{ asset('assets/admin/js/alpine.js') }}"></script>
    <script src="{{ asset('assets/admin/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
    {{-- Bỏ các script không cần thiết trên mọi trang (vd: chart) để tối ưu --}}
    {{-- <script src="{{ asset('assets/admin/js/chart.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script> --}}
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
    
    {{-- Nơi để các trang con chèn script riêng --}}
    @stack('scripts')
</body>

</html>