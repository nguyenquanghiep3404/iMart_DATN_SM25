<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Giao Hàng') - {{ config('app.name') }}</title>

    {{-- Sử dụng CDN để đảm bảo CSS luôn được tải đúng --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html, body {
            height: 100%;
            overflow: hidden; /* Ngăn body cuộn */
        }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f4f7fa;
        }
        .page-wrapper {
            display: flex;
            flex-direction: column; /* Sắp xếp theo chiều dọc */
            height: 100vh; /* Chiếm toàn bộ chiều cao màn hình */
            width: 100%;
            max-width: 448px; /* Giả lập kích thước điện thoại */
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .main-content-area {
            flex-grow: 1; /* Cho phép phần nội dung chiếm hết không gian */
            overflow-y: auto; /* Chỉ phần này được cuộn */
            background-color: #f4f7fa;
        }
        .bottom-navigation {
            flex-shrink: 0; /* Ngăn thanh nav co lại */
            height: 70px;
            background-color: white;
            border-top: 1px solid #e5e7eb;
            z-index: 20;
        }
        .nav-link.active {
            color: #4f46e5; /* Màu indigo-600 */
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page-wrapper">
        {{-- Phần nội dung chính có thể cuộn --}}
        <main class="main-content-area">
            @yield('content')
        </main>

        {{-- Thanh điều hướng dưới cùng, nằm ngoài khu vực cuộn --}}
        <nav class="bottom-navigation flex justify-around items-center">
             <a href="{{ route('shipper.dashboard') }}" class="nav-link text-center {{ request()->routeIs('shipper.dashboard') ? 'active' : 'text-gray-500' }}">
                <i class="fas fa-motorcycle fa-lg"></i>
                <span class="block text-xs font-semibold mt-1">Hôm nay</span>
            </a>
             <a href="{{ route('shipper.stats') }}" class="nav-link text-center {{ request()->routeIs('shipper.stats') ? 'active' : 'text-gray-500' }}">
                <i class="fas fa-chart-line fa-lg"></i>
                <span class="block text-xs font-semibold mt-1">Thống kê</span>
            </a>
             <a href="{{ route('shipper.history') }}" class="nav-link text-center {{ request()->routeIs('shipper.history') ? 'active' : 'text-gray-500' }}">
                <i class="fas fa-history fa-lg"></i>
                <span class="block text-xs font-semibold mt-1">Lịch sử</span>
            </a>
             <a href="{{ route('shipper.profile') }}" class="nav-link text-center {{ request()->routeIs('shipper.profile') ? 'active' : 'text-gray-500' }}">
                <i class="fas fa-user-circle fa-lg"></i>
                <span class="block text-xs font-semibold mt-1">Tài khoản</span>
            </a>
        </nav>
    </div>

    {{-- Nơi đặt modal để nó không ảnh hưởng đến layout --}}
    @stack('modals')
    @stack('scripts')
</body>
</html>
