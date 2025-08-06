<!-- resources/views/serials/serial_lookup.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Tra cứu Serial/IMEI')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
    <!-- Header -->
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Tra cứu Serial / IMEI</h1>
        <p class="text-gray-600 mt-1">Nhập số Serial/IMEI để xem toàn bộ lịch sử và trạng thái của sản phẩm.</p>
    </header>

    <!-- Search Section -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-8">
        <form method="POST" action="{{ route('admin.serial.lookup') }}">
            @csrf
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" name="serial_number" id="serial-input" placeholder="Nhập Serial hoặc IMEI..." 
                       class="flex-grow block w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono" 
                       value="{{ old('serial_number', $serial_number ?? '') }}">
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-sm hover:bg-indigo-700 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Tra cứu
                </button>
            </div>
            @error('serial_number')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
            @if (session('error'))
                <p class="text-red-600 text-sm mt-2">{{ session('error') }}</p>
            @endif
        </form>
    </div>

    <!-- Result Section -->
    @if (isset($serialData))
        <div id="result-section">
            <!-- Product & Status Summary -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <!-- Product Info -->
                    <div class="flex items-center space-x-4">
                        <img class="h-16 w-16 rounded-lg object-cover" src="{{ $serialData['summary']['image'] ?? 'https://placehold.co/100x100/e2e8f0/475569?text=No+Image' }}" alt="{{ $serialData['summary']['name'] }}">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $serialData['summary']['name'] }}</h2>
                            <p class="text-gray-500 text-sm">SKU: {{ $serialData['summary']['sku'] }}</p>
                        </div>
                    </div>
                    <!-- Status Info -->
                    <div class="mt-4 sm:mt-0 text-left sm:text-right">
                        <p class="text-sm font-medium text-gray-500">Trạng thái hiện tại</p>
                        <p class="font-bold text-lg text-green-600 flex items-center justify-start sm:justify-end">
                            <span class="{{ $serialData['summary']['status_class'] }} text-sm font-medium px-3 py-1 rounded-full">{{ $serialData['summary']['status'] }}</span>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Tại: <span class="font-semibold">{{ $serialData['summary']['location'] }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Timeline / History -->
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Vòng đời sản phẩm</h3>
                <ol class="relative border-l border-gray-300">
                    @foreach ($serialData['events'] as $event)
                        <li class="mb-10 ml-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 {{ $event['icon_bg'] }} rounded-full -left-3 ring-8 ring-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $event['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $event['icon'] }}" />
                                </svg>
                            </span>
                            <h4 class="flex items-center mb-1 text-base font-semibold text-gray-900">
                                {{ $event['type'] }}
                                @if ($event['is_latest'])
                                    <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-3">Giao dịch gần nhất</span>
                                @endif
                            </h4>
                            <time class="block mb-2 text-sm font-normal leading-none text-gray-500">{{ $event['date'] }}</time>
                            <p class="text-sm text-gray-600">{!! $event['description'] !!}</p>
                        </li>
                    @endforeach
                    @if (empty($serialData['events']))
                        <li class="ml-6">
                            <p class="text-sm text-gray-600">Chưa có sự kiện nào được ghi nhận.</p>
                        </li>
                    @endif
                </ol>
            </div>
        </div>
    @else
        <!-- Initial Prompt / Not Found -->
        <div id="initial-prompt" class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 3h.01" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">{{ session('error') ? 'Không tìm thấy kết quả' : 'Bắt đầu tra cứu' }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ session('error') ? 'Vui lòng kiểm tra lại số Serial/IMEI và thử lại.' : 'Nhập số Serial hoặc IMEI vào ô bên trên để xem thông tin.' }}</p>
        </div>
    @endif
</div>
@endsection