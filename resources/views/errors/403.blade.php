@extends($layout)

@section('title', 'Trang không có quyền')

@section('content')
<div class="py-20 px-4 bg-gray-100 min-h-screen">
    <div class="text-center max-w-2xl mx-auto">
        <div class="inline-block bg-red-100 p-5 rounded-full mb-4">
            <div class="inline-block bg-red-200 p-4 rounded-full">
                <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <h1 class="text-6xl font-extrabold text-red-600">403</h1>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Truy cập bị từ chối</h2>
        <p class="text-gray-600 mt-4">
            Bạn không có quyền truy cập vào trang này. Nếu đây là lỗi, hãy liên hệ quản trị viên.
        </p>

        <div class="mt-8 flex justify-center items-center space-x-4">
            <button onclick="window.history.back()"
                class="px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </button>

            @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Về trang tổng quan
                </a>
            @else
                <a href="{{ url('/') }}"
                   class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Về trang chủ
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
