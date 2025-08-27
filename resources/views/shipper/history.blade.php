@extends('layouts.shipper')

@section('title', 'Lịch sử giao hàng')

@section('content')
    <header class="page-header p-5 bg-white border-b flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Lịch sử gói hàng</h1>
    </header>

    <main class="page-content p-4 space-y-3 bg-gray-50">
        @forelse($fulfillmentsHistory as $fulfillment)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                {{-- Truy cập thông tin đơn hàng qua $fulfillment->order --}}
                <a href="{{ route('shipper.fulfillments.show', $fulfillment) }}" class="block">
                    <div class="flex justify-between items-center mb-1">
                        {{-- Hiển thị Mã Vận Đơn ở trên --}}
                        <span class="font-bold text-gray-800">{{ $fulfillment->tracking_code }}</span>
                        <span class="text-xs text-gray-400">{{ $fulfillment->updated_at->format('d/m/Y H:i') }}</span>
                    </div>

                    {{-- Hiển thị Mã Đơn Hàng ở dưới --}}
                    <p class="text-xs text-gray-500 mb-2">Đơn hàng: {{ $fulfillment->order->order_code }}</p>

                    {{-- Giữ nguyên tên khách hàng --}}
                    <p class="text-sm font-semibold text-gray-700 truncate">{{ $fulfillment->order->customer_name }}</p>
                    <div class="mt-2 pt-2 border-t border-dashed">
                        {{-- Kiểm tra trạng thái của GÓI HÀNG --}}
                        @if ($fulfillment->status === 'delivered')
                            <p class="text-sm text-green-600 font-bold flex items-center"><i
                                    class="fas fa-check-circle mr-2"></i> Giao thành công</p>
                        @elseif ($fulfillment->status === 'failed')
                            <p class="text-sm text-red-600 font-bold flex items-center"><i
                                    class="fas fa-times-circle mr-2"></i> Giao thất bại</p>
                        @else
                            <p class="text-sm text-gray-500 font-bold flex items-center"><i class="fas fa-ban mr-2"></i>
                                {{ ucfirst($fulfillment->status) }}</p>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <p class="text-center text-gray-500 pt-10">Chưa có lịch sử giao hàng.</p>
        @endforelse

        {{-- Sửa biến phân trang --}}
        <div class="mt-6 px-4">
            {{ $fulfillmentsHistory->links() }}
        </div>
    </main>
@endsection
