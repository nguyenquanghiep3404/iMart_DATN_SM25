<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Truy cập bị từ chối</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <div class="inline-block bg-red-100 p-5 rounded-full mb-4">
            <div class="inline-block bg-red-200 p-4 rounded-full">
                {{-- Icon Khiên và Chấm than --}}
                <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-6xl md:text-8xl font-extrabold text-red-600">403</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2">Truy cập bị từ chối</h2>

        <p class="text-gray-600 mt-4 max-w-md mx-auto">
            Rất tiếc, bạn không có quyền để truy cập vào trang hoặc thực hiện hành động này.
            Vui lòng liên hệ quản trị viên nếu bạn cho rằng đây là một sự nhầm lẫn.
        </p>

        <div class="mt-8 flex justify-center items-center space-x-4">
            {{-- Nút Quay lại --}}
            <button onclick="window.history.back()" class="flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại
            </button>

            {{-- Nút Về trang tổng quan --}}
            @auth
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Về trang tổng quan
                </a>
            @else
                <a href="{{ url('/') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Về trang chủ
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
