<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>iMart Trang Quản Trị - @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('assets/users/app-icons/Bản sao của iMart.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/choices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/quill.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rangeslider.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/main.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
{{-- Updated body with Alpine.js data for sidebar toggle and persistence --}}

<body x-data="{
    sideMenu: localStorage.getItem('sideMenu') === 'true', // Load state from localStorage
    toggleSideMenu() {
        this.sideMenu = !this.sideMenu;
        localStorage.setItem('sideMenu', this.sideMenu); // Save state to localStorage
    }
}">
    <div class="tp-main-wrapper bg-slate-100 min-h-screen flex">
        @include('admin.partials.sidebar')

        <!-- Overlay for mobile -->
        <div class="fixed inset-0 bg-black/70 z-30 transition-all duration-300 print:hidden lg:hidden" x-show="sideMenu"
            x-transition.opacity @click="sideMenu = false">
        </div>

        <div class="tp-main-content flex-1 transition-all duration-300 min-h-screen"
            :class="sideMenu && window.innerWidth < 1024 ? 'overflow-hidden' : ''"
            :style="window.innerWidth >= 1024 ? (sideMenu ? 'margin-left: 300px;' : 'margin-left: 0;') : ''">
            @include('admin.partials.header')
            <main class="p-4 sm:p-6 lg:p-8"> {{-- Added padding for main content --}}
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('assets/admin/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
    <script src="{{ asset('assets/admin/js/media-library.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @stack('scripts')
</body>

</html>
