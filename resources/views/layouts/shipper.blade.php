<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shipper Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- PHẦN STYLE BỊ THIẾU CỦA BẠN BẮT ĐẦU TỪ ĐÂY --}}
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 64px;
            --primary-color-shipper: #28a745;
        }
        body { font-family: 'Public Sans', sans-serif; background-color: #f4f6f9; margin: 0; }
        .app-wrapper { display: flex; min-height: 100vh; }
        .main-sidebar {
            width: var(--sidebar-width); background-color: #fff; border-right: 1px solid #e0e0e0;
            display: flex; flex-direction: column; position: fixed;
            top: 0; left: 0; height: 100%; z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-brand {
            display: flex; align-items: center; justify-content: center;
            height: var(--header-height); font-size: 1.8rem; font-weight: 700;
            color: #0052cc; text-decoration: none;
        }
        .sidebar-menu { list-style: none; padding: 1rem; margin: 0; flex-grow: 1; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex; align-items: center; padding: 10px 16px;
            color: #455560; text-decoration: none; border-radius: 8px;
            font-weight: 500; transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #e7f1ff; color: #0052cc; }
        .sidebar-menu a .icon { margin-right: 16px; width: 24px; height: 24px; }
        .content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }
        .main-header {
            height: var(--header-height); background-color: #fff; border-bottom: 1px solid #e0e0e0;
            padding: 0 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .mobile-menu-toggle { display: none; background: none; border: none; cursor: pointer; }
        .main-header .user-info { display: flex; align-items: center; }
        .main-header .user-info span { margin-right: 1rem; font-weight: 600; }
        .main-header .logout-form button {
            background-color: #0052cc; color: white; border: none;
            padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;
        }
        .main-content { padding: 2rem; }

        /* === Responsive cho Mobile (Phần quan trọng bị thiếu) === */
        @media (max-width: 992px) {
            .main-sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            .main-sidebar.is-visible {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .mobile-menu-toggle {
                display: block;
            }
            .sidebar-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background-color: rgba(0,0,0,0.5); z-index: 999;
                display: none;
            }
            .sidebar-overlay.is-visible {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div id="app" class="app-wrapper">
        <aside class="main-sidebar">
            <a href="{{ route('shipper.dashboard') }}" class="sidebar-brand">Shipper Panel</a>
            <ul class="sidebar-menu">
                <li><a href="{{ route('shipper.dashboard') }}" class="active"><img src="https://img.icons8.com/ios/50/dashboard-layout.png" class="icon" alt="dashboard"/> Bảng điều khiển</a></li>
                <li><a href="#"><img src="https://img.icons8.com/ios/50/time-machine.png" class="icon" alt="history"/> Lịch sử giao hàng</a></li>
                <li><a href="#"><img src="https://img.icons8.com/ios/50/cash-in-hand.png" class="icon" alt="cod"/> Đối soát COD</a></li>
                <li><a href="{{ route('profile.edit') }}"><img src="https://img.icons8.com/ios/50/user-male-circle.png" class="icon" alt="profile"/> Tài khoản của tôi</a></li>
            </ul>
        </aside>

        <div class="sidebar-overlay"></div>

        <div class="content-wrapper">
            <header class="main-header">
                <button class="mobile-menu-toggle">
                    <svg height="24px" viewBox="0 0 32 32" width="24px"><path d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2 s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2 S29.104,22,28,22z"/></svg>
                </button>
                @auth
                    <div class="user-info">
                        <span>Chào, {{ Auth::user()->name }}</span>
                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                @endauth
            </header>
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

    {{-- PHẦN SCRIPT BỊ THIẾU CỦA BẠN Ở ĐÂY --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.main-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (menuToggle && sidebar && overlay) {
                // Sự kiện khi bấm nút hamburger
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('is-visible');
                    overlay.classList.toggle('is-visible');
                });

                // Sự kiện khi bấm vào lớp phủ mờ (để đóng menu)
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('is-visible');
                    overlay.classList.remove('is-visible');
                });
            }
        });
    </script>
    @stack('page-scripts')
</body>
</html>
