<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Giao Hàng') - {{ config('app.name') }}</title>

    {{-- Sử dụng TailwindCSS và Font Awesome qua CDN giống file HTML gốc của bạn --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f4f7fa;
        }
        .page-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
            max-width: 448px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .page-content {
            flex-grow: 1;
            overflow-y: auto;
            background-color: #f4f7fa;
        }
        .page-header, .page-footer, .bottom-nav {
            flex-shrink: 0;
            background-color: white;
            z-index: 10;
        }
        .nav-link.active {
            color: #4f46e5; /* indigo-600 */
        }
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: none; /* Mặc định ẩn */
            justify-content: center; align-items: flex-end;
            z-index: 50;
        }
        .modal-overlay.is-visible {
            display: flex; /* Hiện khi có class */
        }
        .modal-content {
            background: white; border-radius: 1.5rem 1.5rem 0 0;
            width: 100%; max-width: 448px;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
        }
        .modal-overlay.is-visible .modal-content {
            transform: translateY(0);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page-wrapper">

        @yield('content')

    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
