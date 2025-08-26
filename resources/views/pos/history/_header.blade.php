<header class="mb-8">
    <div class="flex items-center mb-4">
        {{-- Giả sử route để quay lại màn hình POS là 'pos.dashboard.index' --}}
        <a href="{{ route('pos.dashboard.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại màn hình bán hàng
        </a>
    </div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Lịch sử Giao dịch</h1>
            <p class="text-gray-600 mt-1">Tra cứu các hóa đơn đã tạo trong ca làm việc.</p>
        </div>
    </div>
</header>