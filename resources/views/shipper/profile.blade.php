@extends('layouts.shipper')

@section('title', 'Tài khoản của tôi')

@section('content')
    <header class="page-header p-5 bg-white border-b flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Tài khoản của tôi</h1>
    </header>

    <main class="page-content p-5 space-y-6">
        <div class="flex flex-col items-center space-y-4">
            <img src="{{ $shipper->avatar_url ?? 'https://placehold.co/100x100/4F46E5/FFFFFF?text=' . strtoupper(substr($shipper->name, 0, 1)) }}"
                 alt="Avatar"
                 class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ $shipper->name }}</h2>
                <p class="text-gray-500">{{ $shipper->phone_number }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm">
             <a href="#" class="flex justify-between items-center text-gray-700 hover:bg-gray-50 p-4 rounded-t-lg border-b">
                <span>Đổi mật khẩu</span>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </a>
             <a href="#" class="flex justify-between items-center text-gray-700 hover:bg-gray-50 p-4 rounded-b-lg">
                <span>Trung tâm trợ giúp</span>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </a>
        </div>

        <div class="pt-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-red-100 text-red-600 font-bold py-3 rounded-lg flex items-center justify-center space-x-2 transition hover:bg-red-200">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
   </main>
@endsection
